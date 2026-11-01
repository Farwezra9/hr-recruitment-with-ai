<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Atur Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body style="background:#f7f8fb;">
    <div class="max-w-sm mx-auto px-6 py-24">
        <h1 class="text-xl font-semibold mb-6 text-center" style="color:#14213d;">Atur Password Akunmu</h1>

        @if ($errors->any())
            <div class="mb-4 rounded-md px-4 py-3 text-sm bg-red-50 text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('invite.store-password', $token) }}" class="bg-white border rounded-lg p-6 space-y-4" style="border-color:#e2e5ee;">
            @csrf
            <p class="text-sm" style="color:#465073;">Halo {{ $user->name }}, buat password baru untuk email {{ $user->email }}.</p>
            <div>
                <label class="block text-sm font-medium mb-1">Password Baru</label>
                <input type="password" name="password" class="w-full border rounded-md px-3 py-2" style="border-color:#e2e5ee;" required minlength="8">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="w-full border rounded-md px-3 py-2" style="border-color:#e2e5ee;" required minlength="8">
            </div>
            <button type="submit" class="w-full px-4 py-2 rounded-md text-white text-sm" style="background:#1f7a5c;">Simpan & Masuk</button>
        </form>
    </div>
</body>
</html>
