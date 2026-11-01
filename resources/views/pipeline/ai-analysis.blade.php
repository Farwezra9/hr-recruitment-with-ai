@if (($aiStatus ?? 'idle') === 'failed')
    <p class="text-sm mb-3" style="color: var(--warn);">
        Screening gagal diproses. Semua penyedia AI mungkin sedang bermasalah, coba jalankan ulang beberapa saat lagi.
    </p>
@endif

@if ($application->ai_score !== null)
    <div class="flex items-center gap-3 mb-3">
        <span class="text-3xl font-semibold" style="color: var(--accent);">{{ $application->ai_score }}</span>
        <span class="text-sm px-2 py-1 rounded stage-pill">{{ $application->ai_recommendation }}</span>
    </div>
    <p class="text-sm mb-4" style="color: var(--ink-soft);">{{ $application->ai_summary }}</p>

    @if (!empty($application->ai_analysis['matched_skills']))
        <p class="text-xs font-medium mb-1">Skill yang cocok</p>
        <p class="text-sm mb-3">{{ implode(', ', $application->ai_analysis['matched_skills']) }}</p>
    @endif

    @if (!empty($application->ai_analysis['missing_skills']))
        <p class="text-xs font-medium mb-1">Skill yang belum terlihat</p>
        <p class="text-sm mb-3">{{ implode(', ', $application->ai_analysis['missing_skills']) }}</p>
    @endif

    @if (!empty($application->ai_analysis['concerns']))
        <p class="text-xs font-medium mb-1" style="color: var(--warn);">Catatan / red flag</p>
        <p class="text-sm">{{ implode(', ', $application->ai_analysis['concerns']) }}</p>
    @endif
@elseif ($application->ai_summary)
    <p class="text-sm" style="color: var(--ink-soft);">{{ $application->ai_summary }}</p>
@else
    <p class="text-sm" style="color: var(--ink-soft);">Belum ada hasil screening AI untuk kandidat ini.</p>
@endif