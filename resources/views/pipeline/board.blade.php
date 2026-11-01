@extends('layouts.app')

@section('title', 'Pipeline - ' . $job->title)

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold" style="color: var(--ink);">Pipeline: {{ $job->title }}</h1>
        <a href="{{ route('jobs.show', $job) }}" class="text-sm" style="color: var(--ink-soft);">&larr; Detail lowongan</a>
    </div>

    <div class="flex gap-4 overflow-x-auto pb-4">
        @foreach ($stages as $stage)
            @php $items = $applications->get($stage, collect()); @endphp
            <div class="w-64 shrink-0">
                <div class="flex items-center justify-between mb-2 px-1">
                    <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--ink-soft);">{{ str_replace('_', ' ', $stage) }}</p>
                    <span class="text-xs" style="color: var(--ink-soft);">{{ $items->count() }}</span>
                </div>
                <div class="space-y-2">
                    @foreach ($items as $app)
                        <a href="{{ route('pipeline.show', $app) }}" class="block bg-white border rounded-md p-3 hover:shadow-sm transition" style="border-color: var(--line);">
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
@endsection
