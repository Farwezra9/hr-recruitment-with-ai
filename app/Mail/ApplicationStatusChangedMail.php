<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Application $application, public string $stage)
    {
    }

    protected array $subjects = [
        'shortlisted' => 'Kamu masuk shortlist!',
        'offer' => 'Selamat, kamu mendapatkan penawaran kerja',
        'hired' => 'Selamat bergabung!',
        'rejected' => 'Update lamaranmu',
    ];

    public function build()
    {
        $this->application->loadMissing(['candidate', 'jobPosting']);

        $subject = $this->subjects[$this->stage] ?? 'Update status lamaran';

        return $this->subject($subject . ' - ' . $this->application->jobPosting->title)
            ->view('emails.application-status-changed')
            ->with([
                'candidateName' => $this->application->candidate->name,
                'jobTitle' => $this->application->jobPosting->title,
                'stage' => $this->stage,
                'rejectionReason' => $this->application->rejection_reason,
            ]);
    }
}
