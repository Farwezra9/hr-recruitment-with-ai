@extends('layouts.app')

@section('title', 'Bank Tes')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold" style="color: var(--ink);">Bank Tes / Assessment</h1>
        <a href="{{ route('assessments.create') }}" class="px-4 py-2 rounded-md text-white text-sm" style="background: var(--accent);">+ Buat Tes Baru</a>
    </div>

    <div class="bg-white rounded-lg border overflow-hidden" style="border-color: var(--line);">
        <table class="w-full text-sm">
            <thead style="background: var(--paper);">
                <tr class="text-left" style="color: var(--ink-soft);">
                    <th class="px-4 py-3">Judul Tes</th>
                    <th class="px-4 py-3">Tipe</th>
                    <th class="px-4 py-3">Jumlah Soal</th>
                    <th class="px-4 py-3">Nilai Lulus</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($assessments as $a)
                    <tr class="border-t" style="border-color: var(--line);">
                        <td class="px-4 py-3 font-medium">{{ $a->title }}</td>
                        <td class="px-4 py-3">{{ $a->type }}</td>
                        <td class="px-4 py-3">{{ $a->questions_count }}</td>
                        <td class="px-4 py-3">{{ $a->passing_score }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('assessments.edit', $a) }}" class="text-sm" style="color: var(--accent);">Kelola Soal</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center" style="color: var(--ink-soft);">Belum ada tes. Klik "+ Buat Tes Baru" untuk mulai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
