<?php

namespace App\Http\Controllers;

use App\Jobs\ScreenApplicationWithAi;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\JobPosting;
use App\Services\CvTextExtractorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CandidateApplicationController extends Controller
{
    /**
     * Halaman publik daftar lowongan yang terbuka (career page).
     */
    public function careerPage()
    {
        $jobs = JobPosting::open()->latest()->paginate(10);

        return view('candidates.career-page', compact('jobs'));
    }

    public function showApplyForm(JobPosting $job)
    {
        abort_unless($job->status === 'open', 404);

        return view('candidates.apply', compact('job'));
    }

    /**
     * Kandidat submit lamaran: simpan data, upload CV, ekstrak teks, lalu antrikan AI screening.
     */
    public function submit(Request $request, JobPosting $job, CvTextExtractorService $extractor)
    {
        abort_unless($job->status === 'open', 404);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'linkedin_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
            'cv' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $path = $request->file('cv')->store('cv', 'public');
        $absolutePath = Storage::disk('public')->path($path);

        $rawText = $extractor->extract($absolutePath);

        $candidate = Candidate::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'linkedin_url' => $data['linkedin_url'] ?? null,
            'portfolio_url' => $data['portfolio_url'] ?? null,
            'cv_path' => $path,
            'cv_raw_text' => $rawText,
            'source' => 'career_page',
        ]);

        $application = Application::create([
            'candidate_id' => $candidate->id,
            'job_posting_id' => $job->id,
            'stage' => 'applied',
            'status' => 'active',
        ]);

        // Jalankan AI screening di background (queue) supaya kandidat tidak menunggu lama
        ScreenApplicationWithAi::dispatch($application);

        return view('candidates.applied', compact('candidate', 'job'));
    }
}
