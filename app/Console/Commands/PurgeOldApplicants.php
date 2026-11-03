<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\Candidate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeOldApplicants extends Command
{
    protected $signature = 'data:purge-rejected {--days=365 : Hapus pelamar ditolak yang lebih lama dari N hari} {--dry-run : Hanya tampilkan, jangan hapus}';

    protected $description = 'Retensi data: hapus lamaran yang ditolak sudah lama, beserta CV dan data kandidat yang tidak punya lamaran lain.';

    public function handle(): int
    {
        $days = max(30, (int) $this->option('days'));
        $dry = (bool) $this->option('dry-run');
        $cutoff = now()->subDays($days);

        $applications = Application::where('status', 'rejected')->where('updated_at', '<', $cutoff)->get();
        $candidateIds = $applications->pluck('candidate_id')->unique();

        $this->info(($dry ? '[SIMULASI] ' : '') . "{$applications->count()} lamaran ditolak lebih dari {$days} hari.");

        if (! $dry) {
            foreach ($applications as $application) {
                $application->delete(); // riwayat tahap, interview, dan tes ikut terhapus (cascade)
            }
        }

        $removed = 0;

        $deletedIds = $applications->pluck('id');

        Candidate::whereIn('id', $candidateIds)->each(function (Candidate $candidate) use ($dry, $deletedIds, &$removed) {
            // Masih punya lamaran lain (di luar yang akan dihapus)? Jangan hapus kandidatnya.
            if ($candidate->applications()->whereNotIn('id', $deletedIds)->exists()) {
                return;
            }

            if (! $dry) {
                if ($candidate->cv_path) {
                    Storage::disk('local')->delete($candidate->cv_path);
                    Storage::disk('public')->delete($candidate->cv_path);
                }
                $candidate->delete();
            }

            $removed++;
        });

        $this->info(($dry ? '[SIMULASI] ' : '') . "{$removed} kandidat (beserta file CV) dihapus karena tidak punya lamaran lain.");

        return self::SUCCESS;
    }
}
