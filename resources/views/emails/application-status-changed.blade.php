<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"></head>
<body style="margin:0; padding:0; background:#f7f8fb; font-family: system-ui, sans-serif;">
    <div style="max-width:520px; margin:0 auto; padding:32px 24px;">
        <p style="font-size:13px; letter-spacing:0.05em; text-transform:uppercase; color:#465073; margin:0 0 16px;">
            {{ config('app.name') }}
        </p>
        <div style="background:#ffffff; border:1px solid #e2e5ee; border-radius:10px; padding:24px;">
            <p style="color:#465073; font-size:14px;">Halo {{ $candidateName }},</p>

            @if ($stage === 'shortlisted')
                <h2 style="margin:0 0 12px; color:#14213d;">Kamu Masuk Shortlist!</h2>
                <p style="color:#465073; font-size:14px;">Kami senang menginformasikan kamu masuk shortlist kandidat untuk posisi <strong>{{ $jobTitle }}</strong>. Tim HR akan segera menghubungimu untuk tahap selanjutnya.</p>
            @elseif ($stage === 'offer')
                <h2 style="margin:0 0 12px; color:#14213d;">Selamat, Kamu Mendapat Penawaran!</h2>
                <p style="color:#465073; font-size:14px;">Kami dengan senang hati menawarkan posisi <strong>{{ $jobTitle }}</strong> kepadamu. Tim HR akan mengirimkan detail penawaran secara terpisah.</p>
            @elseif ($stage === 'hired')
                <h2 style="margin:0 0 12px; color:#14213d;">Selamat Bergabung!</h2>
                <p style="color:#465073; font-size:14px;">Selamat datang di tim kami untuk posisi <strong>{{ $jobTitle }}</strong>! Kami akan menghubungimu terkait proses onboarding.</p>
            @elseif ($stage === 'rejected')
                <h2 style="margin:0 0 12px; color:#14213d;">Update Lamaranmu</h2>
                <p style="color:#465073; font-size:14px;">
                    Terima kasih telah melamar posisi <strong>{{ $jobTitle }}</strong>. Setelah melalui proses seleksi, saat ini kami memutuskan untuk melanjutkan dengan kandidat lain.
                    @if ($rejectionReason)
                        <br><br>Catatan dari tim HR: {{ $rejectionReason }}
                    @endif
                </p>
                <p style="color:#465073; font-size:14px;">Kami menyimpan profilmu dan akan menghubungi kembali jika ada kesempatan yang sesuai di masa depan.</p>
            @else
                <h2 style="margin:0 0 12px; color:#14213d;">Update Status Lamaran</h2>
                <p style="color:#465073; font-size:14px;">Status lamaranmu untuk posisi <strong>{{ $jobTitle }}</strong> telah diperbarui menjadi: {{ $stage }}.</p>
            @endif
        </div>
    </div>
</body>
</html>
