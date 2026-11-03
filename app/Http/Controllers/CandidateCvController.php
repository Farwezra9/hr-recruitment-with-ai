<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Interview;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CandidateCvController extends Controller
{
    /**
     * CV hanya bisa dibuka staff yang login, atau interviewer yang ditugaskan untuk kandidat tersebut.
     * Setiap pembukaan dicatat di audit log (CV berisi data pribadi).
     */
    public function __invoke(Request $request, Candidate $candidate)
    {
        $user = $request->user();

        $allowed = $user->isStaff()
            || Interview::where('interviewer_id', $user->id)
                ->whereHas('application', fn ($q) => $q->where('candidate_id', $candidate->id))
                ->exists();

        abort_unless($allowed, 403);

        // File lama mungkin masih ada di disk public (sebelum update); cek keduanya.
        $disk = Storage::disk('local')->exists($candidate->cv_path) ? 'local' : 'public';
        abort_unless(Storage::disk($disk)->exists($candidate->cv_path), 404, 'File CV tidak ditemukan.');

        Audit::log('cv.viewed', $candidate, "Membuka CV {$candidate->name}");

        $extension = strtolower(pathinfo($candidate->cv_path, PATHINFO_EXTENSION));
        $filename = Str::slug($candidate->name) . '-cv.' . $extension;

        // PDF boleh tampil di browser; DOCX selalu diunduh
        return Storage::disk($disk)->response(
            $candidate->cv_path,
            $filename,
            ['X-Content-Type-Options' => 'nosniff'],
            $extension === 'pdf' ? 'inline' : 'attachment'
        );
    }
}
