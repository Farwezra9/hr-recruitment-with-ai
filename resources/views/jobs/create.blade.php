@extends('layouts.app')

@section('title', 'Buat Lowongan')

@section('content')
    <h1 class="text-2xl font-semibold mb-6" style="color: var(--ink);">Buat Lowongan Baru</h1>

    <form method="POST" action="{{ route('jobs.store') }}" class="bg-white p-6 rounded-lg border space-y-4 max-w-2xl" style="border-color: var(--line);">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">Judul Posisi</label>
            <input type="text" name="title" value="{{ old('title') }}" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Departemen</label>
            <select name="department_id" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);">
                <option value="">- Tidak ada -</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Deskripsi Pekerjaan</label>
            <textarea name="description" rows="3" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);" required>{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Kualifikasi / Persyaratan</label>
            <p class="text-xs mb-1" style="color: var(--ink-soft);">Teks ini akan dipakai sebagai acuan AI saat menyaring CV kandidat — tulis sedetail mungkin.</p>
            <textarea name="requirements" rows="5" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);" required>{{ old('requirements') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Tanggung Jawab (opsional)</label>
            <textarea name="responsibilities" rows="3" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);">{{ old('responsibilities') }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Lokasi</label>
                <input type="text" name="location" value="{{ old('location') }}" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Jumlah Kebutuhan</label>
                <input type="number" name="vacancies" value="{{ old('vacancies', 1) }}" min="1" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);" required>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Tipe Kerja</label>
                <select name="employment_type" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);">
                    <option value="full_time">Full-time</option>
                    <option value="part_time">Part-time</option>
                    <option value="contract">Kontrak</option>
                    <option value="internship">Magang</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Mode Kerja</label>
                <select name="work_mode" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);">
                    <option value="onsite">Onsite</option>
                    <option value="remote">Remote</option>
                    <option value="hybrid">Hybrid</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select name="status" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);">
                    <option value="draft">Draft</option>
                    <option value="open">Buka (tampil di career page)</option>
                    <option value="closed">Ditutup</option>
                </select>
            </div>
        </div>

        <div class="pt-2">
            <button type="submit" class="px-5 py-2 rounded-md text-white text-sm" style="background: var(--accent);">Simpan Lowongan</button>
        </div>
    </form>
@endsection
