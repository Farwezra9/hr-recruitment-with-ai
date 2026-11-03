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
        $filters = $request->validate([
            'stage' => 'sometimes|nullable|in:applied,ai_screening,shortlisted,assessment,interview,offer,hired,rejected',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);
        $applications = $job->applications()
            ->with('candidate')
            ->when(! empty($filters['stage']), fn ($q) => $q->where('stage', $filters['stage']))
            ->orderByDesc('ai_score')
            ->paginate($filters['per_page'] ?? 20);

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
        abort_unless($application->status === 'active', 409, 'Lamaran yang tidak aktif tidak dapat dinilai ulang.');

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
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($data['stage'] !== $application->stage) {
            $application->moveToStage($data['stage'], $request->user()->id, $data['notes'] ?? null);

            if ($data['stage'] === 'rejected') {
                $application->update([
                    'status' => 'rejected',
                    'rejected_by' => $request->user()->id,
                    'rejection_reason' => $data['notes'] ?? null,
                ]);
            } elseif ($data['stage'] === 'hired') {
                $application->update(['status' => 'hired', 'rejection_reason' => null]);
            } else {
                $application->update(['status' => 'active', 'rejection_reason' => null]);
            }
        }

        return response()->json($application->fresh()->load(['candidate', 'jobPosting']));
    }
}
