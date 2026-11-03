<?php

namespace App\Services;

use App\Mail\ApplicationStatusChangedMail;
use App\Models\Application;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Satu-satunya tempat logika pindah tahap lamaran, dipakai oleh web dan REST API.
 */
class ApplicationStageService
{
    public const STAGES = [
        'applied', 'ai_screening', 'shortlisted', 'assessment', 'interview', 'offer', 'hired', 'rejected',
    ];

    /** Perpindahan ke tahap ini mengirim email ke kandidat. */
    public const EMAIL_STAGES = ['shortlisted', 'offer', 'hired', 'rejected'];

    public function move(Application $application, string $stage, ?User $by = null, ?string $notes = null): Application
    {
        if ($stage === $application->stage) {
            return $application;
        }

        $from = $application->stage;
        $application->moveToStage($stage, $by?->id, $notes);
        Audit::log('application.stage_changed', $application, "Tahap {$from} -> {$stage}", ['from' => $from, 'to' => $stage], $by);

        if ($stage === 'rejected') {
            $application->update([
                'status' => 'rejected',
                'rejected_by' => $by?->id,
                'rejection_reason' => $notes,
            ]);
        } elseif ($stage === 'hired') {
            $application->update(['status' => 'hired', 'rejection_reason' => null]);
        } else {
            // Dikembalikan dari rejected/hired: aktifkan lagi
            $application->update(['status' => 'active', 'rejection_reason' => null]);
        }

        if (in_array($stage, self::EMAIL_STAGES, true)) {
            try {
                $application->loadMissing('candidate');
                Mail::to($application->candidate->email)->send(
                    new ApplicationStatusChangedMail($application->fresh(), $stage)
                );
            } catch (\Throwable $e) {
                // Gagal kirim email tidak boleh membatalkan perpindahan tahap.
                Log::warning('Gagal mengirim email status lamaran', [
                    'application_id' => $application->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $application->fresh();
    }
}
