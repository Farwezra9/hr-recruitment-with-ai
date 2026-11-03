@extends('layouts.public')

@section('title', 'Atur Ulang Password')
@section('width', 'max-w-sm')

@section('content')
    <h1 class="text-2xl font-light text-center mb-6">Atur ulang password</h1>

    @if ($errors->any())
        <div class="flash flash-error mb-4">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="gh-box gh-box-body space-y-4" style="background: var(--subtle);">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email', $email) }}" class="w-full px-3 py-2" required>
        </div>
        <div>
            <label class="form-label">Password baru</label>
            <input type="password" name="password" class="w-full px-3 py-2" required minlength="10">
        </div>
        <div>
            <label class="form-label">Ulangi password baru</label>
            <input type="password" name="password_confirmation" class="w-full px-3 py-2" required minlength="10">
        </div>
        <p class="muted text-xs">Minimal 10 karakter, mengandung huruf besar, huruf kecil, dan angka.</p>
        <button type="submit" class="btn btn-primary btn-block" style="justify-content:center;">Simpan password</button>
    </form>
@endsection
