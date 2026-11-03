<?php

namespace App\Http\Controllers;

use App\Mail\InterviewerAssignedMail;
use App\Mail\InterviewScheduledMail;
use App\Models\Application;
use App\Models\Interview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InterviewController extends Controller
{
    public function create(Application $application)
    {
        $application->loadMissing(['candidate', 'jobPosting']);

        $interviewers = User::where('is_active', true)
            ->whereIn('role', ['interviewer', 'hiring_manager', 'recruiter', 'admin'])
            ->orderBy('name')
            ->get();

        return view('interviews.create', compact('application', 'interviewers'));
    }

    public function store(Request $request, Application $application)
    {
        $data = $request->validate([
            'interviewer_id' => 'required|exists:users,id',
            'type' => 'required|in:phone,video,onsite',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:240',
            'location_or_link' => 'nullable|string|max:255',
        ]);

        // Cegah interviewer terjadwal ganda di waktu yang bertabrakan
        $start = \Illuminate\Support\Carbon::parse($data['scheduled_at']);
        $end = $start->copy()->addMinutes((int) $data['duration_minutes']);

        $clash = Interview::where('interviewer_id', $data['interviewer_id'])
            ->where('status', 'scheduled')
            ->whereBetween('scheduled_at', [$start->copy()->subHours(5), $end])
            ->get()
            ->first(fn ($i) => $i->scheduled_at < $end && $i->scheduled_at->copy()->addMinutes($i->duration_minutes) > $start);

        if ($clash) {
            return back()->withInput()->withErrors([
                'scheduled_at' => 'Interviewer sudah punya jadwal yang bertabrakan pada ' . $clash->scheduled_at->format('d M Y H:i') . '.',
            ]);
        }

        $data['application_id'] = $application->id;
        $interview = Interview::create($data);

        $application->moveToStage('interview', $request->user()->id, 'Interview dijadwalkan.');

        try {
            $application->loadMissing('candidate');
            Mail::to($application->candidate->email)->send(new InterviewScheduledMail($interview));
            Mail::to($interview->interviewer->email)->send(new InterviewerAssignedMail($interview));
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim email interview', ['interview_id' => $interview->id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('pipeline.show', $application)->with('status', 'Interview berhasil dijadwalkan.');
    }

    /** Daftar interview milik user yang sedang login (untuk interviewer). */
    public function mine(Request $request)
    {
        $interviews = Interview::with(['application.candidate', 'application.jobPosting'])
            ->where('interviewer_id', $request->user()->id)
            ->orderByRaw("status = 'scheduled' desc")
            ->orderBy('scheduled_at')
            ->get();

        return view('interviews.mine', compact('interviews'));
    }

    public function submitFeedback(Request $request, Interview $interview)
    {
        $user = $request->user();

        abort_unless($user->isRecruiter() || $interview->interviewer_id === $user->id, 403);

        $data = $request->validate([
            'status' => 'required|in:completed,no_show,cancelled',
            'feedback' => 'nullable|string|max:5000',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $interview->update($data);

        return back()->with('status', 'Feedback interview tersimpan.');
    }
}
