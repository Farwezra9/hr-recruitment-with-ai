@extends('layouts.app')

@section('title', 'Interview Saya')

@section('content')
    <h1 class="text-2xl font-semibold mb-1" style="color: var(--ink);">Interview Saya</h1>
    <p class="text-sm mb-6" style="color: var(--ink-soft);">Daftar interview yang ditugaskan kepadamu.</p>

    <div class="space-y-4">
        @forelse ($interviews as $i)
            @php $app = $i->application; @endphp
            <div class="bg-white border rounded-lg p-5" style="border-color: var(--line);">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-medium">{{ $app->candidate->name }}
                            <span class="font-normal" style="color: var(--ink-soft);">&middot; {{ $app->jobPosting->title }}</span>
                        </p>
                        <p class="text-sm mt-1" style="color: var(--ink-soft);">
                            {{ $i->scheduled_at->translatedFormat('l, d F Y H:i') }} &middot; {{ $i->duration_minutes }} menit &middot; {{ ucfirst($i->type) }}
                            @if ($i->location_or_link) &middot; {{ $i->location_or_link }} @endif
                        </p>
                    </div>
                    <span class="stage-pill px-2 py-1 rounded text-xs">{{ $i->status }}</span>
                </div>

                @if ($app->ai_summary)
                    <p class="text-sm mt-3" style="color: var(--ink-soft);">
                        <strong>Ringkasan AI</strong> (alat bantu, bukan penentu):
                        {{ $app->ai_score !== null ? 'skor ' . $app->ai_score . '. ' : '' }}{{ $app->ai_summary }}
                    </p>
                @endif

                <p class="mt-2 text-sm">
                    <a href="{{ route('candidates.cv', $app->candidate) }}" target="_blank" style="color: var(--accent);">Lihat CV ↗</a>
                </p>

                <details class="mt-3">
                    <summary class="text-sm cursor-pointer" style="color: var(--accent);">Isi feedback interview</summary>
                    <form method="POST" action="{{ route('interviews.feedback', $i) }}" class="mt-3 space-y-2">
                        @csrf @method('PATCH')
                        <div class="grid grid-cols-2 gap-2 max-w-sm">
                            <select name="status" class="border rounded-md px-2 py-1.5 text-sm" style="border-color: var(--line);">
                                @foreach (['completed' => 'Selesai', 'no_show' => 'Tidak hadir', 'cancelled' => 'Dibatalkan'] as $val => $label)
                                    <option value="{{ $val }}" @selected($i->status === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <select name="rating" class="border rounded-md px-2 py-1.5 text-sm" style="border-color: var(--line);">
                                <option value="">Rating</option>
                                @for ($r = 1; $r <= 5; $r++)
                                    <option value="{{ $r }}" @selected($i->rating === $r)>{{ $r }}</option>
                                @endfor
                            </select>
                        </div>
                        <textarea name="feedback" rows="3" placeholder="Catatan interview" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);">{{ $i->feedback }}</textarea>
                        <button type="submit" class="px-4 py-2 rounded-md text-white text-sm" style="background: var(--accent);">Simpan Feedback</button>
                    </form>
                </details>
            </div>
        @empty
            <div class="bg-white border rounded-lg p-6 text-sm" style="border-color: var(--line); color: var(--ink-soft);">
                Belum ada interview yang ditugaskan kepadamu.
            </div>
        @endforelse
    </div>
@endsection
