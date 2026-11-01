<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $candidateAssessment->assessment->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body style="background:#f7f8fb;">
    <div class="max-w-2xl mx-auto px-6 py-14">
        <h1 class="text-2xl font-semibold mb-1" style="color:#14213d;">{{ $candidateAssessment->assessment->title }}</h1>
        <p class="text-sm mb-8" style="color:#465073;">Durasi: {{ $candidateAssessment->assessment->duration_minutes }} menit &middot; Kerjakan dengan teliti, jawaban tersimpan saat kamu klik "Kirim Jawaban".</p>

        <form method="POST" action="{{ route('assessments.submit', $candidateAssessment) }}" class="space-y-5">
            @csrf
            @foreach ($candidateAssessment->assessment->questions as $i => $q)
                <div class="bg-white border rounded-lg p-5" style="border-color:#e2e5ee;">
                    <p class="font-medium mb-3">{{ $i + 1 }}. {{ $q->question }}</p>

                    @if ($q->question_type === 'multiple_choice')
                        <div class="space-y-2">
                            @foreach ($q->options as $key => $option)
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="radio" name="answers[{{ $q->id }}]" value="{{ $key }}" required>
                                    {{ $option }}
                                </label>
                            @endforeach
                        </div>
                    @else
                        <textarea name="answers[{{ $q->id }}]" rows="4" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color:#e2e5ee;" required></textarea>
                    @endif
                </div>
            @endforeach

            <button type="submit" class="w-full px-4 py-2 rounded-md text-white text-sm" style="background:#1f7a5c;">Kirim Jawaban</button>
        </form>
    </div>
</body>
</html>
