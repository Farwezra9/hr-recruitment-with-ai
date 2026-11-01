<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use Illuminate\Http\Request;

class JobPostingApiController extends Controller
{
    public function index(Request $request)
    {
        $query = JobPosting::with('department')->withCount('applications');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return response()->json($query->latest()->paginate($request->integer('per_page', 15)));
    }

    public function show(JobPosting $job)
    {
        $job->load('department');

        return response()->json($job);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'responsibilities' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'employment_type' => 'required|in:full_time,part_time,contract,internship',
            'work_mode' => 'required|in:onsite,remote,hybrid',
            'vacancies' => 'required|integer|min:1',
            'status' => 'required|in:draft,open,closed,archived',
            'closing_date' => 'nullable|date',
        ]);

        $data['created_by'] = $request->user()->id;
        $job = JobPosting::create($data);

        return response()->json($job, 201);
    }
}
