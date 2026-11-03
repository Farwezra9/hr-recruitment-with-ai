@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold" style="color: var(--ink);">Manajemen User</h1>
        <a href="{{ route('users.create') }}" class="px-4 py-2 rounded-md text-white text-sm" style="background: var(--accent);">+ Undang User</a>
    </div>

    <div class="bg-white rounded-lg border overflow-hidden" style="border-color: var(--line);">
        <table class="w-full text-sm">
            <thead style="background: var(--paper);">
                <tr class="text-left" style="color: var(--ink-soft);">
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $u)
                    <tr class="border-t" style="border-color: var(--line);">
                        <td class="px-4 py-3 font-medium">{{ $u->name }}</td>
                        <td class="px-4 py-3">{{ $u->email }}</td>
                        <td class="px-4 py-3">{{ str_replace('_', ' ', $u->role) }}</td>
                        <td class="px-4 py-3">
                            @if ($u->invite_token)
                                <span class="stage-pill px-2 py-1 rounded text-xs">Menunggu aktivasi</span>
                            @elseif ($u->is_active)
                                <span class="stage-pill px-2 py-1 rounded text-xs" style="color: var(--accent);">Aktif</span>
                            @else
                                <span class="stage-pill px-2 py-1 rounded text-xs" style="color: var(--warn);">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            @if ($u->invite_token)
                                <form method="POST" action="{{ route('users.resend-invite', $u) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-sm" style="color: var(--ink-soft);">Kirim ulang undangan</button>
                                </form>
                            @endif
                            <a href="{{ route('users.edit', $u) }}" class="text-sm" style="color: var(--accent);">Edit</a>
                            @if ($u->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $u) }}" class="inline" onsubmit="return confirm('Hapus user ini? Kalau punya riwayat data, akun hanya dinonaktifkan.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-sm" style="color: var(--warn);">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
@endsection
