@extends('layouts.public')

@section('title', 'Atur Password')
@section('width', 'max-w-sm')

@section('content')
    <h1 class="text-2xl font-light text-center mb-2">Atur password akunmu</h1>
    <p class="muted text-center mb-6">Halo {{ $user->name }}, buat password untuk {{ $user->email }}.</p>

    @if ($errors->any())
        <div class="flash flash-error mb-4">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('invite.store-password', $token) }}" class="gh-box gh-box-body space-y-4" style="background: var(--subtle);">
        @csrf
        <div>
            <label class="form-label">Password baru</label>
            <input type="password" name="password" class="w-full px-3 py-2" required minlength="10">
        </div>
        <div>
            <label class="form-label">Konfirmasi password</label>
            <input type="password" name="password_confirmation" class="w-full px-3 py-2" required minlength="10">
        </div>
        <p class="muted text-xs">Minimal 10 karakter, mengandung huruf besar, huruf kecil, dan angka.</p>
        <button type="submit" class="btn btn-primary btn-block" style="justify-content:center;">Simpan &amp; masuk</button>
    </form>
@endsection
