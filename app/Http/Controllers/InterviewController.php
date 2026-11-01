<?php

namespace App\Http\Controllers;

use App\Mail\InterviewScheduledMail;
use App\Models\Application;
use App\Models\Interview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InterviewController extends Controller
{
    public function create(Application $application)
    {
        $interviewers = User::whereIn('role', ['interviewer', 'hiring_manager', 'recruiter', 'admin'])
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

        $data['application_id'] = $application->id;

        $interview = Interview::create($data);

        $application->moveToStage('interview', $request->user()->id, 'Interview dijadwalkan.');

        $application->loadMissing('candidate');
        Mail::to($application->candidate->email)->send(new InterviewScheduledMail($interview));

        return redirect()->route('pipeline.show', $application)->with('status', 'Interview berhasil dijadwalkan & email notifikasi terkirim.');
    }

    public function submitFeedback(Request $request, Interview $interview)
    {
        $data = $request->validate([
            'status' => 'required|in:completed,no_show,cancelled',
            'feedback' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $interview->update($data);

        return back()->with('status', 'Feedback interview tersimpan.');
    }
}
