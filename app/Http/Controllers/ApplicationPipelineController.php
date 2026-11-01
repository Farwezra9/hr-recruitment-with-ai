<?php

namespace App\Http\Controllers;

use App\Jobs\ScreenApplicationWithAi;
use Illuminate\Support\Facades\Cache;
use App\Mail\ApplicationStatusChangedMail;
use App\Models\Application;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ApplicationPipelineController extends Controller
{
    protected array $stages = [
        'applied', 'ai_screening', 'shortlisted', 'assessment', 'interview', 'offer', 'hired', 'rejected',
    ];

    /**
     * Tampilan kanban pipeline kandidat untuk satu lowongan.
     */
    public function board(JobPosting $job)
    {
        $applications = $job->applications()
            ->with(['candidate'])
            ->get()
            ->groupBy('stage');

        return view('pipeline.board', [
            'job' => $job,
            'stages' => $this->stages,
            'applications' => $applications,
        ]);
    }

public function show(Application $application)
{
    $application->load(['candidate', 'jobPosting', 'stageHistories.changedBy', 'interviews.interviewer', 'candidateAssessments.assessment']);

    $assessments = \App\Models\Assessment::where('is_active', true)->orderBy('title')->get();
    $aiStatus = Cache::get(ScreenApplicationWithAi::cacheKey($application->id), 'idle');

    return view('pipeline.show', compact('application', 'assessments', 'aiStatus'));
}

public function rescreen(Request $request, Application $application)
{
    ScreenApplicationWithAi::dispatch($application);

    if ($request->expectsJson()) {
        return response()->json(['status' => 'running']);
    }

    return back()->with('status', 'AI screening dijalankan ulang, hasil akan muncul sesaat lagi.');
}

/**
 * Dipanggil berkala oleh halaman detail kandidat untuk memeriksa hasil screening.
 */
public function aiStatus(Application $application)
{
    $status = Cache::get(ScreenApplicationWithAi::cacheKey($application->id), 'idle');

    if ($status === 'running') {
        return response()->json(['status' => 'running']);
    }

    $application->load(['stageHistories.changedBy']);

    return response()->json([
        'status' => $status, // done | failed | idle
        'stage' => $application->stage,
        'analysis_html' => view('pipeline.ai-analysis', ['application' => $application, 'aiStatus' => $status])->render(),
        'history_html' => view('pipeline.stage-history', ['application' => $application])->render(),
    ]);
}

    /**
     * Pindahkan kandidat ke tahap lain (drag-and-drop di kanban, atau tombol aksi).
     */
    public function moveStage(Request $request, Application $application)
    {
        $data = $request->validate([
            'stage' => 'required|in:applied,ai_screening,shortlisted,assessment,interview,offer,hired,rejected',
            'notes' => 'nullable|string',
        ]);

        $application->moveToStage($data['stage'], $request->user()->id, $data['notes'] ?? null);

        if ($data['stage'] === 'rejected') {
            $application->update([
                'status' => 'rejected',
                'rejected_by' => $request->user()->id,
                'rejection_reason' => $data['notes'] ?? null,
            ]);
        }

        if ($data['stage'] === 'hired') {
            $application->update(['status' => 'hired']);
        }

        if (in_array($data['stage'], ['shortlisted', 'offer', 'hired', 'rejected'])) {
            $application->loadMissing('candidate');
            Mail::to($application->candidate->email)->send(
                new ApplicationStatusChangedMail($application->fresh(), $data['stage'])
            );
        }

        return back()->with('status', 'Tahap kandidat diperbarui & email notifikasi terkirim.');
    }


}
