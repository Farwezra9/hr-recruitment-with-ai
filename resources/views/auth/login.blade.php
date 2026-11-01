<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - AI HR Recruitment</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body style="background:#f7f8fb;">
    <div class="max-w-sm mx-auto px-6 py-24">
        <h1 class="text-xl font-semibold mb-6 text-center" style="color:#14213d;">Masuk ke Panel HR</h1>

        @if ($errors->any())
            <div class="mb-4 rounded-md px-4 py-3 text-sm bg-red-50 text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="bg-white border rounded-lg p-6 space-y-4" style="border-color:#e2e5ee;">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" class="w-full border rounded-md px-3 py-2" style="border-color:#e2e5ee;" required autofocus>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" class="w-full border rounded-md px-3 py-2" style="border-color:#e2e5ee;" required>
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember"> Ingat saya
            </label>
            <button type="submit" class="w-full px-4 py-2 rounded-md text-white text-sm" style="background:#1f7a5c;">Masuk</button>
        </form>
    </div>
</body>
</html>
