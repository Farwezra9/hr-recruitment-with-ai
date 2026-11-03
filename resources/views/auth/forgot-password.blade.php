@extends('layouts.public')

@section('title', 'Lupa Password')
@section('width', 'max-w-sm')

@section('content')
    <h1 class="text-2xl font-light text-center mb-2">Lupa password</h1>
    <p class="muted text-center mb-6">Masukkan email akunmu, kami kirim link untuk mengatur ulang password.</p>

    @if (session('status'))
        <div class="flash flash-success mb-4">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="flash flash-error mb-4">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="gh-box gh-box-body space-y-4" style="background: var(--subtle);">
        @csrf
        <div>
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full px-3 py-2" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary btn-block" style="justify-content:center;">Kirim link reset</button>
    </form>
    <p class="text-center mt-4"><a href="{{ route('login') }}" class="muted">&larr; Kembali ke login</a></p>
@endsection
