@extends('layouts.app')

@section('title', 'Dasbor')

@section('content')
    @php
        $hour = (int) now()->format('G');
        $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));

        // [label, nilai, warna, ikon]
        $cards = [
            ['Lowongan terbuka', $stats['open_jobs'], 'purple', 'briefcase'],
            ['Total pelamar', $stats['applications'], 'blue', 'users'],
            ['Pelamar 7 hari terakhir', $stats['this_week'], 'teal', 'trending'],
            ['Menunggu screening AI', $stats['pending_screening'], 'orange', 'cpu'],
            ['Rata-rata skor AI', $stats['avg_score'] !== null ? round($stats['avg_score']) : '-', 'pink', 'star'],
            ['Diterima (hired)', $stats['hired'], 'green', 'award'],
            ['Rata-rata waktu rekrut', $stats['time_to_hire'] !== null ? round($stats['time_to_hire'], 1) . ' hari' : '-', 'yellow', 'clock'],
        ];

        $stageLabels = ['applied' => 'Baru masuk', 'ai_screening' => 'Screening AI', 'shortlisted' => 'Shortlist', 'assessment' => 'Tes', 'interview' => 'Interview', 'offer' => 'Penawaran', 'hired' => 'Diterima', 'rejected' => 'Ditolak'];
        $stageColors = ['applied' => 'blue', 'ai_screening' => 'purple', 'shortlisted' => 'yellow', 'assessment' => 'orange', 'interview' => 'pink', 'offer' => 'teal', 'hired' => 'green', 'rejected' => 'red'];
        $maxCount = max(1, $stageCounts->max() ?? 1);
    @endphp

    <div class="flex items-end justify-between mb-6 pb-4" style="border-bottom: 1px solid var(--line);">
        <div>
            <h1 class="text-2xl font-semibold">{{ $greeting }}, {{ strtok(auth()->user()->name, ' ') }}</h1>
            <p class="muted">{{ now()->translatedFormat('l, d F Y') }} &middot; ringkasan proses rekrutmen saat ini.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        @foreach ($cards as [$label, $value, $color, $icon])
            <div class="stat-card" style="--stat: var(--c-{{ $color }}); --stat-bg: var(--c-{{ $color }}-bg);">
                <div class="flex items-center gap-3">
                    <span class="stat-ico">@include('partials.icon', ['name' => $icon, 'class' => '', 'size' => 18])</span>
                    <div>
                        <p class="stat-val">{{ $value }}</p>
                        <p class="muted text-xs">{{ $label }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="gh-box">
            <div class="gh-box-header">Pelamar per tahap</div>
            <div class="gh-box-body space-y-3">
                @foreach ($stageLabels as $key => $label)
                    @php $count = (int) ($stageCounts[$key] ?? 0); $color = $stageColors[$key]; @endphp
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span><span class="dot dot-{{ $key }}"></span>{{ $label }}</span>
                            <span class="muted">{{ $count }}</span>
                        </div>
                        <div style="height: 8px; border-radius: 4px; background: var(--line-muted);">
                            <div style="height: 8px; border-radius: 4px; width: {{ round($count / $maxCount * 100) }}%; background: var(--c-{{ $color }});"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="gh-box">
            <div class="gh-box-header">Interview mendatang</div>
            <div>
                @forelse ($upcoming as $i)
                    <div class="gh-row">
                        <div>
                            <p class="font-semibold">{{ $i->application->candidate->name }}</p>
                            <p class="muted text-xs">{{ $i->application->jobPosting->title }} &middot; {{ $i->interviewer->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold" style="color: var(--c-orange);">{{ $i->scheduled_at->translatedFormat('l, d M') }}</p>
                            <p class="muted text-xs">{{ $i->scheduled_at->format('H:i') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="gh-box-body muted">Tidak ada interview terjadwal.</p>
                @endforelse
            </div>
        </div>

        <div class="gh-box">
            <div class="gh-box-header">Kandidat skor AI tertinggi</div>
            <div>
                @forelse ($topCandidates as $a)
                    <a href="{{ route('pipeline.show', $a) }}" class="gh-row">
                        <div>
                            <p class="font-semibold" style="color: var(--accent);">{{ $a->candidate->name }}</p>
                            <p class="muted text-xs">{{ $a->jobPosting->title }}</p>
                        </div>
                        <span class="stage-pill {{ $a->ai_score >= 70 ? 'pill-green' : ($a->ai_score >= 40 ? 'pill-yellow' : 'pill-red') }}" data-colored="1">{{ $a->ai_score }}</span>
                    </a>
                @empty
                    <p class="gh-box-body muted">Belum ada kandidat yang dinilai AI.</p>
                @endforelse
            </div>
        </div>

        <div class="gh-box">
            <div class="gh-box-header">Lowongan terbuka</div>
            <div>
                @forelse ($openJobs as $job)
                    <a href="{{ route('pipeline.board', $job) }}" class="gh-row">
                        <span class="font-semibold">{{ $job->title }}</span>
                        <span class="stage-pill pill-blue" data-colored="1">{{ $job->applications_count }} pelamar</span>
                    </a>
                @empty
                    <p class="gh-box-body muted">Belum ada lowongan terbuka.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
