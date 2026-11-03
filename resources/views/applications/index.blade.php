@extends('layouts.app')

@section('title', 'Semua Pelamar')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold" style="color: var(--ink);">Semua Pelamar</h1>
        <a href="{{ route('applications.export', request()->query()) }}" class="px-4 py-2 rounded-md text-sm border" style="border-color: var(--line);">Ekspor CSV</a>
    </div>

    <form method="GET" action="{{ route('applications.index') }}" class="bg-white border rounded-lg p-4 mb-6 grid grid-cols-2 md:grid-cols-5 gap-3 items-end" style="border-color: var(--line);">
        <div class="col-span-2 md:col-span-1">
            <label class="block text-xs mb-1" style="color: var(--ink-soft);">Cari nama / email</label>
            <input type="text" name="q" value="{{ request('q') }}" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);">
        </div>
        <div>
            <label class="block text-xs mb-1" style="color: var(--ink-soft);">Lowongan</label>
            <select name="job" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);">
                <option value="">Semua</option>
                @foreach ($jobs as $job)
                    <option value="{{ $job->id }}" @selected((int) request('job') === $job->id)>{{ $job->title }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs mb-1" style="color: var(--ink-soft);">Tahap</label>
            <select name="stage" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);">
                <option value="">Semua</option>
                @foreach ($stages as $s)
                    <option value="{{ $s }}" @selected(request('stage') === $s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs mb-1" style="color: var(--ink-soft);">Skor AI minimal</label>
            <input type="number" name="min_score" min="0" max="100" value="{{ request('min_score') }}" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 rounded-md text-white text-sm" style="background: var(--accent);">Terapkan</button>
            <a href="{{ route('applications.index') }}" class="px-3 py-2 text-sm" style="color: var(--ink-soft);">Reset</a>
        </div>
    </form>

    <div class="bg-white rounded-lg border overflow-hidden" style="border-color: var(--line);">
        <table class="w-full text-sm">
            <thead style="background: var(--paper);">
                <tr class="text-left" style="color: var(--ink-soft);">
                    <th class="px-4 py-3">Kandidat</th>
                    <th class="px-4 py-3">Posisi</th>
                    <th class="px-4 py-3">Skor AI</th>
                    <th class="px-4 py-3">Tahap</th>
                    <th class="px-4 py-3">Melamar</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($applications as $a)
                    <tr class="border-t" style="border-color: var(--line);">
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $a->candidate->name }}</p>
                            <p class="text-xs" style="color: var(--ink-soft);">{{ $a->candidate->email }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $a->jobPosting->title ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $a->ai_score ?? '-' }}</td>
                        <td class="px-4 py-3"><span class="stage-pill px-2 py-1 rounded text-xs">{{ $a->stage }}</span></td>
                        <td class="px-4 py-3 text-xs" style="color: var(--ink-soft);">{{ $a->created_at->translatedFormat('d M Y') }}<br>{{ $a->created_at->format('H:i') }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('pipeline.show', $a) }}" class="text-sm" style="color: var(--accent);">Lihat</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center" style="color: var(--ink-soft);">Tidak ada pelamar yang cocok dengan filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $applications->links() }}</div>
@endsection
