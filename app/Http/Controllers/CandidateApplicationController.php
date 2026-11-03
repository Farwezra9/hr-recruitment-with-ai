<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Services\ApplicationSubmissionService;
use Illuminate\Http\Request;

class CandidateApplicationController extends Controller
{
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

    public function submit(Request $request, JobPosting $job, ApplicationSubmissionService $service)
    {
        abort_unless($job->status === 'open', 404);

        // Honeypot: kolom ini tersembunyi untuk manusia. Kalau terisi, kemungkinan besar bot.
        if ($request->filled('website')) {
            return redirect()->route('career.index');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'linkedin_url' => 'nullable|url:http,https|max:255',
            'portfolio_url' => 'nullable|url:http,https|max:255',
            'cv' => 'required|file|mimes:pdf,docx|max:5120',
            'consent' => 'accepted',
        ], [
            'cv.mimes' => 'CV harus berformat PDF atau DOCX.',
            'cv.max' => 'Ukuran CV maksimal 5 MB.',
            'consent.accepted' => 'Kamu perlu menyetujui pemrosesan data untuk melanjutkan lamaran.',
        ]);

        // Aturan bisnis (lamaran ganda, simpan CV privat, antrikan AI) ada di service bersama web + API.
        $application = $service->submit($job, $data, $request->file('cv'));

        return view('candidates.applied', ['candidate' => $application->candidate, 'job' => $job]);
    }
}
