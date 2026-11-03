@extends('layouts.public')

@section('title', 'Karir')

@section('content')
    <h1 class="text-2xl font-semibold mb-1">Lowongan Terbuka</h1>
    <p class="muted mb-6">Temukan posisi yang sesuai dan kirim lamaranmu.</p>

    <div class="gh-box">
        @forelse ($jobs as $job)
            <a href="{{ route('career.apply', $job) }}" class="gh-row">
                <div>
                    <p class="font-semibold" style="color: var(--accent);">{{ $job->title }}</p>
                    <p class="muted text-xs mt-1">
                        {{ $job->location ?? 'Lokasi fleksibel' }}
                        &middot; {{ ucfirst(str_replace('_', ' ', $job->employment_type)) }}
                        @if ($job->closing_date) &middot; Ditutup {{ $job->closing_date->translatedFormat('d M Y') }} @endif
                    </p>
                </div>
                <span class="stage-pill">{{ ucfirst($job->work_mode) }}</span>
            </a>
        @empty
            <p class="gh-box-body muted">Belum ada lowongan yang dibuka saat ini.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $jobs->links() }}</div>
@endsection
