<?php

namespace App\Http\Controllers;

use App\Jobs\ScreenApplicationWithAi;
use App\Models\Application;
use App\Models\Assessment;
use App\Models\JobPosting;
use App\Services\ApplicationStageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ApplicationPipelineController extends Controller
{
    public function board(Request $request, JobPosting $job)
    {
        $applications = $job->applications()
            ->with(['candidate'])
            ->orderByDesc('ai_score')
            ->get()
            ->groupBy('stage');

        return view('pipeline.board', [
            'job' => $job,
            'stages' => ApplicationStageService::STAGES,
            'applications' => $applications,
            'canMove' => $request->user()->isRecruiter(),
        ]);
    }

    public function show(Application $application)
    {
        $application->load([
            'candidate',
            'jobPosting',
            'stageHistories.changedBy',
            'interviews.interviewer',
            'candidateAssessments.assessment.questions',
        ]);

        $assessments = Assessment::where('is_active', true)->orderBy('title')->get();
        $aiStatus = Cache::get(ScreenApplicationWithAi::cacheKey($application->id), 'idle');

        return view('pipeline.show', compact('application', 'assessments', 'aiStatus'));
    }

    /** Pindah tahap (form detail atau drag-and-drop di kanban). Logika ada di ApplicationStageService. */
    public function moveStage(Request $request, Application $application, ApplicationStageService $stages)
    {
        $data = $request->validate([
            'stage' => 'required|in:' . implode(',', ApplicationStageService::STAGES),
            'notes' => 'nullable|string|max:1000',
        ]);

        $application = $stages->move($application, $data['stage'], $request->user(), $data['notes'] ?? null);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'stage' => $application->stage]);
        }

        return back()->with('status', 'Tahap kandidat diperbarui.');
    }

    public function rescreen(Request $request, Application $application)
    {
        ScreenApplicationWithAi::dispatch($application);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'running']);
        }

        return back()->with('status', 'AI screening dijalankan ulang, hasil akan muncul sesaat lagi.');
    }

    /** Dipanggil berkala oleh halaman detail untuk memeriksa hasil screening. */
    public function aiStatus(Application $application)
    {
        $status = Cache::get(ScreenApplicationWithAi::cacheKey($application->id), 'idle');

        if ($status === 'running') {
            return response()->json(['status' => 'running']);
        }

        $application->load(['stageHistories.changedBy']);

        return response()->json([
            'status' => $status,
            'stage' => $application->stage,
            'analysis_html' => view('pipeline.ai-analysis', ['application' => $application, 'aiStatus' => $status])->render(),
            'history_html' => view('pipeline.stage-history', ['application' => $application])->render(),
        ]);
    }
}
