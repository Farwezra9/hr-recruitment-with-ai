<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\ApplicationResource;
use App\Jobs\ScreenApplicationWithAi;
use App\Models\Application;
use App\Models\JobPosting;
use App\Services\ApplicationStageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ApplicationController extends ApiController
{
    /**
     * Filter: q, job_id, stage, status, recommendation, min_score
     * Urutan: sort=-created_at (default) | ai_score | -ai_score | created_at
     */
    protected function query(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        $sort = (string) $request->query('sort', '-created_at');
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        if (! in_array($column, ['ai_score', 'created_at'], true)) {
            [$column, $direction] = ['created_at', 'desc'];
        }

        return Application::with(['candidate', 'jobPosting'])
            ->when($term !== '', fn ($q) => $q->whereHas('candidate', function ($c) use ($term) {
                $c->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%");
            }))
            ->when($request->filled('job_id'), fn ($q) => $q->where('job_posting_id', (int) $request->query('job_id')))
            ->when($request->filled('stage'), fn ($q) => $q->where('stage', $request->query('stage')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('recommendation'), fn ($q) => $q->where('ai_recommendation', $request->query('recommendation')))
            ->when($request->filled('min_score'), fn ($q) => $q->where('ai_score', '>=', (int) $request->query('min_score')))
            ->orderBy($column, $direction);
    }

    public function index(Request $request)
    {
        return ApplicationResource::collection($this->query($request)->paginate($this->perPage($request))->withQueryString());
    }

    public function forJob(Request $request, JobPosting $job)
    {
        $query = $this->query($request)->where('job_posting_id', $job->id);

        return ApplicationResource::collection($query->paginate($this->perPage($request))->withQueryString());
    }

    public function show(Application $application)
    {
        $application->load([
            'candidate', 'jobPosting', 'interviews.interviewer',
            'candidateAssessments.assessment', 'stageHistories.changedBy',
        ]);

        return new ApplicationResource($application);
    }

    /** PATCH /applications/{id}  body: {"stage": "shortlisted", "notes": "..."} */
    public function update(Request $request, Application $application, ApplicationStageService $stages)
    {
        $data = $request->validate([
            'stage' => 'required|in:' . implode(',', ApplicationStageService::STAGES),
            'notes' => 'nullable|string|max:1000',
        ]);

        $application = $stages->move($application, $data['stage'], $request->user(), $data['notes'] ?? null);

        return new ApplicationResource($application->load(['candidate', 'jobPosting', 'stageHistories.changedBy']));
    }

    /** POST /applications/{id}/screening: jalankan AI (202 Accepted) */
    public function screen(Application $application)
    {
        ScreenApplicationWithAi::dispatch($application);

        return response()->json(['status' => 'running'], 202);
    }

    /** GET /applications/{id}/screening: status & hasil terakhir (idle|running|done|failed) */
    public function screeningStatus(Application $application)
    {
        return response()->json([
            'status' => Cache::get(ScreenApplicationWithAi::cacheKey($application->id), 'idle'),
            'ai' => [
                'score' => $application->ai_score,
                'recommendation' => $application->ai_recommendation,
                'summary' => $application->ai_summary,
                'screened_at' => $application->ai_screened_at?->toIso8601String(),
            ],
        ]);
    }
}
