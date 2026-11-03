@extends('layouts.app')

@section('title', $job->title)

@section('content')
    @php $canEdit = auth()->user()->isRecruiter(); @endphp

    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold" style="color: var(--ink);">{{ $job->title }}</h1>
            <div class="flex items-center gap-2 mt-1 text-sm" style="color: var(--ink-soft);">
                <span>{{ $job->department->name ?? 'Tanpa departemen' }} &middot; {{ $job->location ?? '-' }} &middot;</span>
                @if ($canEdit)
                    <form method="POST" action="{{ route('jobs.status', $job) }}">
                        @csrf
                        @method('PATCH')
                        <select name="status" onchange="this.form.submit()" class="border rounded-md px-2 py-1 text-xs" style="border-color: var(--line);">
                            @foreach (['draft' => 'Draft', 'open' => 'Buka', 'closed' => 'Tutup', 'archived' => 'Arsip'] as $val => $label)
                                <option value="{{ $val }}" @selected($job->status === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                @else
                    <span>{{ ucfirst($job->status) }}</span>
                @endif
            </div>
        </div>
        <div class="space-x-2">
            <a href="{{ route('pipeline.board', $job) }}" class="px-4 py-2 rounded-md text-white text-sm" style="background: var(--accent);">Buka Pipeline</a>
            @if ($canEdit)
                <a href="{{ route('jobs.edit', $job) }}" class="px-4 py-2 rounded-md text-sm border" style="border-color: var(--line);">Edit</a>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg border p-5 mb-6" style="border-color: var(--line);">
        <h2 class="font-medium mb-2">Kualifikasi</h2>
        <p class="text-sm whitespace-pre-line" style="color: var(--ink-soft);">{{ $job->requirements }}</p>
    </div>

    <div class="flex items-center justify-between mb-3">
        <h2 class="font-medium">Pelamar (urut skor AI tertinggi)</h2>
        @if ($canEdit && $applications->total() > 0)
            <form method="POST" action="{{ route('jobs.rescreen-all', $job) }}"
                  onsubmit="return confirm('Nilai ulang {{ $applications->total() }} pelamar dengan AI? Ini memakai kuota/biaya API sebanyak jumlah pelamar.')">
                @csrf
                <button type="submit" class="px-3 py-1.5 rounded-md text-xs border" style="border-color: var(--line);">Nilai Ulang Semua dengan AI</button>
            </form>
        @endif
    </div>

    <div class="bg-white rounded-lg border overflow-hidden" style="border-color: var(--line);">
        <table class="w-full text-sm">
            <thead style="background: var(--paper);">
                <tr class="text-left" style="color: var(--ink-soft);">
                    <th class="px-4 py-3">Kandidat</th>
                    <th class="px-4 py-3">Skor AI</th>
                    <th class="px-4 py-3">Rekomendasi</th>
                    <th class="px-4 py-3">Tahap</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($applications as $app)
                    <tr class="border-t" style="border-color: var(--line);">
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $app->candidate->name }}</p>
                            <p class="text-xs" style="color: var(--ink-soft);">{{ $app->candidate->email }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $app->ai_score ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $app->ai_recommendation ?? 'Belum discreening' }}</td>
                        <td class="px-4 py-3"><span class="stage-pill px-2 py-1 rounded text-xs">{{ $app->stage }}</span></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('pipeline.show', $app) }}" class="text-sm" style="color: var(--accent);">Lihat</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center" style="color: var(--ink-soft);">Belum ada pelamar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $applications->links() }}</div>
@endsection
