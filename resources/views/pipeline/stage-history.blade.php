<ul class="space-y-2 text-sm">
    @forelse ($application->stageHistories as $history)
        <li class="border-b pb-2" style="border-color: var(--line);">
            <span class="font-medium">{{ $history->to_stage }}</span>
            <span style="color: var(--ink-soft);" title="{{ $history->created_at->format('d M Y H:i:s') }}"> &middot; {{ $history->created_at->translatedFormat('l, d M Y H:i') }} ({{ $history->created_at->diffForHumans() }})</span>
            @if ($history->notes)
                <p style="color: var(--ink-soft);">{{ $history->notes }}</p>
            @endif
        </li>
    @empty
        <li style="color: var(--ink-soft);">Belum ada riwayat.</li>
    @endforelse
</ul>
