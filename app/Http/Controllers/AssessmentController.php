<?php

namespace App\Http\Controllers;

use App\Mail\AssessmentAssignedMail;
use App\Models\Application;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\CandidateAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AssessmentController extends Controller
{
    // --- Manajemen bank soal (recruiter/admin) ---

    public function index()
    {
        $assessments = Assessment::withCount('questions')->latest()->get();

        return view('assessments.index', compact('assessments'));
    }

    public function create()
    {
        return view('assessments.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:technical,psychometric,language,case_study,other',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:5|max:240',
            'passing_score' => 'required|integer|min:0|max:100',
        ]);

        $assessment = Assessment::create($data);

        return redirect()->route('assessments.edit', $assessment)->with('status', 'Tes berhasil dibuat, sekarang tambahkan soalnya.');
    }

    public function edit(Assessment $assessment)
    {
        $assessment->load('questions');

        return view('assessments.edit', compact('assessment'));
    }

    public function update(Request $request, Assessment $assessment)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:technical,psychometric,language,case_study,other',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:5|max:240',
            'passing_score' => 'required|integer|min:0|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $assessment->update($data);

        return back()->with('status', 'Tes berhasil diperbarui.');
    }

    public function destroy(Assessment $assessment)
    {
        $assessment->delete();

        return redirect()->route('assessments.index')->with('status', 'Tes dihapus.');
    }

    // --- Kelola soal di dalam sebuah tes ---

    public function storeQuestion(Request $request, Assessment $assessment)
    {
        $data = $request->validate([
            'question' => 'required|string',
            'question_type' => 'required|in:multiple_choice,essay',
            'options' => 'required_if:question_type,multiple_choice|array',
            'options.*' => 'nullable|string',
            'correct_option' => 'required_if:question_type,multiple_choice|string',
            'points' => 'required|integer|min:1|max:20',
        ]);

        $options = null;
        if ($data['question_type'] === 'multiple_choice') {
            // Ubah array numerik ["opsi A", "opsi B", ...] jadi map ["A" => ..., "B" => ...]
            $labels = ['A', 'B', 'C', 'D', 'E', 'F'];
            $options = collect($data['options'])
                ->filter(fn ($v) => filled($v))
                ->values()
                ->mapWithKeys(fn ($v, $i) => [$labels[$i] => $v])
                ->toArray();
        }

        $assessment->questions()->create([
            'question' => $data['question'],
            'question_type' => $data['question_type'],
            'options' => $options,
            'correct_option' => $data['correct_option'] ?? null,
            'points' => $data['points'],
            'order' => $assessment->questions()->count() + 1,
        ]);

        return back()->with('status', 'Soal berhasil ditambahkan.');
    }

    public function updateQuestion(Request $request, AssessmentQuestion $question)
    {
        $data = $request->validate([
            'question' => 'required|string',
            'question_type' => 'required|in:multiple_choice,essay',
            'options' => 'required_if:question_type,multiple_choice|array',
            'options.*' => 'nullable|string',
            'correct_option' => 'required_if:question_type,multiple_choice|string',
            'points' => 'required|integer|min:1|max:20',
        ]);

        $options = null;
        if ($data['question_type'] === 'multiple_choice') {
            $labels = ['A', 'B', 'C', 'D', 'E', 'F'];
            $options = collect($data['options'])
                ->filter(fn ($v) => filled($v))
                ->values()
                ->mapWithKeys(fn ($v, $i) => [$labels[$i] => $v])
                ->toArray();
        }

        $question->update([
            'question' => $data['question'],
            'question_type' => $data['question_type'],
            'options' => $options,
            'correct_option' => $data['correct_option'] ?? null,
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

    public function assignToApplication(Request $request, Application $application)
    {
        $data = $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
        ]);

        $candidateAssessment = CandidateAssessment::create([
            'application_id' => $application->id,
            'assessment_id' => $data['assessment_id'],
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        $application->moveToStage('assessment', $request->user()->id, 'Tes online ditugaskan.');

        $application->loadMissing('candidate');
        Mail::to($application->candidate->email)->send(new AssessmentAssignedMail($candidateAssessment));

        return back()->with('status', 'Tes berhasil ditugaskan & email tes terkirim ke kandidat.');
    }

    // --- Sisi kandidat mengerjakan tes (akses via link unik/token, bukan login) ---

    public function take(CandidateAssessment $candidateAssessment)
    {
        abort_if($candidateAssessment->status === 'completed', 403, 'Tes sudah pernah dikerjakan.');

        $candidateAssessment->update([
            'status' => 'in_progress',
            'started_at' => $candidateAssessment->started_at ?? now(),
        ]);

        $candidateAssessment->load('assessment.questions');

        return view('assessments.take', compact('candidateAssessment'));
    }

    public function submit(Request $request, CandidateAssessment $candidateAssessment)
    {
        $answers = $request->validate(['answers' => 'required|array'])['answers'];

        $questions = $candidateAssessment->assessment->questions;
        $totalPoints = $questions->sum('points');
        $earned = 0;

        foreach ($questions as $question) {
            $given = $answers[$question->id] ?? null;
            if ($question->question_type === 'multiple_choice' && $given === $question->correct_option) {
                $earned += $question->points;
            }
        }

        $score = $totalPoints > 0 ? (int) round(($earned / $totalPoints) * 100) : 0;

        $candidateAssessment->update([
            'status' => 'completed',
            'completed_at' => now(),
            'answers' => $answers,
            'score' => $score,
        ]);

        return view('assessments.submitted', compact('candidateAssessment', 'score'));
    }
}
