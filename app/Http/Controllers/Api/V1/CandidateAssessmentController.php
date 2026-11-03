<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CandidateAssessmentResource;
use App\Mail\AssessmentAssignedMail;
use App\Models\Application;
use App\Models\CandidateAssessment;
use App\Services\AssessmentScorer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CandidateAssessmentController extends ApiController
{
    /** GET /applications/{id}/assessments */
    public function index(Application $application)
    {
        $items = $application->candidateAssessments()->with('assessment.questions')->latest()->get();

        return CandidateAssessmentResource::collection($items);
    }

    /** POST /applications/{id}/assessments  body: {"assessment_id": 1} */
    public function store(Request $request, Application $application)
    {
        $data = $request->validate(['assessment_id' => 'required|exists:assessments,id']);

        $pending = $application->candidateAssessments()
            ->where('assessment_id', $data['assessment_id'])
            ->whereIn('status', ['assigned', 'in_progress'])
            ->exists();

        if ($pending) {
            return response()->json([
                'message' => 'Data tidak valid.',
                'errors' => ['assessment_id' => ['Tes ini sudah dikirim dan belum dikerjakan kandidat.']],
            ], 422);
        }

        $ca = CandidateAssessment::create([
            'application_id' => $application->id,
            'assessment_id' => $data['assessment_id'],
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        $application->moveToStage('assessment', $request->user()->id, 'Tes online ditugaskan.');

        try {
            $application->loadMissing('candidate');
            Mail::to($application->candidate->email)->send(new AssessmentAssignedMail($ca));
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim email tes', ['error' => $e->getMessage()]);
        }

        return (new CandidateAssessmentResource($ca->load('assessment.questions')))->response()->setStatusCode(201);
    }

    /** GET /candidate-assessments/{token} */
    public function show(CandidateAssessment $candidateAssessment)
    {
        return new CandidateAssessmentResource($candidateAssessment->load('assessment.questions'));
    }

    /** PATCH /candidate-assessments/{token}/grade  body: {"essay_points": {"12": 3.5}} */
    public function grade(Request $request, CandidateAssessment $candidateAssessment, AssessmentScorer $scorer)
    {
        if (! $candidateAssessment->isFinished()) {
            return response()->json(['message' => 'Tes belum selesai dikerjakan.'], 409);
        }

        $request->validate([
            'essay_points' => 'required|array',
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
            'score' => $scorer->score($questions, $candidateAssessment->answers ?? [], $essayScores),
        ]);

        return new CandidateAssessmentResource($candidateAssessment->load('assessment.questions'));
    }
}
