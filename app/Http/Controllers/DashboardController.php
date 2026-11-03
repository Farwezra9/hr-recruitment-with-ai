<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationStageHistory;
use App\Models\Interview;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        if ($request->user()->isInterviewer()) {
            return redirect()->route('interviews.mine');
        }

        $stageCounts = Application::select('stage', DB::raw('count(*) as total'))
            ->groupBy('stage')
            ->pluck('total', 'stage');

        // Rata-rata hari dari melamar sampai diterima (hired)
        $hiredRows = ApplicationStageHistory::where('to_stage', 'hired')->with('application:id,created_at')->get();
        $avgDays = $hiredRows->filter(fn ($h) => $h->application)
            ->avg(fn ($h) => abs($h->application->created_at->diffInDays($h->created_at)));

        $stats = [
            'open_jobs' => JobPosting::where('status', 'open')->count(),
            'applications' => Application::count(),
            'this_week' => Application::where('created_at', '>=', now()->subDays(7))->count(),
            'avg_score' => Application::whereNotNull('ai_score')->avg('ai_score'),
            'hired' => (int) ($stageCounts['hired'] ?? 0),
            'pending_screening' => Application::where('status', 'active')->whereNull('ai_screened_at')->count(),
            'time_to_hire' => $avgDays,
        ];

        $upcoming = Interview::with(['application.candidate', 'application.jobPosting', 'interviewer'])
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        $topCandidates = Application::with(['candidate', 'jobPosting'])
            ->where('status', 'active')
            ->whereNotNull('ai_score')
            ->orderByDesc('ai_score')
            ->limit(5)
            ->get();

        $openJobs = JobPosting::where('status', 'open')->withCount('applications')->orderByDesc('applications_count')->limit(6)->get();

        return view('dashboard', compact('stats', 'stageCounts', 'upcoming', 'topCandidates', 'openJobs'));
    }
}
