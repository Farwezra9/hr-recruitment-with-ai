<?php

namespace App\Services;

use App\Models\Application;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeCvScreeningService
{
    /** Batas karakter teks CV yang dikirim ke AI (hemat token). */
    protected const MAX_CV_CHARS = 10000;

    /** Batas nilai maksimum tiap komponen rubrik (total = 100). */
    protected const RUBRIC_MAX = [
        'skills' => 40,
        'experience' => 30,
        'education' => 15,
        'other' => 15,
    ];

    /**
     * Screening CV dengan AI. Penyedia dipilih lewat AI_PROVIDER di .env
     * (groq dan/atau claude), dipisah koma, dicoba berurutan dari kiri.
     */
    public function screen(Application $application): Application
    {
        $application->loadMissing(['candidate', 'jobPosting']);

        $candidate = $application->candidate;
        $job = $application->jobPosting;

        $cvText = trim($candidate->cv_raw_text ?? '');

        // CV tidak terbaca (mis. PDF hasil scan): jangan kirim ke AI, tandai saja.
        if ($cvText === '') {
            $application->update([
                'ai_score' => null,
                'ai_recommendation' => null,
                'ai_summary' => 'Teks CV tidak bisa dibaca (kemungkinan PDF hasil scan/gambar). Periksa CV secara manual.',
                'ai_analysis' => null,
                'ai_screened_at' => now(),
            ]);

            $this->advanceStage($application);

            return $application->fresh();
        }

        $prompt = $this->buildPrompt(
            cvText: mb_substr($cvText, 0, self::MAX_CV_CHARS),
            jobTitle: $job->title,
            requirements: $job->requirements,
            responsibilities: $job->responsibilities ?? ''
        );

        $text = $this->callWithFallback($prompt, $application);
        $result = $this->normalizeResult($this->parseJsonResponse($text));

        $application->update([
            'ai_score' => $result['score'] ?? null,
            'ai_summary' => $result['summary'] ?? null,
            'ai_recommendation' => $result['recommendation'] ?? null,
            'ai_analysis' => $result,
            'ai_screened_at' => now(),
        ]);

        $this->advanceStage($application);

        return $application->fresh();
    }

    protected function advanceStage(Application $application): void
    {
        if ($application->stage === 'applied') {
            $application->moveToStage('ai_screening', notes: 'Screening otomatis oleh AI.');
        }
    }

    /**
     * Coba penyedia AI satu per satu sesuai urutan di AI_PROVIDER.
     * Nama penyedia yang tidak dikenal dianggap gagal dan dicatat di log.
     */
    protected function callWithFallback(string $prompt, Application $application): string
    {
        $providers = array_filter(array_map('trim', explode(',', (string) config('services.ai.provider', 'groq,claude'))));
        $lastError = null;

        foreach ($providers as $provider) {
            try {
                return match ($provider) {
                    'groq' => $this->callGroq($prompt, $application),
                    'claude' => $this->callClaude($prompt, $application),
                    default => throw new \InvalidArgumentException("Provider AI tidak dikenal: {$provider}"),
                };
            } catch (\Throwable $e) {
                $lastError = $e;
                Log::warning("Provider AI '{$provider}' gagal, mencoba berikutnya", [
                    'application_id' => $application->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        throw $lastError ?? new \RuntimeException('Tidak ada provider AI yang dikonfigurasi di AI_PROVIDER.');
    }

    protected function callGroq(string $prompt, Application $application): string
    {
        $response = $this->sendWithRetry(fn () => Http::withToken((string) config('services.groq.api_key'))
            ->timeout(30)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => config('services.groq.model', 'openai/gpt-oss-120b'),
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'max_tokens' => 4096,
                'temperature' => 0,
                'seed' => 42,
                'response_format' => ['type' => 'json_object'],
            ]), 'Groq', $application);

        return (string) $response->json('choices.0.message.content', '');
    }

    protected function callClaude(string $prompt, Application $application): string
    {
        $response = $this->sendWithRetry(fn () => Http::withHeaders([
            'x-api-key' => (string) config('services.anthropic.api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
            'model' => config('services.anthropic.model', 'claude-sonnet-4-5-20250929'),
            'max_tokens' => 2000,
            'temperature' => 0,
            'messages' => [['role' => 'user', 'content' => $prompt]],
        ]), 'Claude', $application);

        return collect($response->json('content'))
            ->where('type', 'text')
            ->pluck('text')
            ->implode("\n");
    }

    /**
     * Kirim request dan ulangi (maks 2x) kalau kena 429/503 atau gagal koneksi.
     */
    protected function sendWithRetry(callable $request, string $provider, Application $application): Response
    {
        $attempts = 2;

        for ($i = 1; $i <= $attempts; $i++) {
            try {
                $response = $request();
            } catch (ConnectionException $e) {
                if ($i === $attempts) {
                    throw new \RuntimeException("Gagal terhubung ke API {$provider}: " . $e->getMessage());
                }
                sleep(3);
                continue;
            }

            if (in_array($response->status(), [429, 503], true) && $i < $attempts) {
                sleep(4);
                continue;
            }

            $this->failIfNeeded($response, $application, $provider);

            return $response;
        }

        throw new \RuntimeException("Gagal memanggil API {$provider}.");
    }

    protected function failIfNeeded(Response $response, Application $application, string $provider): void
    {
        if ($response->failed()) {
            Log::error("Screening CV via {$provider} gagal", [
                'application_id' => $application->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException("Gagal memanggil API {$provider}: " . $response->status());
        }
    }

    protected function buildPrompt(string $cvText, string $jobTitle, string $requirements, string $responsibilities): string
    {
        return <<<PROMPT
Kamu adalah asisten HR yang membantu menyaring CV kandidat terhadap sebuah lowongan pekerjaan.

Posisi: {$jobTitle}

Kualifikasi/Persyaratan:
{$requirements}

Tanggung Jawab:
{$responsibilities}

Isi CV Kandidat (teks hasil ekstraksi):
"""
{$cvText}
"""

Nilai kandidat secara objektif memakai rubrik berikut, hanya berdasarkan bukti yang ada di CV:
- skills (0-40): kecocokan keahlian dengan persyaratan. 0 = tidak ada yang cocok, 40 = semua terpenuhi.
- experience (0-30): kesesuaian pengalaman kerja (lama dan relevansi).
- education (0-15): kesesuaian pendidikan dan sertifikasi.
- other (0-15): pencapaian, proyek, portofolio, dan kejelasan CV.

Balas HANYA dengan JSON valid (tanpa markdown, tanpa teks lain) dengan struktur persis berikut:

{
  "score_breakdown": {"skills": <0-40>, "experience": <0-30>, "education": <0-15>, "other": <0-15>},
  "summary": "<ringkasan 2-3 kalimat mengapa nilai ini diberikan>",
  "matched_skills": ["skill1", "skill2"],
  "missing_skills": ["skill yang dibutuhkan tapi tidak terlihat di CV"],
  "years_of_relevant_experience": <angka perkiraan>,
  "strengths": ["poin kekuatan 1", "poin kekuatan 2"],
  "concerns": ["poin kekhawatiran/red flag jika ada"],
  "education_match": "<sesuai/tidak sesuai/tidak jelas>"
}
PROMPT;
    }

    protected function parseJsonResponse(string $text): array
    {
        $cleaned = trim(preg_replace('/```json|```/', '', $text));
        $decoded = json_decode($cleaned, true);

        // Kalau AI menambah teks di luar JSON, ambil bagian dari "{" pertama sampai "}" terakhir.
        if (json_last_error() !== JSON_ERROR_NONE) {
            $start = strpos($cleaned, '{');
            $end = strrpos($cleaned, '}');

            if ($start !== false && $end !== false && $end > $start) {
                $decoded = json_decode(substr($cleaned, $start, $end - $start + 1), true);
            }
        }

        if (! is_array($decoded)) {
            Log::warning('Respons AI bukan JSON valid', ['raw' => $text]);

            return [
                'score' => null,
                'recommendation' => null,
                'summary' => 'Gagal mem-parsing hasil analisis AI. Coba jalankan ulang screening.',
            ];
        }

        return $decoded;
    }

    /**
     * Hitung skor total dari rubrik (bukan tebakan model), tentukan rekomendasi
     * dari skor, dan pastikan ringkasan berupa teks biasa.
     */
    protected function normalizeResult(array $result): array
    {
        $breakdown = is_array($result['score_breakdown'] ?? null) ? $result['score_breakdown'] : null;

        if ($breakdown !== null) {
            $total = 0;

            foreach (self::RUBRIC_MAX as $key => $limit) {
                $value = isset($breakdown[$key]) && is_numeric($breakdown[$key]) ? (float) $breakdown[$key] : 0;
                $breakdown[$key] = max(0, min($limit, (int) round($value)));
                $total += $breakdown[$key];
            }

            $result['score_breakdown'] = $breakdown;
            $result['score'] = $total; // total komponen = otomatis 0-100
        } elseif (isset($result['score']) && is_numeric($result['score'])) {
            $result['score'] = max(0, min(100, (int) round($result['score'])));
        } else {
            $result['score'] = null;
        }

        // Rekomendasi ditentukan dari skor. Sesuaikan batas 70 dan 40 dengan kebutuhan HR.
        if ($result['score'] !== null) {
            $result['recommendation'] = $result['score'] >= 70
                ? 'strong_fit'
                : ($result['score'] >= 40 ? 'possible_fit' : 'not_a_fit');
        } else {
            $result['recommendation'] = null;
        }

        // Ringkasan harus berupa teks biasa, bukan array/objek.
        if (isset($result['summary']) && ! is_string($result['summary'])) {
            $result['summary'] = is_array($result['summary'])
                ? collect($result['summary'])->flatten()->filter()->implode(' ')
                : (string) $result['summary'];
        }

        return $result;
    }
}