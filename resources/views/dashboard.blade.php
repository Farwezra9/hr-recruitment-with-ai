@extends('layouts.app')

@section('title', 'Dasbor')

@section('content')
    <h1 class="text-2xl font-semibold mb-1" style="color: var(--ink);">Dasbor Rekrutmen</h1>
    <p class="text-sm mb-8" style="color: var(--ink-soft);">Ringkasan proses rekrutmen yang sedang berjalan.</p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('jobs.index') }}" class="block p-5 rounded-lg border bg-white hover:shadow-sm transition" style="border-color: var(--line);">
            <p class="text-sm" style="color: var(--ink-soft);">Kelola</p>
            <p class="font-medium">Lowongan Pekerjaan</p>
        </a>
        <a href="{{ route('assessments.index') }}" class="block p-5 rounded-lg border bg-white hover:shadow-sm transition" style="border-color: var(--line);">
            <p class="text-sm" style="color: var(--ink-soft);">Kelola</p>
            <p class="font-medium">Bank Soal Tes</p>
        </a>
        <a href="{{ route('career.index') }}" class="block p-5 rounded-lg border bg-white hover:shadow-sm transition" style="border-color: var(--line);">
            <p class="text-sm" style="color: var(--ink-soft);">Lihat</p>
            <p class="font-medium">Career Page Publik</p>
        </a>
    </div>
@endsection
