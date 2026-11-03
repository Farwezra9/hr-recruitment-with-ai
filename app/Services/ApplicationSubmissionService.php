<?php

namespace App\Services;

use App\Jobs\ScreenApplicationWithAi;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\JobPosting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Logika menerima lamaran (form web dan REST API publik memakai ini).
 * Validasi input dilakukan pemanggil; di sini hanya aturan bisnis.
 */
class ApplicationSubmissionService
{
    public function __construct(protected CvTextExtractorService $extractor)
    {
    }

    public function submit(JobPosting $job, array $data, UploadedFile $cv): Application
    {
        $email = mb_strtolower(trim($data['email']));
        $candidate = Candidate::where('email', $email)->first();

        // Cegah lamaran ganda ke posisi yang sama
        if ($candidate && Application::where('candidate_id', $candidate->id)->where('job_posting_id', $job->id)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Email ini sudah pernah melamar untuk posisi ini.'],
            ]);
        }

        // CV disimpan di disk privat (tidak bisa dibuka lewat URL publik)
        $path = $cv->store('cv', 'local');
        $rawText = $this->extractor->extract(Storage::disk('local')->path($path));

        $attributes = [
            'name' => $data['name'],
            'email' => $email,
            'phone' => $data['phone'] ?? null,
            'linkedin_url' => $data['linkedin_url'] ?? null,
            'portfolio_url' => $data['portfolio_url'] ?? null,
            'cv_path' => $path,
            'cv_raw_text' => $rawText,
            'consented_at' => now(),
        ];

        if ($candidate) {
            $oldPath = $candidate->cv_path;
            $candidate->update($attributes);

            if ($oldPath && $oldPath !== $path) {
                Storage::disk('local')->delete($oldPath);
                Storage::disk('public')->delete($oldPath);
            }
        } else {
            $candidate = Candidate::create($attributes + ['source' => 'career_page']);
        }

        $application = Application::create([
            'candidate_id' => $candidate->id,
            'job_posting_id' => $job->id,
            'stage' => 'applied',
            'status' => 'active',
        ]);

        ScreenApplicationWithAi::dispatch($application);

        return $application->load('candidate');
    }
}
