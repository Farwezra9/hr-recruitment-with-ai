@extends('layouts.public')

@section('title', $candidateAssessment->assessment->title)

@section('topbar')
    <p class="text-sm" style="color: rgba(255,255,255,.75);">
        Sisa waktu: <strong id="timer" style="color:#fff; font-variant-numeric: tabular-nums;">--:--</strong>
    </p>
@endsection

@section('content')
    <h1 class="text-2xl font-semibold mb-1">{{ $candidateAssessment->assessment->title }}</h1>
    <p class="muted mb-6">
        Durasi {{ $candidateAssessment->assessment->duration_minutes }} menit. Jawaban dikirim otomatis saat waktu habis.
        Jangan menutup halaman ini sebelum menekan "Kirim Jawaban".
    </p>

    <form id="test-form" method="POST" action="{{ route('assessments.submit', $candidateAssessment) }}" class="space-y-4">
        @csrf
        @foreach ($candidateAssessment->assessment->questions as $i => $q)
            <div class="gh-box gh-box-body">
                <p class="font-semibold mb-3">{{ $i + 1 }}. {{ $q->question }}</p>

                @if ($q->question_type === 'multiple_choice')
                    <div class="space-y-2">
                        @foreach ($q->options as $key => $option)
                            <label class="flex items-center gap-2">
                                <input type="radio" name="answers[{{ $q->id }}]" value="{{ $key }}">
                                {{ $key }}. {{ $option }}
                            </label>
                        @endforeach
                    </div>
                @else
                    <textarea name="answers[{{ $q->id }}]" rows="4" class="w-full px-3 py-2"></textarea>
                @endif
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary btn-block" style="justify-content:center;">Kirim Jawaban</button>
    </form>
@endsection

@push('scripts')
<script>
    (function () {
        // Sisa waktu dihitung server, jadi tidak bisa diakali dengan mengubah jam perangkat.
        var endAt = Date.now() + {{ (int) $remainingSeconds }} * 1000;
        var timerEl = document.getElementById('timer');
        var form = document.getElementById('test-form');
        var submitted = false;

        form.addEventListener('submit', function () { submitted = true; });

        function tick() {
            var left = Math.max(0, Math.round((endAt - Date.now()) / 1000));
            var m = String(Math.floor(left / 60)).padStart(2, '0');
            var s = String(left % 60).padStart(2, '0');
            timerEl.textContent = m + ':' + s;
            if (left <= 60) timerEl.style.color = '#ff8182';

            if (left <= 0 && !submitted) {
                submitted = true;
                form.submit(); // form.submit() melewati validasi, jawaban parsial tetap terkirim
                return;
            }
            setTimeout(tick, 1000);
        }
        tick();

        window.addEventListener('beforeunload', function (e) {
            if (!submitted) { e.preventDefault(); e.returnValue = ''; }
        });
    })();
</script>
@endpush
