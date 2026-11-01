<?php

namespace App\Mail;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InterviewScheduledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Interview $interview)
    {
    }

    public function build()
    {
        $application = $this->interview->application()->with(['candidate', 'jobPosting'])->first();

        return $this->subject('Jadwal Interview - ' . $application->jobPosting->title)
            ->view('emails.interview-scheduled')
            ->with([
                'candidateName' => $application->candidate->name,
                'jobTitle' => $application->jobPosting->title,
                'interview' => $this->interview,
            ]);
    }
}
