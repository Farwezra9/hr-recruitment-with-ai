@extends('layouts.public')

@section('title', 'Tes Terkirim')

@section('content')
    <div class="gh-box gh-box-body text-center" style="padding: 48px 24px;">
        <svg width="40" height="40" viewBox="0 0 16 16" fill="currentColor" style="color: var(--success); margin: 0 auto 12px;" aria-hidden="true"><path d="M8 16A8 8 0 1 1 8 0a8 8 0 0 1 0 16Zm3.78-9.72a.751.751 0 0 0-.018-1.042.751.751 0 0 0-1.042-.018L6.75 9.19 5.28 7.72a.751.751 0 0 0-1.042.018.751.751 0 0 0-.018 1.042l2 2a.75.75 0 0 0 1.06 0Z"/></svg>
        <h1 class="text-xl font-semibold mb-2">Jawaban terkirim</h1>
        <p class="muted">Terima kasih sudah mengerjakan tes ini. Tim HR akan meninjau hasilnya dan menghubungimu untuk tahap selanjutnya.</p>
        @if (! empty($late))
            <p class="text-xs mt-3" style="color: var(--danger);">Catatan: jawaban diterima setelah batas waktu pengerjaan.</p>
        @endif
    </div>
@endsection
