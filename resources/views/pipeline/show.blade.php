@extends('layouts.app')

@section('title', $application->candidate->name)

@section('content')
    @php
        $u = auth()->user();
        $canEdit = $u->isRecruiter();
    @endphp

    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold" style="color: var(--ink);">{{ $application->candidate->name }}</h1>
            <p class="text-sm mt-1" style="color: var(--ink-soft);">Melamar untuk {{ $application->jobPosting->title }}</p>
        </div>

        @if ($canEdit)
            <button type="button" id="rescreen-btn"
                    data-url="{{ route('pipeline.rescreen', $application) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm border disabled:opacity-60 disabled:cursor-not-allowed"
                    style="border-color: var(--line);">
                <svg id="rescreen-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12a9 9 0 1 1-3-6.7"/>
                    <path d="M21 3v6h-6"/>
                </svg>
                <span id="rescreen-label">Jalankan Ulang AI Screening</span>
            </button>
        @endif
    </div>

    <div id="ai-toast" class="hidden mb-6 rounded-md px-4 py-3 text-sm"></div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="bg-white rounded-lg border p-5" style="border-color: var(--line);">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-medium">Analisis AI</h2>
                    <span id="ai-running-badge" class="hidden items-center gap-2 text-xs" style="color: var(--accent);">
                        <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <path d="M21 12a9 9 0 1 1-6.2-8.55"/>
                        </svg>
                        AI sedang menganalisis…
                    </span>
                </div>
                <div id="ai-analysis-body">
                    @include('pipeline.ai-analysis', ['application' => $application, 'aiStatus' => $aiStatus])
                </div>
                <p class="text-xs mt-4 pt-3 border-t" style="border-color: var(--line); color: var(--ink-soft);">
                    Skor dan analisis dibuat otomatis oleh AI sebagai alat bantu penyaringan awal. Bisa keliru atau bias terhadap format CV tertentu, jadi keputusan akhir tetap ada pada tim rekrutmen.
                </p>
            </div>

            <div class="bg-white rounded-lg border p-5" style="border-color: var(--line);">
                <h2 class="font-medium mb-3">Interview</h2>

                @forelse ($application->interviews->sortByDesc('scheduled_at') as $interview)
                    @php $canFeedback = $canEdit || $interview->interviewer_id === $u->id; @endphp
                    <div class="border rounded-md p-3 mb-3 text-sm" style="border-color: var(--line);">
                        <div class="flex items-center justify-between">
                            <p class="font-medium">{{ $interview->scheduled_at->translatedFormat('D, d M Y H:i') }} &middot; {{ ucfirst($interview->type) }}</p>
                            <span class="stage-pill px-2 py-0.5 rounded text-xs">{{ $interview->status }}</span>
                        </div>
                        <p class="text-xs mt-1" style="color: var(--ink-soft);">
                            Interviewer: {{ $interview->interviewer->name }} &middot; {{ $interview->duration_minutes }} menit
                            @if ($interview->location_or_link) &middot; {{ $interview->location_or_link }} @endif
                        </p>

                        @if ($interview->rating || $interview->feedback)
                            <p class="mt-2">Rating: <strong>{{ $interview->rating ?? '-' }}/5</strong></p>
                            @if ($interview->feedback)
                                <p class="whitespace-pre-line" style="color: var(--ink-soft);">{{ $interview->feedback }}</p>
                            @endif
                        @endif

                        @if ($canFeedback)
                            <details class="mt-2">
                                <summary class="text-xs cursor-pointer" style="color: var(--accent);">Isi / ubah feedback</summary>
                                <form method="POST" action="{{ route('interviews.feedback', $interview) }}" class="mt-2 space-y-2">
                                    @csrf @method('PATCH')
                                    <div class="grid grid-cols-2 gap-2">
                                        <select name="status" class="border rounded-md px-2 py-1.5 text-sm" style="border-color: var(--line);">
                                            @foreach (['completed' => 'Selesai', 'no_show' => 'Tidak hadir', 'cancelled' => 'Dibatalkan'] as $val => $label)
                                                <option value="{{ $val }}" @selected($interview->status === $val)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <select name="rating" class="border rounded-md px-2 py-1.5 text-sm" style="border-color: var(--line);">
                                            <option value="">Rating</option>
                                            @for ($i = 1; $i <= 5; $i++)
                                                <option value="{{ $i }}" @selected($interview->rating === $i)>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <textarea name="feedback" rows="3" placeholder="Catatan interview" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);">{{ $interview->feedback }}</textarea>
                                    <button type="submit" class="px-3 py-1.5 rounded-md text-white text-xs" style="background: var(--accent);">Simpan Feedback</button>
                                </form>
                            </details>
                        @endif
                    </div>
                @empty
                    <p class="text-sm" style="color: var(--ink-soft);">Belum ada interview terjadwal.</p>
                @endforelse
            </div>

            <div class="bg-white rounded-lg border p-5" style="border-color: var(--line);">
                <h2 class="font-medium mb-3">Riwayat Tahap</h2>
                <div id="stage-history-body">
                    @include('pipeline.stage-history', ['application' => $application])
                </div>
            </div>
        </div>

        <div class="space-y-6">
            @if ($canEdit)
                <div class="bg-white rounded-lg border p-5" style="border-color: var(--line);">
                    <h2 class="font-medium mb-3">Pindah Tahap</h2>
                    <form method="POST" action="{{ route('pipeline.move-stage', $application) }}" class="space-y-3">
                        @csrf @method('PATCH')
                        <select name="stage" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);">
                            @foreach (['applied', 'ai_screening', 'shortlisted', 'assessment', 'interview', 'offer', 'hired', 'rejected'] as $s)
                                <option value="{{ $s }}" @selected($application->stage === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                        <textarea name="notes" rows="2" placeholder="Catatan (opsional)" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);"></textarea>
                        <button type="submit" class="w-full px-4 py-2 rounded-md text-white text-sm" style="background: var(--accent);">Perbarui</button>
                    </form>
                </div>

                <div class="bg-white rounded-lg border p-5" style="border-color: var(--line);">
                    <h2 class="font-medium mb-3">Jadwalkan Interview</h2>
                    <a href="{{ route('interviews.create', $application) }}" class="block text-center px-4 py-2 rounded-md text-sm border" style="border-color: var(--line);">+ Buat Jadwal</a>
                </div>
            @endif

            <div class="bg-white rounded-lg border p-5" style="border-color: var(--line);">
                <h2 class="font-medium mb-3">Tes Online</h2>

                @if ($canEdit)
                    <form method="POST" action="{{ route('assessments.assign', $application) }}" class="space-y-2">
                        @csrf
                        <select name="assessment_id" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);" required>
                            @foreach ($assessments as $a)
                                <option value="{{ $a->id }}">{{ $a->title }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="w-full px-4 py-2 rounded-md text-sm border" style="border-color: var(--line);">Kirim Tes ke Kandidat</button>
                    </form>
                @endif

                @foreach ($application->candidateAssessments as $ca)
                    <div class="mt-4 pt-3 border-t text-sm" style="border-color: var(--line);">
                        <p class="font-medium">{{ $ca->assessment->title }}</p>
                        <p style="color: var(--ink-soft);">
                            Status: {{ $ca->status }}
                            @if ($ca->score !== null)
                                &middot; Skor: <strong>{{ $ca->score }}</strong>
                                ({{ $ca->score >= $ca->assessment->passing_score ? 'lulus' : 'belum lulus' }}, batas {{ $ca->assessment->passing_score }})
                            @endif
                        </p>
                        @if ($ca->needsGrading())
                            <p class="text-xs mt-1" style="color: var(--warn);">Ada soal esai yang belum dinilai.</p>
                        @endif
                        @if ($canEdit && ! $ca->isFinished())
                            <p class="text-xs mt-1 break-all" style="color: var(--ink-soft);">Link: {{ route('assessments.take', $ca) }}<br>Berlaku sampai {{ $ca->expires_at?->format('d M Y H:i') }}</p>
                        @endif

                        @if ($ca->isFinished() && $ca->answers)
                            <details class="mt-2">
                                <summary class="text-xs cursor-pointer" style="color: var(--accent);">Lihat jawaban{{ $canEdit ? ' & nilai esai' : '' }}</summary>

                                <form method="POST" action="{{ route('assessments.grade', $ca) }}" class="mt-2 space-y-3">
                                    @csrf @method('PATCH')
                                    @foreach ($ca->assessment->questions as $q)
                                        @php $given = $ca->answers[$q->id] ?? null; @endphp
                                        <div class="text-xs border rounded-md p-2" style="border-color: var(--line);">
                                            <p class="font-medium">{{ $loop->iteration }}. {{ $q->question }}</p>
                                            @if ($q->question_type === 'multiple_choice')
                                                <p class="mt-1">Jawaban: {{ $given ? $given . '. ' . ($q->options[$given] ?? '') : '(kosong)' }}
                                                    @if ($given === $q->correct_option)
                                                        <span style="color: var(--accent);">&#10003; benar</span>
                                                    @else
                                                        <span style="color: var(--warn);">&#10007; salah (kunci: {{ $q->correct_option }})</span>
                                                    @endif
                                                </p>
                                            @else
                                                <p class="mt-1 whitespace-pre-line" style="color: var(--ink-soft);">{{ $given ?: '(kosong)' }}</p>
                                                @if ($canEdit)
                                                    <label class="flex items-center gap-2 mt-2">
                                                        Nilai (maks {{ $q->points }}):
                                                        <input type="number" step="0.5" min="0" max="{{ $q->points }}" name="essay_points[{{ $q->id }}]"
                                                               value="{{ $ca->essay_scores[$q->id] ?? '' }}"
                                                               class="w-20 border rounded-md px-2 py-1" style="border-color: var(--line);">
                                                    </label>
                                                @elseif (isset($ca->essay_scores[$q->id]))
                                                    <p class="mt-1">Nilai: {{ $ca->essay_scores[$q->id] }} / {{ $q->points }}</p>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach

                                    @if ($canEdit && $ca->assessment->questions->contains('question_type', 'essay'))
                                        <button type="submit" class="px-3 py-1.5 rounded-md text-white text-xs" style="background: var(--accent);">Simpan Nilai Esai</button>
                                    @endif
                                </form>
                            </details>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="bg-white rounded-lg border p-5" style="border-color: var(--line);">
                <h2 class="font-medium mb-2">Kontak Kandidat</h2>
                <p class="text-sm">{{ $application->candidate->email }}</p>
                <p class="text-sm" style="color: var(--ink-soft);">{{ $application->candidate->phone ?? '-' }}</p>
                <a href="{{ route('candidates.cv', $application->candidate) }}" target="_blank" class="text-sm inline-block mt-2" style="color: var(--accent);">Lihat CV asli ↗</a>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const btn = document.getElementById('rescreen-btn');
            const icon = document.getElementById('rescreen-icon');
            const label = document.getElementById('rescreen-label');
            const badge = document.getElementById('ai-running-badge');
            const body = document.getElementById('ai-analysis-body');
            const history = document.getElementById('stage-history-body');
            const toast = document.getElementById('ai-toast');
            const stageSelect = document.querySelector('select[name="stage"]');
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            const statusUrl = @json(route('pipeline.ai-status', $application));
            const POLL_MS = 3000;
            const MAX_WAIT_MS = 4 * 60 * 1000;

            let timer = null;
            let startedAt = 0;
            const initialStage = stageSelect ? stageSelect.value : null;

            function setRunning(running) {
                if (btn) {
                    btn.disabled = running;
                    icon.classList.toggle('animate-spin', running);
                    label.textContent = running ? 'Memproses…' : 'Jalankan Ulang AI Screening';
                }
                badge.classList.toggle('hidden', !running);
                badge.classList.toggle('inline-flex', running);
            }

            function showToast(message, ok) {
                toast.textContent = message;
                toast.classList.remove('hidden');
                toast.style.background = ok ? 'var(--success-soft)' : 'var(--danger-soft)';
                toast.style.color = 'var(--fg)';
                setTimeout(function () { toast.classList.add('hidden'); }, 8000);
            }

            function stopPolling() {
                if (timer) clearTimeout(timer);
                timer = null;
            }

            async function poll() {
                if (Date.now() - startedAt > MAX_WAIT_MS) {
                    stopPolling();
                    setRunning(false);
                    showToast('Proses terlalu lama. Pastikan "php artisan queue:work" sedang berjalan, lalu coba lagi.', false);
                    return;
                }

                let res;
                try {
                    res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                } catch (e) {
                    timer = setTimeout(poll, POLL_MS);
                    return;
                }

                if (!res.ok) {
                    stopPolling();
                    setRunning(false);
                    showToast('Gagal memuat hasil (HTTP ' + res.status + '). Cek storage/logs/laravel.log.', false);
                    return;
                }

                const data = await res.json();

                if (data.status === 'running') {
                    timer = setTimeout(poll, POLL_MS);
                    return;
                }

                if (typeof data.analysis_html !== 'string') {
                    stopPolling();
                    setRunning(false);
                    showToast('Respons server tidak lengkap. Muat ulang halaman.', false);
                    return;
                }

                body.innerHTML = data.analysis_html;
                history.innerHTML = data.history_html;

                if (stageSelect && stageSelect.value === initialStage) {
                    stageSelect.value = data.stage;
                }

                setRunning(false);
                showToast(
                    data.status === 'failed' ? 'AI screening gagal diproses.' : 'Hasil AI screening sudah diperbarui.',
                    data.status !== 'failed'
                );
            }

            function startPolling() {
                stopPolling();
                startedAt = Date.now();
                setRunning(true);
                timer = setTimeout(poll, POLL_MS);
            }

            if (btn) {
                btn.addEventListener('click', async function () {
                    setRunning(true);
                    try {
                        const res = await fetch(btn.dataset.url, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                        });
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        startPolling();
                    } catch (e) {
                        setRunning(false);
                        showToast('Gagal memulai screening: ' + e.message, false);
                    }
                });
            }

            @if ($aiStatus === 'running')
                startPolling();
            @endif
        })();
    </script>
@endsection
