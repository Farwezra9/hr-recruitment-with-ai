<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lamaran Terkirim</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body style="background:#f7f8fb;">
    <div class="max-w-xl mx-auto px-6 py-24 text-center">
        <h1 class="text-2xl font-semibold mb-2" style="color:#14213d;">Terima kasih, {{ $candidate->name }}!</h1>
        <p class="text-sm" style="color:#465073;">
            Lamaranmu untuk posisi <strong>{{ $job->title }}</strong> sudah kami terima dan sedang diproses.
            Tim HR akan menghubungimu melalui {{ $candidate->email }} jika ada perkembangan.
        </p>
    </div>
</body>
</html>
