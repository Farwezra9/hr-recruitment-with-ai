<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Catatan jejak aktivitas penting (siapa, kapan, dari IP mana).
 * Tidak pernah melempar error: kegagalan mencatat tidak boleh menghentikan proses utama.
 */
class Audit
{
    public static function log(string $action, ?Model $subject = null, ?string $description = null, array $metadata = [], ?User $user = null): void
    {
        try {
            $request = request();

            AuditLog::create([
                'user_id' => ($user ?? $request?->user())?->id,
                'action' => $action,
                'subject_type' => $subject ? class_basename($subject) : null,
                'subject_id' => $subject?->getKey(),
                'description' => $description,
                'metadata' => $metadata ?: null,
                'ip_address' => $request?->ip(),
                'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 255) : null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal menulis audit log', ['action' => $action, 'error' => $e->getMessage()]);
        }
    }
}
