<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\DepartmentResource;
use App\Models\Application;
use App\Models\Department;
use App\Models\Interview;
use App\Models\JobPosting;
use Illuminate\Support\Facades\DB;

class MetaController extends ApiController
{
    public function departments()
    {
        return DepartmentResource::collection(Department::orderBy('name')->get());
    }

    /** GET /stats: angka ringkas dashboard. */
    public function stats()
    {
        $stageCounts = Application::select('stage', DB::raw('count(*) as total'))->groupBy('stage')->pluck('total', 'stage');

        return response()->json(['data' => [
            'open_jobs' => JobPosting::where('status', 'open')->count(),
            'applications_total' => Application::count(),
            'applications_last_7_days' => Application::where('created_at', '>=', now()->subDays(7))->count(),
            'average_ai_score' => ($avg = Application::whereNotNull('ai_score')->avg('ai_score')) !== null ? round($avg, 1) : null,
            'pending_screening' => Application::where('status', 'active')->whereNull('ai_screened_at')->count(),
            'upcoming_interviews' => Interview::where('status', 'scheduled')->where('scheduled_at', '>=', now())->count(),
            'by_stage' => $stageCounts,
        ]]);
    }
}
