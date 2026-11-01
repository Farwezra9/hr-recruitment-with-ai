@extends('layouts.app')

@section('title', 'Undang User')

@section('content')
    <h1 class="text-2xl font-semibold mb-6" style="color: var(--ink);">Undang User Baru</h1>

    <form method="POST" action="{{ route('users.store') }}" class="bg-white p-6 rounded-lg border space-y-4 max-w-lg" style="border-color: var(--line);">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);" required>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">No. HP</label>
            <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Role</label>
            <select name="role" class="w-full border rounded-md px-3 py-2" style="border-color: var(--line);">
                <option value="recruiter">Recruiter</option>
                <option value="hiring_manager">Hiring Manager</option>
                <option value="interviewer">Interviewer</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <p class="text-xs" style="color: var(--ink-soft);">User akan menerima email berisi link untuk mengatur passwordnya sendiri.</p>

        <button type="submit" class="px-5 py-2 rounded-md text-white text-sm" style="background: var(--accent);">Kirim Undangan</button>
    </form>
@endsection
