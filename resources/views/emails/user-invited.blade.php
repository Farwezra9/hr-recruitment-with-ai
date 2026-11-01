<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"></head>
<body style="margin:0; padding:0; background:#f7f8fb; font-family: system-ui, sans-serif;">
    <div style="max-width:520px; margin:0 auto; padding:32px 24px;">
        <p style="font-size:13px; letter-spacing:0.05em; text-transform:uppercase; color:#465073; margin:0 0 16px;">
            {{ config('app.name') }}
        </p>
        <div style="background:#ffffff; border:1px solid #e2e5ee; border-radius:10px; padding:24px;">
            <h2 style="margin:0 0 12px; color:#14213d;">Kamu Diundang Bergabung</h2>
            <p style="color:#465073; font-size:14px;">Halo {{ $user->name }},</p>
            <p style="color:#465073; font-size:14px;">
                Kamu diundang untuk bergabung ke panel HR sebagai <strong>{{ $user->role }}</strong>.
                Klik tombol di bawah untuk mengatur password akunmu.
            </p>
            <p style="margin:20px 0;">
                <a href="{{ route('invite.set-password', $user->invite_token) }}" style="background:#1f7a5c; color:#ffffff; padding:10px 20px; border-radius:6px; text-decoration:none; font-size:14px;">
                    Atur Password
                </a>
            </p>
            <p style="color:#465073; font-size:14px;">Email login kamu: {{ $user->email }}</p>
        </div>
    </div>
</body>
</html>
