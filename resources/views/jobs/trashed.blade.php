@extends('layouts.app')

@section('title', 'Lowongan Terhapus')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold" style="color: var(--ink);">Lowongan Terhapus</h1>
        <a href="{{ route('jobs.index') }}" class="text-sm" style="color: var(--ink-soft);">&larr; Kembali ke lowongan</a>
    </div>

    <div class="bg-white rounded-lg border overflow-hidden" style="border-color: var(--line);">
        <table class="w-full text-sm">
            <thead style="background: var(--paper);">
                <tr class="text-left" style="color: var(--ink-soft);">
                    <th class="px-4 py-3">Posisi</th>
                    <th class="px-4 py-3">Pelamar</th>
                    <th class="px-4 py-3">Dihapus</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobs as $job)
                    <tr class="border-t" style="border-color: var(--line);">
                        <td class="px-4 py-3 font-medium">{{ $job->title }}</td>
                        <td class="px-4 py-3">{{ $job->applications_count }}</td>
                        <td class="px-4 py-3">{{ $job->deleted_at->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('jobs.restore', $job) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-sm" style="color: var(--accent);">Pulihkan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center" style="color: var(--ink-soft);">Tidak ada lowongan terhapus.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $jobs->links() }}</div>
@endsection
