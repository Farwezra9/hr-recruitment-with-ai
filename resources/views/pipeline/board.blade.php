@extends('layouts.app')

@section('title', 'Pipeline - ' . $job->title)

@section('content')
    <div class="flex items-center justify-between mb-2">
        <h1 class="text-2xl font-semibold" style="color: var(--ink);">Pipeline: {{ $job->title }}</h1>
        <a href="{{ route('jobs.show', $job) }}" class="text-sm" style="color: var(--ink-soft);">&larr; Detail lowongan</a>
    </div>
    @if ($canMove)
        <p class="text-xs mb-6" style="color: var(--ink-soft);">Tarik kartu kandidat ke kolom lain untuk memindahkan tahap. Perpindahan ke Shortlist, Penawaran, Diterima, atau Ditolak mengirim email ke kandidat.</p>
    @else
        <p class="text-xs mb-6" style="color: var(--ink-soft);">Mode lihat saja.</p>
    @endif

    <div id="board" class="flex gap-4 overflow-x-auto pb-4">
        @foreach ($stages as $stage)
            @php $items = $applications->get($stage, collect()); @endphp
            <div class="w-72 shrink-0 rounded-md p-2" style="background: var(--paper); border: 1px solid var(--line);">
                <div class="flex items-center justify-between mb-2 px-1">
                    <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--ink-soft);"><span class="dot dot-{{ $stage }}"></span>{{ str_replace('_', ' ', $stage) }}</p>
                    <span class="text-xs count" style="color: var(--ink-soft);">{{ $items->count() }}</span>
                </div>
                <div data-stage="{{ $stage }}" class="space-y-2 min-h-24 rounded-md p-1 transition">
                    @foreach ($items as $app)
                        <a href="{{ route('pipeline.show', $app) }}"
                           data-card
                           data-move-url="{{ route('pipeline.move-stage', $app) }}"
                           @if ($canMove) draggable="true" @endif
                           class="block bg-white border rounded-md p-3 hover:shadow-sm transition {{ $canMove ? 'cursor-grab' : '' }}" style="border-color: var(--line);">
                            <p class="text-sm font-medium">{{ $app->candidate->name }}</p>
                            @if ($app->ai_score !== null)
                                <p class="text-xs mt-1" style="color: var(--accent);">Skor AI: {{ $app->ai_score }}</p>
                            @else
                                <p class="text-xs mt-1" style="color: var(--ink-soft);">Menunggu AI screening…</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    @if ($canMove)
    <script>
        (function () {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const emailStages = ['shortlisted', 'offer', 'hired'];
            let dragged = null;

            function updateCounts() {
                document.querySelectorAll('[data-stage]').forEach(function (col) {
                    col.parentElement.querySelector('.count').textContent = col.querySelectorAll('[data-card]').length;
                });
            }

            document.querySelectorAll('[data-card]').forEach(function (card) {
                card.addEventListener('dragstart', function (e) {
                    dragged = card;
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', card.dataset.moveUrl);
                    card.style.opacity = '0.4';
                });
                card.addEventListener('dragend', function () {
                    card.style.opacity = '';
                    document.querySelectorAll('[data-stage]').forEach(function (c) { c.classList.remove('drag-over'); });
                });
            });

            document.querySelectorAll('[data-stage]').forEach(function (col) {
                col.addEventListener('dragover', function (e) {
                    e.preventDefault();
                    col.classList.add('drag-over');
                });
                col.addEventListener('dragleave', function () { col.classList.remove('drag-over'); });

                col.addEventListener('drop', async function (e) {
                    e.preventDefault();
                    col.classList.remove('drag-over');
                    if (!dragged) return;

                    const fromCol = dragged.closest('[data-stage]');
                    const toStage = col.dataset.stage;
                    if (fromCol === col) return;

                    let notes = '';
                    if (toStage === 'rejected') {
                        notes = window.prompt('Alasan penolakan (opsional). Teks ini ikut dikirim ke kandidat lewat email:', '');
                        if (notes === null) return; // dibatalkan
                    } else if (emailStages.includes(toStage)) {
                        if (!window.confirm('Memindahkan ke "' + toStage + '" akan mengirim email ke kandidat. Lanjutkan?')) return;
                    }

                    const card = dragged;
                    col.appendChild(card);
                    updateCounts();

                    try {
                        const res = await fetch(card.dataset.moveUrl, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ stage: toStage, notes: notes })
                        });
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                    } catch (err) {
                        fromCol.appendChild(card); // batalkan perpindahan di layar
                        updateCounts();
                        window.alert('Gagal memindahkan kandidat (' + err.message + '). Coba lagi.');
                    }
                });
            });
        })();
    </script>
    @endif
@endsection
