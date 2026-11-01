@extends('layouts.app')

@section('title', 'Kelola Tes - ' . $assessment->title)

@section('content')
    <h1 class="text-2xl font-semibold mb-6" style="color: var(--ink);">Kelola Tes: {{ $assessment->title }}</h1>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="bg-white rounded-lg border p-5" style="border-color: var(--line);">
                <h2 class="font-medium mb-3">Daftar Soal ({{ $assessment->questions->count() }})</h2>

                <div class="space-y-3">
                    @forelse ($assessment->questions as $q)
                        <details class="border rounded-md p-3" style="border-color: var(--line);">
                            <summary class="cursor-pointer text-sm font-medium">
                                {{ $loop->iteration }}. {{ Str::limit($q->question, 80) }}
                                <span class="text-xs" style="color: var(--ink-soft);">({{ $q->question_type === 'multiple_choice' ? 'Pilihan Ganda' : 'Esai' }}, {{ $q->points }} poin)</span>
                            </summary>

                            <form method="POST" action="{{ route('assessments.questions.update', $q) }}" class="mt-4 space-y-3">
                                @csrf @method('PUT')
                                <div>
                                    <label class="block text-xs font-medium mb-1">Pertanyaan</label>
                                    <textarea name="question" rows="2" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);" required>{{ $q->question }}</textarea>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium mb-1">Tipe Soal</label>
                                        <select name="question_type" class="w-full border rounded-md px-3 py-2 text-sm question-type-select" style="border-color: var(--line);">
                                            <option value="multiple_choice" @selected($q->question_type === 'multiple_choice')>Pilihan Ganda</option>
                                            <option value="essay" @selected($q->question_type === 'essay')>Esai</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium mb-1">Poin</label>
                                        <input type="number" name="points" value="{{ $q->points }}" min="1" max="20" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);" required>
                                    </div>
                                </div>

                                <div class="mc-fields {{ $q->question_type !== 'multiple_choice' ? 'hidden' : '' }} space-y-2">
                                    @php $optionLabels = ['A', 'B', 'C', 'D', 'E', 'F']; @endphp
                                    @foreach ($optionLabels as $label)
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs w-4">{{ $label }}</span>
                                            <input type="text" name="options[]" value="{{ $q->options[$label] ?? '' }}" placeholder="Opsi {{ $label }}" class="flex-1 border rounded-md px-3 py-1.5 text-sm" style="border-color: var(--line);">
                                        </div>
                                    @endforeach
                                    <div>
                                        <label class="block text-xs font-medium mb-1">Jawaban Benar</label>
                                        <select name="correct_option" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);">
                                            @foreach ($optionLabels as $label)
                                                <option value="{{ $label }}" @selected($q->correct_option === $label)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="pt-1">
                                    <button type="submit" class="px-3 py-1.5 rounded-md text-white text-xs" style="background: var(--accent);">Simpan Soal</button>
                                </div>
                            </form>
                            <form method="POST" action="{{ route('assessments.questions.destroy', $q) }}" onsubmit="return confirm('Hapus soal ini?')" class="mt-2">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs" style="color: var(--warn);">Hapus Soal</button>
                            </form>
                        </details>
                    @empty
                        <p class="text-sm" style="color: var(--ink-soft);">Belum ada soal. Tambahkan lewat form di kanan.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg border p-5" style="border-color: var(--line);">
                <h2 class="font-medium mb-3">Info Tes</h2>
                <form method="POST" action="{{ route('assessments.update', $assessment) }}" class="space-y-3">
                    @csrf @method('PUT')
                    <input type="text" name="title" value="{{ $assessment->title }}" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);" required>
                    <select name="type" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);">
                        @foreach (['technical' => 'Technical', 'psychometric' => 'Psikotes', 'language' => 'Bahasa', 'case_study' => 'Studi Kasus', 'other' => 'Lainnya'] as $val => $label)
                            <option value="{{ $val }}" @selected($assessment->type === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="duration_minutes" value="{{ $assessment->duration_minutes }}" min="5" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);" required>
                        <input type="number" name="passing_score" value="{{ $assessment->passing_score }}" min="0" max="100" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);" required>
                    </div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked($assessment->is_active)> Aktif
                    </label>
                    <button type="submit" class="w-full px-4 py-2 rounded-md text-white text-sm" style="background: var(--accent);">Simpan Info Tes</button>
                </form>
            </div>

            <div class="bg-white rounded-lg border p-5" style="border-color: var(--line);">
                <h2 class="font-medium mb-3">Tambah Soal Baru</h2>
                <form method="POST" action="{{ route('assessments.questions.store', $assessment) }}" class="space-y-3">
                    @csrf
                    <textarea name="question" rows="2" placeholder="Tulis pertanyaan..." class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);" required></textarea>

                    <select name="question_type" class="w-full border rounded-md px-3 py-2 text-sm question-type-select" style="border-color: var(--line);">
                        <option value="multiple_choice">Pilihan Ganda</option>
                        <option value="essay">Esai</option>
                    </select>

                    <div class="mc-fields space-y-2">
                        @php $optionLabels = ['A', 'B', 'C', 'D', 'E', 'F']; @endphp
                        @foreach ($optionLabels as $label)
                            <div class="flex items-center gap-2">
                                <span class="text-xs w-4">{{ $label }}</span>
                                <input type="text" name="options[]" placeholder="Opsi {{ $label }}" class="flex-1 border rounded-md px-3 py-1.5 text-sm" style="border-color: var(--line);">
                            </div>
                        @endforeach
                        <select name="correct_option" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);">
                            @foreach ($optionLabels as $label)
                                <option value="{{ $label }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <input type="number" name="points" value="1" min="1" max="20" placeholder="Poin" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);" required>

                    <button type="submit" class="w-full px-4 py-2 rounded-md text-white text-sm" style="background: var(--accent);">Tambah Soal</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Sembunyikan/tampilkan kolom opsi pilihan ganda sesuai tipe soal yang dipilih.
        document.querySelectorAll('.question-type-select').forEach(function (select) {
            function toggle() {
                const wrapper = select.closest('form').querySelector('.mc-fields');
                if (!wrapper) return;
                wrapper.classList.toggle('hidden', select.value !== 'multiple_choice');
            }
            select.addEventListener('change', toggle);
            toggle();
        });
    </script>
@endsection
