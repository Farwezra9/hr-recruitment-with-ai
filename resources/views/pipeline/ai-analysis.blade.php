@php
    // Ubah nilai apa pun (teks, daftar teks, daftar objek) jadi satu teks yang rapi.
    $flat = function ($value) {
        if (is_string($value)) {
            return $value;
        }
        if (! is_array($value)) {
            return '';
        }

        return collect($value)
            ->map(fn ($v) => is_array($v) ? collect($v)->flatten()->filter()->implode(' - ') : (string) $v)
            ->filter()
            ->implode(', ');
    };

    $analysis = is_array($application->ai_analysis) ? $application->ai_analysis : [];
    $breakdown = is_array($analysis['score_breakdown'] ?? null) ? $analysis['score_breakdown'] : null;
@endphp

@if (($aiStatus ?? 'idle') === 'failed')
    <p class="text-sm mb-3" style="color: var(--warn);">
        Screening gagal diproses. Semua penyedia AI mungkin sedang bermasalah, coba jalankan ulang beberapa saat lagi.
    </p>
@endif

@if (! empty($analysis['injection_suspected']))
    <div class="flash flash-error mb-3 text-sm" role="alert">
        Peringatan: teks CV berisi kalimat yang mencoba mengarahkan AI. Skor di bawah mungkin tidak bisa dipercaya, periksa CV secara manual.
    </div>
@endif

@if ($application->ai_score !== null)
    <div class="flex items-center gap-3 mb-3">
        <span class="text-3xl font-semibold" style="color: var(--accent);">{{ $application->ai_score }}</span>
        <span class="text-sm px-2 py-1 rounded stage-pill">{{ $application->ai_recommendation }}</span>
    </div>

    @if ($breakdown)
        <p class="text-xs mb-3" style="color: var(--ink-soft);">
            Rincian: skill {{ $breakdown['skills'] ?? 0 }}/40 &middot; pengalaman {{ $breakdown['experience'] ?? 0 }}/30
            &middot; pendidikan {{ $breakdown['education'] ?? 0 }}/15 &middot; lainnya {{ $breakdown['other'] ?? 0 }}/15
        </p>
    @endif

    <p class="text-sm mb-4" style="color: var(--ink-soft);">{{ $application->ai_summary }}</p>

    @if ($flat($analysis['matched_skills'] ?? null) !== '')
        <p class="text-xs font-medium mb-1">Skill yang cocok</p>
        <p class="text-sm mb-3">{{ $flat($analysis['matched_skills']) }}</p>
    @endif

    @if ($flat($analysis['missing_skills'] ?? null) !== '')
        <p class="text-xs font-medium mb-1">Skill yang belum terlihat</p>
        <p class="text-sm mb-3">{{ $flat($analysis['missing_skills']) }}</p>
    @endif

    @if ($flat($analysis['strengths'] ?? null) !== '')
        <p class="text-xs font-medium mb-1">Kekuatan</p>
        <p class="text-sm mb-3">{{ $flat($analysis['strengths']) }}</p>
    @endif

    @if ($flat($analysis['concerns'] ?? null) !== '')
        <p class="text-xs font-medium mb-1" style="color: var(--warn);">Catatan / red flag</p>
        <p class="text-sm">{{ $flat($analysis['concerns']) }}</p>
    @endif
@elseif ($application->ai_summary)
    <p class="text-sm" style="color: var(--ink-soft);">{{ $application->ai_summary }}</p>
@else
    <p class="text-sm" style="color: var(--ink-soft);">Belum ada hasil screening AI untuk kandidat ini.</p>
@endif
