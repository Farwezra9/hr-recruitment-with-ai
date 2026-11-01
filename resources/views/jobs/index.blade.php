@extends('layouts.app')

@section('title', 'Lowongan Pekerjaan')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold" style="color: var(--ink);">Lowongan Pekerjaan</h1>
        <a href="{{ route('jobs.create') }}" class="px-4 py-2 rounded-md text-white text-sm" style="background: var(--accent);">+ Buat Lowongan</a>
    </div>

    <div class="bg-white rounded-lg border overflow-hidden" style="border-color: var(--line);">
        <table class="w-full text-sm">
            <thead style="background: var(--paper);">
                <tr class="text-left" style="color: var(--ink-soft);">
                    <th class="px-4 py-3">Posisi</th>
                    <th class="px-4 py-3">Departemen</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Pelamar</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobs as $job)
                    <tr class="border-t" style="border-color: var(--line);">
                        <td class="px-4 py-3 font-medium">{{ $job->title }}</td>
                        <td class="px-4 py-3">{{ $job->department->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('jobs.status', $job) }}">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()"
                                        class="border rounded-md px-2 py-1 text-xs"
                                        style="border-color: var(--line); color: {{ $job->status === 'open' ? 'var(--accent)' : ($job->status === 'closed' ? 'var(--warn)' : 'var(--ink-soft)') }};">
                                    @foreach (['draft' => 'Draft', 'open' => 'Buka', 'closed' => 'Tutup', 'archived' => 'Arsip'] as $val => $label)
                                        <option value="{{ $val }}" @selected($job->status === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="px-4 py-3">{{ $job->applications_count }}</td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('pipeline.board', $job) }}" class="text-sm" style="color: var(--accent);">Pipeline</a>
                            <a href="{{ route('jobs.show', $job) }}" class="text-sm" style="color: var(--ink-soft);">Detail</a>
                            <a href="{{ route('jobs.edit', $job) }}" class="text-sm" style="color: var(--ink-soft);">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center" style="color: var(--ink-soft);">Belum ada lowongan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $jobs->links() }}</div>
@endsection