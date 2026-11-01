@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    <h1 class="text-2xl font-semibold mb-6" style="color: var(--ink);">Edit User: {{ $user->name }}</h1>

    <form method="POST" action="{{ route('users.update', $user) }}" class="bg-white p-6 rounded-lg border space-y-4 max-w-lg" style="border-color: var(--line);">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-medium mb-1">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">No. HP</label>
            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Role</label>
            <select name="role" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);">
                @foreach (['recruiter' => 'Recruiter', 'hiring_manager' => 'Hiring Manager', 'interviewer' => 'Interviewer', 'admin' => 'Admin'] as $val => $label)
                    <option value="{{ $val }}" @selected($user->role === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked($user->is_active)>
            Akun aktif
        </label>

        <button type="submit" class="px-5 py-2 rounded-md text-white text-sm" style="background: var(--accent);">Simpan Perubahan</button>
    </form>
@endsection
