@extends('layouts.public')

@section('title', 'Masuk')
@section('width', 'max-w-sm')

@section('content')
    <h1 class="text-2xl font-light text-center mb-6">Masuk ke Panel HR</h1>

    @if (session('status'))
        <div class="flash flash-success mb-4">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="flash flash-error mb-4">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="gh-box gh-box-body space-y-4" style="background: var(--subtle);">
        @csrf
        <div>
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full px-3 py-2" required autofocus>
        </div>
        <div>
            <div class="flex items-center justify-between">
                <label class="form-label">Password</label>
                <a href="{{ route('password.request') }}" class="text-xs" style="color: var(--accent);">Lupa password?</a>
            </div>
            <input type="password" name="password" class="w-full px-3 py-2" required>
        </div>
        <label class="flex items-center gap-2"><input type="checkbox" name="remember"> Ingat saya</label>
        <button type="submit" class="btn btn-primary btn-block" style="justify-content:center;">Masuk</button>
    </form>
@endsection
