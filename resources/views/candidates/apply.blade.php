<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lamar - {{ $job->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body style="background:#f7f8fb;">
    <div class="max-w-xl mx-auto px-6 py-14">
        <h1 class="text-2xl font-semibold mb-1" style="color:#14213d;">Lamar: {{ $job->title }}</h1>
        <p class="text-sm mb-8" style="color:#465073;">{{ $job->location ?? '' }}</p>

        @if ($errors->any())
            <div class="mb-4 rounded-md px-4 py-3 text-sm bg-red-50 text-red-700">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('career.submit', $job) }}" enctype="multipart/form-data" class="bg-white border rounded-lg p-6 space-y-4" style="border-color:#e2e5ee;">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded-md px-3 py-2" style="border-color:#e2e5ee;" required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded-md px-3 py-2" style="border-color:#e2e5ee;" required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">No. HP</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border rounded-md px-3 py-2" style="border-color:#e2e5ee;">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">LinkedIn (opsional)</label>
                <input type="url" name="linkedin_url" value="{{ old('linkedin_url') }}" class="w-full border rounded-md px-3 py-2" style="border-color:#e2e5ee;">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Upload CV (PDF/DOCX, maks 5MB)</label>
                <input type="file" name="cv" accept=".pdf,.doc,.docx" class="w-full border rounded-md px-3 py-2" style="border-color:#e2e5ee;" required>
            </div>

            <button type="submit" class="w-full px-4 py-2 rounded-md text-white text-sm" style="background:#1f7a5c;">Kirim Lamaran</button>
        </form>
    </div>
</body>
</html>
