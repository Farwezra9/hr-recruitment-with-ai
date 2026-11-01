@extends('layouts.app')

@section('title', 'Buat Tes Baru')

@section('content')
    <h1 class="text-2xl font-semibold mb-6" style="color: var(--ink);">Buat Tes Baru</h1>

    <form method="POST" action="{{ route('assessments.store') }}" class="bg-white p-6 rounded-lg border space-y-4 max-w-lg" style="border-color: var(--line);">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">Judul Tes</label>
            <input type="text" name="title" value="{{ old('title') }}" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Deskripsi</label>
            <textarea name="description" rows="3" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);">{{ old('description') }}</textarea>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Tipe</label>
                <select name="type" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);">
                    <option value="technical">Technical</option>
                    <option value="psychometric">Psikotes</option>
                    <option value="language">Bahasa</option>
                    <option value="case_study">Studi Kasus</option>
                    <option value="other">Lainnya</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Durasi (menit)</label>
                <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 30) }}" min="5" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Nilai Lulus</label>
                <input type="number" name="passing_score" value="{{ old('passing_score', 60) }}" min="0" max="100" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);" required>
            </div>
        </div>

        <button type="submit" class="px-5 py-2 rounded-md text-white text-sm" style="background: var(--accent);">Buat & Tambahkan Soal</button>
    </form>
@endsection
