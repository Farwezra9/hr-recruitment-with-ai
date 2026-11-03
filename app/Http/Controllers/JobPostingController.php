<?php

namespace App\Http\Controllers;

use App\Jobs\ScreenApplicationWithAi;
use App\Models\Department;
use App\Models\JobPosting;
use Illuminate\Http\Request;

class JobPostingController extends Controller
{
    protected function rules(): array
    {
        return [
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
        ];
    }

    public function index()
    {
        $jobs = JobPosting::with('department')
            ->withCount('applications')
            ->latest()
            ->paginate(15);

        $trashedCount = JobPosting::onlyTrashed()->count();

        return view('jobs.index', compact('jobs', 'trashedCount'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();

        return view('jobs.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
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
        $job->update($request->validate($this->rules()));

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

    /** Soft delete: lowongan masuk "Terhapus" dan bisa dipulihkan. Data lamaran tidak ikut hilang. */
    public function destroy(JobPosting $job)
    {
        $job->update(['status' => 'closed']);
        $job->delete();

        return redirect()->route('jobs.index')->with('status', 'Lowongan dipindahkan ke daftar terhapus (bisa dipulihkan).');
    }

    public function trashed()
    {
        $jobs = JobPosting::onlyTrashed()->withCount('applications')->latest('deleted_at')->paginate(15);

        return view('jobs.trashed', compact('jobs'));
    }

    public function restore(JobPosting $job)
    {
        $job->restore();

        return redirect()->route('jobs.show', $job)->with('status', 'Lowongan dipulihkan (status: Tutup).');
    }

    /** Nilai ulang semua pelamar aktif di lowongan ini, mis. setelah kualifikasi diubah. */
    public function rescreenAll(JobPosting $job)
    {
        $count = 0;

        $job->applications()->where('status', 'active')->each(function ($application) use (&$count) {
            ScreenApplicationWithAi::dispatch($application);
            $count++;
        });

        return back()->with('status', "{$count} pelamar dijadwalkan untuk dinilai ulang oleh AI. Hasil akan muncul bertahap.");
    }
}
