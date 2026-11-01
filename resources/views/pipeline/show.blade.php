@extends('layouts.app')

@section('title', $application->candidate->name)

@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold" style="color: var(--ink);">{{ $application->candidate->name }}</h1>
            <p class="text-sm mt-1" style="color: var(--ink-soft);">Melamar untuk {{ $application->jobPosting->title }}</p>
        </div>

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
            </div>

            <div class="bg-white rounded-lg border p-5" style="border-color: var(--line);">
                <h2 class="font-medium mb-3">Riwayat Tahap</h2>
                <div id="stage-history-body">
                    @include('pipeline.stage-history', ['application' => $application])
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg border p-5" style="border-color: var(--line);">
                <h2 class="font-medium mb-3">Pindah Tahap</h2>
                <form method="POST" action="{{ route('pipeline.move-stage', $application) }}" class="space-y-3">
                    @csrf @method('PATCH')
                    <select name="stage" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);">
                        @foreach (['applied', 'aiscreening', 'shortlisted', 'assessment', 'interview', 'offer', 'hired', 'rejected'] as $s)
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

            <div class="bg-white rounded-lg border p-5" style="border-color: var(--line);">
                <h2 class="font-medium mb-3">Tes Online</h2>

                <form method="POST" action="{{ route('assessments.assign', $application) }}" class="space-y-2">
                    @csrf
                    <select name="assessment_id" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);" required>
                        @foreach ($assessments as $a)
                            <option value="{{ $a->id }}">{{ $a->title }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="w-full px-4 py-2 rounded-md text-sm border" style="border-color: var(--line);">Kirim Tes ke Kandidat</button>
                </form>

                @foreach ($application->candidateAssessments as $ca)
                    <div class="mt-4 pt-3 border-t text-sm" style="border-color: var(--line);">
                        <p class="font-medium">{{ $ca->assessment->title }}</p>
                        <p style="color: var(--ink-soft);">
                            Status: {{ $ca->status }}
                            @if ($ca->score !== null)
                                &middot; Skor: <strong>{{ $ca->score }}</strong>
                                ({{ $ca->score >= $ca->assessment->passing_score ? 'lulus' : 'belum lulus' }})
                            @endif
                        </p>
                        <p class="text-xs mt-1 break-all" style="color: var(--ink-soft);">Link: {{ route('assessments.take', $ca) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="bg-white rounded-lg border p-5" style="border-color: var(--line);">
                <h2 class="font-medium mb-2">Kontak Kandidat</h2>
                <p class="text-sm">{{ $application->candidate->email }}</p>
                <p class="text-sm" style="color: var(--ink-soft);">{{ $application->candidate->phone ?? '-' }}</p>
                <a href="{{ Storage::url($application->candidate->cv_path) }}" target="_blank" class="text-sm inline-block mt-2" style="color: var(--accent);">Lihat CV asli ↗</a>
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
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content
                || '{{ csrf_token() }}';

            const statusUrl = @json(route('pipeline.ai-status', $application));
            const POLL_MS = 3000;
            const MAX_WAIT_MS = 4 * 60 * 1000; // berhenti menunggu setelah 4 menit

            let timer = null;
            let startedAt = 0;
            const initialStage = stageSelect ? stageSelect.value : null;

            function setRunning(running) {
                btn.disabled = running;
                icon.classList.toggle('animate-spin', running);
                label.textContent = running ? 'Memproses…' : 'Jalankan Ulang AI Screening';
                badge.classList.toggle('hidden', !running);
                badge.classList.toggle('inline-flex', running);
            }

            function showToast(message, ok) {
                toast.textContent = message;
                toast.classList.remove('hidden');
                toast.style.background = ok ? 'var(--accent-soft)' : '#fbeae5';
                toast.style.color = ok ? 'var(--accent)' : 'var(--warn)';
                setTimeout(() => toast.classList.add('hidden'), 8000);
            }

            async function poll() {
                if (Date.now() - startedAt > MAX_WAIT_MS) {
                    stopPolling();
                    setRunning(false);
                    showToast('Proses terlalu lama. Pastikan "php artisan queue:work" sedang berjalan, lalu coba lagi.', false);
                    return;
                }

                try {
                    const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();

                    if (data.status === 'running') {
                        timer = setTimeout(poll, POLL_MS);
                        return;
                    }

                    body.innerHTML = data.analysis_html;
                    history.innerHTML = data.history_html;

                    if (stageSelect && stageSelect.value === initialStage) {
                        stageSelect.value = data.stage;
                    }

                    setRunning(false);
                    showToast(
                        data.status === 'failed'
                            ? 'AI screening gagal diproses.'
                            : 'Hasil AI screening sudah diperbarui.',
                        data.status !== 'failed'
                    );
                } catch (e) {
                    timer = setTimeout(poll, POLL_MS); // jaringan putus sebentar, coba lagi
                }
            }

            function startPolling() {
                stopPolling();
                startedAt = Date.now();
                setRunning(true);
                timer = setTimeout(poll, POLL_MS);
            }

            function stopPolling() {
                if (timer) clearTimeout(timer);
                timer = null;
            }

            btn.addEventListener('click', async function () {
                setRunning(true);

                try {
                    const res = await fetch(btn.dataset.url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                    });

                    if (!res.ok) throw new Error('HTTP ' + res.status);

                    startPolling();
                } catch (e) {
                    setRunning(false);
                    showToast('Gagal memulai screening: ' + e.message, false);
                }
            });

            // Kalau halaman dibuka saat screening masih berjalan (mis. lamaran baru masuk),
            // langsung tampilkan loading dan pantau hasilnya.
            @if ($aiStatus === 'running')
                startPolling();
            @endif
        })();
    </script>
@endsection