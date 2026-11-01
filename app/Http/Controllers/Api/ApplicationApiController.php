<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ScreenApplicationWithAi;
use App\Models\Application;
use App\Models\JobPosting;
use Illuminate\Http\Request;

class ApplicationApiController extends Controller
{
    public function index(Request $request, JobPosting $job)
    {
        $applications = $job->applications()
            ->with('candidate')
            ->when($request->filled('stage'), fn ($q) => $q->where('stage', $request->string('stage')))
            ->orderByDesc('ai_score')
            ->paginate($request->integer('per_page', 20));

        return response()->json($applications);
    }

    public function show(Application $application)
    {
        $application->load(['candidate', 'jobPosting', 'interviews', 'candidateAssessments']);

        return response()->json($application);
    }

    /**
     * Trigger (ulang) AI screening untuk sebuah lamaran via API.
     */
    public function screen(Application $application)
    {
        ScreenApplicationWithAi::dispatch($application);

        return response()->json([
            'message' => 'AI screening telah diantrikan.',
            'application_id' => $application->id,
        ], 202);
    }

    public function moveStage(Request $request, Application $application)
    {
        $data = $request->validate([
            'stage' => 'required|in:applied,ai_screening,shortlisted,assessment,interview,offer,hired,rejected',
            'notes' => 'nullable|string',
        ]);

        $application->moveToStage($data['stage'], $request->user()?->id, $data['notes'] ?? null);

        return response()->json($application->fresh());
    }
}
