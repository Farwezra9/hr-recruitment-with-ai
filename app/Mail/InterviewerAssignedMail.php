<?php

namespace App\Mail;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InterviewerAssignedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Interview $interview)
    {
    }

    public function build()
    {
        $this->interview->loadMissing(['application.candidate', 'application.jobPosting', 'interviewer']);
        $application = $this->interview->application;

        return $this->subject('Kamu ditugaskan sebagai interviewer - ' . $application->candidate->name)
            ->view('emails.interviewer-assigned')
            ->with([
                'interviewerName' => $this->interview->interviewer->name,
                'candidateName' => $application->candidate->name,
                'jobTitle' => $application->jobPosting->title,
                'interview' => $this->interview,
            ]);
    }
}
