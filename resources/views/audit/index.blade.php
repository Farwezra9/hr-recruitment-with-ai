@extends('layouts.app')

@section('title', 'Log Aktivitas')

@section('content')
    <div class="flex items-center justify-between mb-2">
        <h1 class="text-2xl font-semibold">Log Aktivitas</h1>
    </div>
    <p class="muted mb-6">Jejak aktivitas penting: login, perubahan akun, perpindahan tahap, dan pembukaan CV. Hanya admin yang bisa melihat halaman ini.</p>

    <form method="GET" action="{{ route('audit.index') }}" class="gh-box gh-box-body mb-6 grid grid-cols-2 md:grid-cols-4 gap-3 items-end">
        <div>
            <label class="form-label text-xs">Jenis aksi</label>
            <select name="action" class="w-full px-3 py-2">
                <option value="">Semua</option>
                @foreach ($actions as $a)
                    <option value="{{ $a }}" @selected(request('action') === $a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label text-xs">Pengguna</label>
            <select name="user" class="w-full px-3 py-2">
                <option value="">Semua</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((int) request('user') === $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label text-xs">Alamat IP</label>
            <input type="text" name="ip" value="{{ request('ip') }}" class="w-full px-3 py-2">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Terapkan</button>
            <a href="{{ route('audit.index') }}" class="btn">Reset</a>
        </div>
    </form>

    <div class="gh-box overflow-hidden">
        <table class="w-full text-sm">
            <thead style="background: var(--subtle);">
                <tr class="text-left muted">
                    <th class="px-4 py-3">Waktu</th>
                    <th class="px-4 py-3">Pengguna</th>
                    <th class="px-4 py-3">Aksi</th>
                    <th class="px-4 py-3">Keterangan</th>
                    <th class="px-4 py-3">IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    @php
                        $danger = str_contains($log->action, 'failed') || str_contains($log->action, 'lockout');
                    @endphp
                    <tr class="border-t" style="border-color: var(--line);">
                        <td class="px-4 py-3 whitespace-nowrap" title="{{ $log->created_at?->format('d M Y H:i:s') }}">
                            <div>{{ $log->created_at?->translatedFormat('l, d M Y') }}</div>
                            <div class="muted text-xs">{{ $log->created_at?->format('H:i:s') }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $log->user->name ?? ($log->metadata['email'] ?? '-') }}</td>
                        <td class="px-4 py-3"><span class="stage-pill {{ $danger ? 'pill-red' : 'pill-blue' }}" data-colored="1">{{ $log->action }}</span></td>
                        <td class="px-4 py-3">{{ $log->description }}</td>
                        <td class="px-4 py-3 muted text-xs">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center muted">Belum ada aktivitas tercatat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
