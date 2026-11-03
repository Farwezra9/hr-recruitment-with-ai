<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\JobPostingResource;
use App\Models\JobPosting;
use App\Services\ApplicationSubmissionService;
use Illuminate\Http\Request;

/**
 * Endpoint publik (tanpa token) untuk career site / aplikasi front-end terpisah.
 */
class PublicJobController extends ApiController
{
    public function index(Request $request)
    {
        $query = JobPosting::open()->with('department')
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%' . $request->query('q') . '%'))
            ->latest();

        return JobPostingResource::collection($query->paginate($this->perPage($request))->withQueryString());
    }

    public function show(JobPosting $job)
    {
        abort_unless($job->status === 'open', 404);

        return new JobPostingResource($job->load('department'));
    }

    /** POST /public/jobs/{job}/applications (multipart/form-data) */
    public function apply(Request $request, JobPosting $job, ApplicationSubmissionService $service)
    {
        abort_unless($job->status === 'open', 404);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'linkedin_url' => 'nullable|url:http,https|max:255',
            'portfolio_url' => 'nullable|url:http,https|max:255',
            'cv' => 'required|file|mimes:pdf,docx|max:5120',
            'consent' => 'accepted',
        ]);

        $application = $service->submit($job, $data, $request->file('cv'));

        // Hasil AI sengaja tidak dikembalikan ke pelamar.
        return response()->json([
            'message' => 'Lamaran diterima.',
            'data' => ['application_id' => $application->id, 'job_id' => $job->id, 'status' => $application->status],
        ], 201);
    }
}
