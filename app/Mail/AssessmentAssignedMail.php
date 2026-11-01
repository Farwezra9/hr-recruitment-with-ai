<?php

namespace App\Mail;

use App\Models\CandidateAssessment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AssessmentAssignedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public CandidateAssessment $candidateAssessment)
    {
    }

    public function build()
    {
        $this->candidateAssessment->loadMissing(['assessment', 'application.candidate', 'application.jobPosting']);
        $application = $this->candidateAssessment->application;

        return $this->subject('Tes Online - ' . $application->jobPosting->title)
            ->view('emails.assessment-assigned')
            ->with([
                'candidateName' => $application->candidate->name,
                'jobTitle' => $application->jobPosting->title,
                'candidateAssessment' => $this->candidateAssessment,
            ]);
    }
}
