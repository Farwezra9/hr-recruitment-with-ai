@extends('layouts.app')

@section('title', 'Edit Lowongan')

@section('content')
    <h1 class="text-2xl font-semibold mb-6" style="color: var(--ink);">Edit Lowongan: {{ $job->title }}</h1>

    <form method="POST" action="{{ route('jobs.update', $job) }}" class="bg-white p-6 rounded-lg border space-y-4 max-w-2xl" style="border-color: var(--line);">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium mb-1">Judul Posisi</label>
            <input type="text" name="title" value="{{ old('title', $job->title) }}" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Departemen</label>
            <select name="department_id" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);">
                <option value="">- Tidak ada -</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" @selected((int) old('department_id', $job->department_id) === $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Deskripsi Pekerjaan</label>
            <textarea name="description" rows="3" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);" required>{{ old('description', $job->description) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Kualifikasi / Persyaratan</label>
            <textarea name="requirements" rows="5" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);" required>{{ old('requirements', $job->requirements) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Tanggung Jawab</label>
            <textarea name="responsibilities" rows="3" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);">{{ old('responsibilities', $job->responsibilities) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Lokasi</label>
                <input type="text" name="location" value="{{ old('location', $job->location) }}" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Jumlah Kebutuhan</label>
                <input type="number" name="vacancies" value="{{ old('vacancies', $job->vacancies) }}" min="1" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);" required>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Tipe Kerja</label>
                <select name="employment_type" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);">
                    @foreach (['full_time' => 'Full-time', 'part_time' => 'Part-time', 'contract' => 'Kontrak', 'internship' => 'Magang'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('employment_type', $job->employment_type) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Mode Kerja</label>
                <select name="work_mode" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);">
                    @foreach (['onsite' => 'Onsite', 'remote' => 'Remote', 'hybrid' => 'Hybrid'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('work_mode', $job->work_mode) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select name="status" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);">
                    @foreach (['draft' => 'Draft', 'open' => 'Buka', 'closed' => 'Tutup', 'archived' => 'Arsip'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('status', $job->status) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="pt-2">
            <button type="submit" class="px-5 py-2 rounded-md text-white text-sm" style="background: var(--accent);">Simpan Perubahan</button>
            <a href="{{ route('jobs.show', $job) }}" class="ml-2 text-sm" style="color: var(--ink-soft);">Batal</a>
        </div>
    </form>

    {{-- Form hapus sengaja DI LUAR form edit (HTML tidak mengizinkan form bersarang). --}}
    <form method="POST" action="{{ route('jobs.destroy', $job) }}"
          onsubmit="return confirm('Hapus lowongan ini beserta semua lamarannya? Tindakan ini tidak bisa dibatalkan.')"
          class="max-w-2xl mt-4">
        @csrf
        @method('DELETE')
        <button type="submit" class="px-4 py-2 rounded-md text-sm border" style="border-color: var(--warn); color: var(--warn);">Hapus Lowongan</button>
    </form>
@endsection