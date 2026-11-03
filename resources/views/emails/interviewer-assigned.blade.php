<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"></head>
<body style="margin:0; padding:0; background:#f7f8fb; font-family: system-ui, sans-serif;">
    <div style="max-width:520px; margin:0 auto; padding:32px 24px;">
        <p style="font-size:13px; letter-spacing:0.05em; text-transform:uppercase; color:#465073; margin:0 0 16px;">
            {{ config('app.name') }}
        </p>
        <div style="background:#ffffff; border:1px solid #e2e5ee; border-radius:10px; padding:24px;">
            <h2 style="margin:0 0 12px; color:#14213d;">Kamu Ditugaskan Sebagai Interviewer</h2>
            <p style="color:#465073; font-size:14px;">Halo {{ $interviewerName }},</p>
            <p style="color:#465073; font-size:14px;">
                Kamu dijadwalkan mewawancarai <strong>{{ $candidateName }}</strong> untuk posisi <strong>{{ $jobTitle }}</strong>.
            </p>
            <table style="width:100%; font-size:14px; color:#14213d; margin:16px 0;">
                <tr><td style="padding:4px 0; color:#465073;">Tanggal &amp; Waktu</td><td>{{ $interview->scheduled_at->translatedFormat('l, d F Y H:i') }}</td></tr>
                <tr><td style="padding:4px 0; color:#465073;">Durasi</td><td>{{ $interview->duration_minutes }} menit</td></tr>
                <tr><td style="padding:4px 0; color:#465073;">Tipe</td><td>{{ ucfirst($interview->type) }}</td></tr>
                @if ($interview->location_or_link)
                    <tr><td style="padding:4px 0; color:#465073;">Link/Lokasi</td><td>{{ $interview->location_or_link }}</td></tr>
                @endif
            </table>
            <p style="margin:20px 0;">
                <a href="{{ route('interviews.mine') }}" style="background:#1f7a5c; color:#ffffff; padding:10px 20px; border-radius:6px; text-decoration:none; font-size:14px;">
                    Buka Interview Saya
                </a>
            </p>
        </div>
    </div>
</body>
</html>
