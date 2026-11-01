<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"></head>
<body style="margin:0; padding:0; background:#f7f8fb; font-family: system-ui, sans-serif;">
    <div style="max-width:520px; margin:0 auto; padding:32px 24px;">
        <p style="font-size:13px; letter-spacing:0.05em; text-transform:uppercase; color:#465073; margin:0 0 16px;">
            {{ config('app.name') }}
        </p>
        <div style="background:#ffffff; border:1px solid #e2e5ee; border-radius:10px; padding:24px;">
            <h2 style="margin:0 0 12px; color:#14213d;">Jadwal Interview Kamu</h2>
            <p style="color:#465073; font-size:14px;">Halo {{ $candidateName }},</p>
            <p style="color:#465073; font-size:14px;">
                Selamat! Kamu dijadwalkan untuk interview posisi <strong>{{ $jobTitle }}</strong>. Detailnya:
            </p>
            <table style="width:100%; font-size:14px; color:#14213d; margin:16px 0;">
                <tr><td style="padding:4px 0; color:#465073;">Tanggal &amp; Waktu</td><td>{{ $interview->scheduled_at->translatedFormat('l, d F Y H:i') }} WIB</td></tr>
                <tr><td style="padding:4px 0; color:#465073;">Durasi</td><td>{{ $interview->duration_minutes }} menit</td></tr>
                <tr><td style="padding:4px 0; color:#465073;">Tipe</td><td>{{ ucfirst($interview->type) }}</td></tr>
                @if ($interview->location_or_link)
                    <tr><td style="padding:4px 0; color:#465073;">Link/Lokasi</td><td>{{ $interview->location_or_link }}</td></tr>
                @endif
            </table>
            <p style="color:#465073; font-size:14px;">Mohon konfirmasi kehadiranmu dengan membalas email ini. Sampai jumpa!</p>
        </div>
    </div>
</body>
</html>
