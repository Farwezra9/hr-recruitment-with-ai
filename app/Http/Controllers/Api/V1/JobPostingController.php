<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\JobPostingResource;
use App\Jobs\ScreenApplicationWithAi;
use App\Models\JobPosting;
use Illuminate\Http\Request;

class JobPostingController extends ApiController
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

    /** GET /jobs?status=open&q=backend&trashed=only&per_page=20 */
    public function index(Request $request)
    {
        $query = JobPosting::with('department')->withCount('applications');

        if ($request->query('trashed') === 'only') {
            $query->onlyTrashed();
        }

        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%' . $request->query('q') . '%'))
            ->latest();

        return JobPostingResource::collection($query->paginate($this->perPage($request))->withQueryString());
    }

    public function show(JobPosting $job)
    {
        $job->load('department')->loadCount('applications');

        return new JobPostingResource($job);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['created_by'] = $request->user()->id;

        $job = JobPosting::create($data)->load('department');

        return (new JobPostingResource($job))
            ->response()
            ->setStatusCode(201)
            ->header('Location', url("/api/v1/jobs/{$job->id}"));
    }

    /** PUT/PATCH: kirim hanya field yang ingin diubah. */
    public function update(Request $request, JobPosting $job)
    {
        $rules = array_map(fn ($r) => 'sometimes|' . $r, $this->rules());

        $job->update($request->validate($rules));

        return new JobPostingResource($job->load('department'));
    }

    /** Soft delete: bisa dipulihkan lewat POST /jobs/{id}/restore. */
    public function destroy(JobPosting $job)
    {
        $job->update(['status' => 'closed']);
        $job->delete();

        return response()->noContent();
    }

    public function restore(int $id)
    {
        $job = JobPosting::onlyTrashed()->findOrFail($id);
        $job->restore();

        return new JobPostingResource($job->load('department'));
    }

    /** POST /jobs/{job}/rescreen: nilai ulang semua pelamar aktif (202 Accepted). */
    public function rescreen(JobPosting $job)
    {
        $count = 0;

        $job->applications()->where('status', 'active')->each(function ($application) use (&$count) {
            ScreenApplicationWithAi::dispatch($application);
            $count++;
        });

        return response()->json(['message' => 'Screening ulang dijadwalkan.', 'queued' => $count], 202);
    }
}
