<?php

namespace App\Http\Controllers;

use App\Mail\AssessmentAssignedMail;
use App\Models\Application;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\CandidateAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AssessmentController extends Controller
{
    /** Toleransi keterlambatan kirim jawaban (detik) akibat jaringan. */
    protected const GRACE_SECONDS = 60;

    // ------------------------------------------------------------------
    // Manajemen bank soal (recruiter/admin)
    // ------------------------------------------------------------------

    public function index()
    {
        $assessments = Assessment::withCount('questions')->latest()->get();

        return view('assessments.index', compact('assessments'));
    }

    public function create()
    {
        return view('assessments.create');
    }

    protected function assessmentRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'type' => 'required|in:technical,psychometric,language,case_study,other',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:5|max:240',
            'passing_score' => 'required|integer|min:0|max:100',
        ];
    }

    public function store(Request $request)
    {
        $assessment = Assessment::create($request->validate($this->assessmentRules()));

        return redirect()->route('assessments.edit', $assessment)->with('status', 'Tes berhasil dibuat, sekarang tambahkan soalnya.');
    }

    public function edit(Assessment $assessment)
    {
        $assessment->load('questions');

        return view('assessments.edit', compact('assessment'));
    }

    public function update(Request $request, Assessment $assessment)
    {
        $data = $request->validate($this->assessmentRules() + ['is_active' => 'sometimes|boolean']);
        $data['is_active'] = $request->boolean('is_active');

        $assessment->update($data);

        return back()->with('status', 'Tes berhasil diperbarui.');
    }

    public function destroy(Assessment $assessment)
    {
        // Tes yang sudah pernah dikirim ke kandidat tidak boleh dihapus (datanya masih dipakai).
        if (CandidateAssessment::where('assessment_id', $assessment->id)->exists()) {
            $assessment->update(['is_active' => false]);

            return redirect()->route('assessments.index')
                ->with('status', 'Tes ini sudah pernah dikirim ke kandidat, jadi dinonaktifkan (tidak dihapus).');
        }

        $assessment->delete();

        return redirect()->route('assessments.index')->with('status', 'Tes dihapus.');
    }

    // --- Soal ---

    protected function questionRules(): array
    {
        return [
            'question' => 'required|string',
            'question_type' => 'required|in:multiple_choice,essay',
            'options' => 'required_if:question_type,multiple_choice|array',
            'options.*' => 'nullable|string',
            'correct_option' => 'required_if:question_type,multiple_choice|nullable|string',
            'points' => 'required|integer|min:1|max:20',
        ];
    }

    protected function buildOptions(array $data): ?array
    {
        if ($data['question_type'] !== 'multiple_choice') {
            return null;
        }

        $labels = ['A', 'B', 'C', 'D', 'E', 'F'];

        // Pertahankan label sesuai posisi input (A..F), buang opsi yang dikosongkan
        $options = [];
        foreach (array_values($data['options'] ?? []) as $i => $text) {
            if (filled($text) && isset($labels[$i])) {
                $options[$labels[$i]] = $text;
            }
        }

        return $options;
    }

    public function storeQuestion(Request $request, Assessment $assessment)
    {
        $data = $request->validate($this->questionRules());
        $options = $this->buildOptions($data);

        if ($data['question_type'] === 'multiple_choice' && ! isset($options[$data['correct_option'] ?? ''])) {
            return back()->withInput()->withErrors(['correct_option' => 'Jawaban benar harus menunjuk ke opsi yang terisi.']);
        }

        $assessment->questions()->create([
            'question' => $data['question'],
            'question_type' => $data['question_type'],
            'options' => $options,
            'correct_option' => $data['question_type'] === 'multiple_choice' ? $data['correct_option'] : null,
            'points' => $data['points'],
            'order' => $assessment->questions()->count() + 1,
        ]);

        return back()->with('status', 'Soal berhasil ditambahkan.');
    }

    public function updateQuestion(Request $request, AssessmentQuestion $question)
    {
        $data = $request->validate($this->questionRules());
        $options = $this->buildOptions($data);

        if ($data['question_type'] === 'multiple_choice' && ! isset($options[$data['correct_option'] ?? ''])) {
            return back()->withErrors(['correct_option' => 'Jawaban benar harus menunjuk ke opsi yang terisi.']);
        }

        $question->update([
            'question' => $data['question'],
            'question_type' => $data['question_type'],
            'options' => $options,
            'correct_option' => $data['question_type'] === 'multiple_choice' ? $data['correct_option'] : null,
            'points' => $data['points'],
        ]);

        return back()->with('status', 'Soal berhasil diperbarui.');
    }

    public function destroyQuestion(AssessmentQuestion $question)
    {
        $assessment = $question->assessment;
        $question->delete();

        return redirect()->route('assessments.edit', $assessment)->with('status', 'Soal dihapus.');
    }

    // ------------------------------------------------------------------
    // Penugasan & penilaian (recruiter)
    // ------------------------------------------------------------------

    public function assignToApplication(Request $request, Application $application)
    {
        $data = $request->validate(['assessment_id' => 'required|exists:assessments,id']);

        $alreadyPending = $application->candidateAssessments()
            ->where('assessment_id', $data['assessment_id'])
            ->whereIn('status', ['assigned', 'in_progress'])
            ->exists();

        if ($alreadyPending) {
            return back()->withErrors(['assessment_id' => 'Tes ini sudah dikirim dan belum dikerjakan kandidat.']);
        }

        $candidateAssessment = CandidateAssessment::create([
            'application_id' => $application->id,
            'assessment_id' => $data['assessment_id'],
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        $application->moveToStage('assessment', $request->user()->id, 'Tes online ditugaskan.');

        try {
            $application->loadMissing('candidate');
            Mail::to($application->candidate->email)->send(new AssessmentAssignedMail($candidateAssessment));
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim email tes', ['error' => $e->getMessage()]);
        }

        return back()->with('status', 'Tes berhasil ditugaskan. Link berlaku 7 hari.');
    }

    /** Recruiter memberi nilai soal esai; skor total dihitung ulang. */
    public function grade(Request $request, CandidateAssessment $candidateAssessment)
    {
        abort_unless($candidateAssessment->isFinished(), 422, 'Tes belum selesai dikerjakan.');

        $request->validate([
            'essay_points' => 'array',
            'essay_points.*' => 'nullable|numeric|min:0',
        ]);

        $questions = $candidateAssessment->assessment->questions;
        $input = $request->input('essay_points', []);

        $essayScores = [];
        foreach ($questions->where('question_type', 'essay') as $q) {
            $essayScores[$q->id] = min((float) $q->points, max(0, (float) ($input[$q->id] ?? 0)));
        }

        $candidateAssessment->update([
            'essay_scores' => $essayScores,
            'graded_at' => now(),
            'score' => $this->calculateScore($questions, $candidateAssessment->answers ?? [], $essayScores),
        ]);

        return back()->with('status', 'Nilai esai tersimpan, skor total diperbarui.');
    }

    // ------------------------------------------------------------------
    // Sisi kandidat (tanpa login, akses lewat token acak)
    // ------------------------------------------------------------------

    public function take(CandidateAssessment $candidateAssessment)
    {
        $ca = $candidateAssessment->load('assessment.questions');

        if ($ca->isFinished()) {
            return view('assessments.closed', ['message' => 'Tes ini sudah selesai dikerjakan.']);
        }

        if ($ca->status === 'assigned' && $ca->expires_at && now()->greaterThan($ca->expires_at)) {
            $ca->update(['status' => 'expired', 'completed_at' => now(), 'score' => 0]);

            return view('assessments.closed', ['message' => 'Link tes ini sudah kedaluwarsa. Hubungi tim HR untuk meminta tes baru.']);
        }

        if (! $ca->started_at) {
            $ca->update(['status' => 'in_progress', 'started_at' => now()]);
        }

        $deadline = $ca->started_at->copy()->addMinutes($ca->assessment->duration_minutes);

        // Waktu sudah habis saat kandidat kembali tanpa mengirim jawaban
        if (now()->greaterThan($deadline->copy()->addSeconds(self::GRACE_SECONDS))) {
            $ca->update(['status' => 'expired', 'completed_at' => now(), 'score' => 0]);

            return view('assessments.closed', ['message' => 'Waktu pengerjaan tes sudah habis.']);
        }

        $remaining = max(0, (int) now()->diffInSeconds($deadline, false));

        return view('assessments.take', [
            'candidateAssessment' => $ca,
            'remainingSeconds' => $remaining,
        ]);
    }

    public function submit(Request $request, CandidateAssessment $candidateAssessment)
    {
        $ca = $candidateAssessment->load('assessment.questions');

        if ($ca->isFinished()) {
            return view('assessments.closed', ['message' => 'Tes ini sudah selesai dikerjakan.']);
        }

        $questions = $ca->assessment->questions;
        $validIds = $questions->pluck('id')->map(fn ($id) => (string) $id)->all();

        // Simpan hanya jawaban untuk soal yang memang ada di tes ini
        $answers = collect($request->input('answers', []))
            ->filter(fn ($value, $id) => in_array((string) $id, $validIds, true))
            ->map(fn ($value) => is_string($value) ? mb_substr($value, 0, 5000) : '')
            ->all();

        $deadline = ($ca->started_at ?? now())->copy()->addMinutes($ca->assessment->duration_minutes);
        $late = now()->greaterThan($deadline->copy()->addSeconds(self::GRACE_SECONDS));

        $ca->update([
            'status' => $late ? 'expired' : 'completed',
            'completed_at' => now(),
            'answers' => $answers,
            'score' => $this->calculateScore($questions, $answers, []),
        ]);

        return view('assessments.submitted', ['candidateAssessment' => $ca, 'late' => $late]);
    }

    /**
     * Skor 0-100 = (poin pilihan ganda benar + nilai esai) / total poin.
     * Esai belum dinilai dianggap 0 sampai recruiter menilainya.
     */
    protected function calculateScore($questions, array $answers, array $essayScores): int
    {
        $total = (float) $questions->sum('points');

        if ($total <= 0) {
            return 0;
        }

        $earned = 0.0;

        foreach ($questions as $q) {
            if ($q->question_type === 'multiple_choice') {
                if (($answers[$q->id] ?? null) === $q->correct_option) {
                    $earned += $q->points;
                }
            } else {
                $earned += (float) ($essayScores[$q->id] ?? 0);
            }
        }

        return (int) round(($earned / $total) * 100);
    }
}
