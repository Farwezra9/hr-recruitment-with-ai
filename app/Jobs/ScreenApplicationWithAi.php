<?php

namespace App\Jobs;

use App\Models\Application;
use App\Services\ClaudeCvScreeningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ScreenApplicationWithAi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 15;

    public function __construct(public Application $application)
    {
        // Dijalankan saat job didaftarkan, jadi halaman langsung tahu proses sedang berjalan.
        $this->setStatus('running');
    }

    public function handle(ClaudeCvScreeningService $service): void
    {
        $this->setStatus('running');

        $service->screen($this->application);

        $this->setStatus('done');
    }

    /** Dipanggil hanya setelah semua percobaan ulang habis. */
    public function failed(\Throwable $e): void
    {
        $this->setStatus('failed');
    }

    protected function setStatus(string $status): void
    {
        Cache::put(self::cacheKey($this->application->id), $status, now()->addMinutes(15));
    }

    public static function cacheKey(int $applicationId): string
    {
        return "ai_screening:{$applicationId}";
    }
}