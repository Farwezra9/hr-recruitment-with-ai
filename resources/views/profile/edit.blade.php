@extends('layouts.app')

@section('title', 'Profil')

@section('content')
    <h1 class="text-2xl font-semibold mb-6" style="color: var(--ink);">Profil</h1>

    <div class="bg-white border rounded-lg p-5 mb-6 max-w-lg" style="border-color: var(--line);">
        <p class="font-medium">{{ $user->name }}</p>
        <p class="text-sm" style="color: var(--ink-soft);">{{ $user->email }} &middot; {{ str_replace('_', ' ', $user->role) }}</p>
    </div>

    <form method="POST" action="{{ route('profile.password') }}" class="bg-white border rounded-lg p-5 space-y-3 max-w-lg mb-8" style="border-color: var(--line);">
        @csrf @method('PUT')
        <h2 class="font-medium">Ganti Password</h2>
        <input type="password" name="current_password" placeholder="Password saat ini" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);" required>
        <input type="password" name="password" placeholder="Password baru (min. 10 karakter)" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);" required minlength="10">
        <input type="password" name="password_confirmation" placeholder="Ulangi password baru" class="w-full border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);" required minlength="10">
        <p class="text-xs" style="color: var(--ink-soft);">Minimal 10 karakter, mengandung huruf besar, huruf kecil, dan angka. Mengganti password mencabut semua token API.</p>
        <button type="submit" class="px-4 py-2 rounded-md text-white text-sm" style="background: var(--accent);">Simpan Password</button>
    </form>

    @if ($user->isAdmin())
        <div class="bg-white border rounded-lg p-5 max-w-lg" style="border-color: var(--line);">
            <h2 class="font-medium mb-1">Token API</h2>
            <p class="text-xs mb-3" style="color: var(--ink-soft);">Untuk integrasi sistem lain ke endpoint <code>/api</code>. Kirim di header <code>Authorization: Bearer &lt;token&gt;</code>.</p>

            @if (session('new_token'))
                <div class="rounded-md p-3 mb-4 text-xs break-all" style="background: var(--accent-soft); color: var(--accent);">
                    <p class="font-medium mb-1">Token baru (hanya tampil sekali):</p>
                    <code>{{ session('new_token') }}</code>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.tokens.store') }}" class="flex gap-2 mb-4">
                @csrf
                <input type="text" name="name" placeholder="Nama token, mis. integrasi-website" class="flex-1 border rounded-md px-3 py-2 text-sm" style="border-color: var(--line);" required>
                <button type="submit" class="px-4 py-2 rounded-md text-white text-sm" style="background: var(--accent);">Buat</button>
            </form>

            @foreach ($tokens as $t)
                <div class="flex items-center justify-between text-sm py-2 border-t" style="border-color: var(--line);">
                    <div>
                        <p>{{ $t->name }}</p>
                        <p class="text-xs" style="color: var(--ink-soft);">Dibuat {{ $t->created_at->diffForHumans() }} &middot; terakhir dipakai {{ $t->last_used_at ? $t->last_used_at->diffForHumans() : 'belum pernah' }} &middot; {{ $t->expires_at ? 'berlaku sampai ' . $t->expires_at->translatedFormat('d M Y') : 'tanpa batas waktu' }}</p>
                    </div>
                    <form method="POST" action="{{ route('profile.tokens.destroy', $t->id) }}" onsubmit="return confirm('Hapus token ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs" style="color: var(--warn);">Hapus</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
@endsection
