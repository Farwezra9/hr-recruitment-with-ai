@extends('layouts.public')

@section('title', 'Lamar - ' . $job->title)

@section('content')
    <a href="{{ route('career.index') }}" class="muted text-sm">&larr; Semua lowongan</a>

    <h1 class="text-2xl font-semibold mt-3 mb-1">{{ $job->title }}</h1>
    <p class="muted mb-6">
        {{ $job->location ?? 'Lokasi fleksibel' }}
        &middot; {{ ucfirst(str_replace('_', ' ', $job->employment_type)) }}
        &middot; {{ ucfirst($job->work_mode) }}
    </p>

    <div class="gh-box mb-8">
        <div class="gh-box-header">Tentang posisi ini</div>
        <div class="gh-box-body space-y-4">
            <p class="whitespace-pre-line">{{ $job->description }}</p>
            @if ($job->responsibilities)
                <div>
                    <h2 class="font-semibold mb-1">Tanggung jawab</h2>
                    <p class="whitespace-pre-line muted">{{ $job->responsibilities }}</p>
                </div>
            @endif
            <div>
                <h2 class="font-semibold mb-1">Kualifikasi</h2>
                <p class="whitespace-pre-line muted">{{ $job->requirements }}</p>
            </div>
        </div>
    </div>

    <h2 class="text-lg font-semibold mb-3">Kirim lamaran</h2>

    @if ($errors->any())
        <div class="flash flash-error mb-4" role="alert">
            <ul class="list-disc pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('career.submit', $job) }}" enctype="multipart/form-data" class="gh-box gh-box-body space-y-4">
        @csrf

        {{-- Honeypot anti-bot: disembunyikan dari manusia, bot biasanya mengisinya. --}}
        <div style="position:absolute; left:-9999px;" aria-hidden="true">
            <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
        </div>

        <div>
            <label class="form-label">Nama lengkap</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full px-3 py-2" required>
        </div>
        <div>
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full px-3 py-2" required>
        </div>
        <div>
            <label class="form-label">No. HP</label>
            <input type="text" name="phone" value="{{ old('phone') }}" class="w-full px-3 py-2">
        </div>
        <div>
            <label class="form-label">LinkedIn <span class="muted font-normal">(opsional)</span></label>
            <input type="url" name="linkedin_url" value="{{ old('linkedin_url') }}" class="w-full px-3 py-2">
        </div>
        <div>
            <label class="form-label">Upload CV</label>
            <input type="file" name="cv" accept=".pdf,.docx" class="w-full" required>
            <p class="muted text-xs mt-1">PDF atau DOCX, maksimal 5 MB. Gunakan CV yang teksnya bisa diseleksi (bukan hasil scan/foto) supaya terbaca sistem.</p>
        </div>

        <label class="flex items-start gap-2">
            <input type="checkbox" name="consent" value="1" class="mt-1" @checked(old('consent')) required>
            <span class="muted">
                Saya menyetujui data pribadi dan CV saya diproses untuk keperluan seleksi, termasuk dianalisis
                dengan bantuan AI melalui penyedia layanan pihak ketiga. Keputusan akhir tetap diambil oleh tim rekrutmen.
            </span>
        </label>

        <button type="submit" class="btn btn-primary btn-block" style="justify-content:center;">Kirim Lamaran</button>
    </form>
@endsection
