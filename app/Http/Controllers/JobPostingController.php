<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\JobPosting;
use Illuminate\Http\Request;

class JobPostingController extends Controller
{
    public function index()
    {
        $jobs = JobPosting::with('department')
            ->withCount('applications')
            ->latest()
            ->paginate(15);

        return view('jobs.index', compact('jobs'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();

        return view('jobs.create', compact('departments'));
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
            'salary_min' => 'nullable|integer|min:0',
            'salary_max' => 'nullable|integer|min:0',
            'vacancies' => 'required|integer|min:1',
            'status' => 'required|in:draft,open,closed,archived',
            'closing_date' => 'nullable|date',
        ]);

        $data['created_by'] = $request->user()->id;

        $job = JobPosting::create($data);

        return redirect()->route('jobs.show', $job)->with('status', 'Lowongan berhasil dibuat.');
    }

    public function show(JobPosting $job)
    {
        $job->load('department');
        $applications = $job->applications()
            ->with('candidate')
            ->orderByDesc('ai_score')
            ->paginate(20);

        return view('jobs.show', compact('job', 'applications'));
    }

    public function edit(JobPosting $job)
    {
        $departments = Department::orderBy('name')->get();

        return view('jobs.edit', compact('job', 'departments'));
    }

    public function update(Request $request, JobPosting $job)
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
            'salary_min' => 'nullable|integer|min:0',
            'salary_max' => 'nullable|integer|min:0',
            'vacancies' => 'required|integer|min:1',
            'status' => 'required|in:draft,open,closed,archived',
            'closing_date' => 'nullable|date',
        ]);

        $job->update($data);

        return redirect()->route('jobs.show', $job)->with('status', 'Lowongan berhasil diperbarui.');
    }
public function updateStatus(Request $request, JobPosting $job)
{
    $data = $request->validate([
        'status' => 'required|in:draft,open,closed,archived',
    ]);

    $job->update($data);

    $labels = ['draft' => 'Draft', 'open' => 'Buka', 'closed' => 'Tutup', 'archived' => 'Arsip'];

    return back()->with('status', "Status \"{$job->title}\" diubah menjadi {$labels[$job->status]}.");
}
    public function destroy(JobPosting $job)
    {
        $job->delete();

        return redirect()->route('jobs.index')->with('status', 'Lowongan dihapus.');
    }
}
