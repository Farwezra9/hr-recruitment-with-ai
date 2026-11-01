<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Karir</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body style="background:#f7f8fb;">
    <div class="max-w-3xl mx-auto px-6 py-14">
        <h1 class="text-3xl font-semibold mb-2" style="color:#14213d;">Lowongan Terbuka</h1>
        <p class="text-sm mb-8" style="color:#465073;">Temukan posisi yang sesuai dan kirim lamaranmu.</p>

        <div class="space-y-3">
            @forelse ($jobs as $job)
                <a href="{{ route('career.apply', $job) }}" class="block bg-white border rounded-lg p-5 hover:shadow-sm transition" style="border-color:#e2e5ee;">
                    <p class="font-medium" style="color:#14213d;">{{ $job->title }}</p>
                    <p class="text-sm mt-1" style="color:#465073;">{{ $job->location ?? 'Lokasi fleksibel' }} &middot; {{ ucfirst(str_replace('_', ' ', $job->employment_type)) }}</p>
                </a>
            @empty
                <p style="color:#465073;">Belum ada lowongan yang dibuka saat ini.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $jobs->links() }}</div>
    </div>
</body>
</html>
