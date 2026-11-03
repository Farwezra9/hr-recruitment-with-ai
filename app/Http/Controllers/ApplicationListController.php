<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\JobPosting;
use Illuminate\Http\Request;

class ApplicationListController extends Controller
{
    protected array $stages = [
        'applied', 'ai_screening', 'shortlisted', 'assessment', 'interview', 'offer', 'hired', 'rejected',
    ];

    public function index(Request $request)
    {
        $applications = $this->filtered($request)->paginate(20)->withQueryString();

        return view('applications.index', [
            'applications' => $applications,
            'jobs' => JobPosting::orderBy('title')->get(['id', 'title']),
            'stages' => $this->stages,
        ]);
    }

    /** Ekspor hasil filter ke CSV (bisa dibuka di Excel). */
    public function export(Request $request)
    {
        $rows = $this->filtered($request)->get();

        $safe = fn ($v) => is_string($v) && $v !== '' && in_array($v[0], ['=', '+', '-', '@', "\t", "\r"], true)
            ? "'" . $v
            : $v;

        return response()->streamDownload(function () use ($rows, $safe) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8 dengan benar

            fputcsv($out, ['Nama', 'Email', 'No. HP', 'Posisi', 'Tahap', 'Skor AI', 'Rekomendasi AI', 'Tanggal Melamar']);

            foreach ($rows as $a) {
                fputcsv($out, [
                    $safe($a->candidate->name),
                    $safe($a->candidate->email),
                    $safe($a->candidate->phone ?? ''),
                    $safe($a->jobPosting->title ?? ''),
                    $a->stage,
                    $a->ai_score ?? '',
                    $a->ai_recommendation ?? '',
                    $a->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($out);
        }, 'pelamar-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function filtered(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        return Application::with(['candidate', 'jobPosting'])
            ->when($term !== '', fn ($q) => $q->whereHas('candidate', function ($c) use ($term) {
                $c->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%");
            }))
            ->when($request->filled('job'), fn ($q) => $q->where('job_posting_id', (int) $request->query('job')))
            ->when($request->filled('stage'), fn ($q) => $q->where('stage', $request->query('stage')))
            ->when($request->filled('min_score'), fn ($q) => $q->where('ai_score', '>=', (int) $request->query('min_score')))
            ->orderByDesc('ai_score')
            ->orderByDesc('created_at');
    }
}
