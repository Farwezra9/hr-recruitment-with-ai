# Keamanan Aplikasi

Ringkasan apa yang sudah diterapkan, apa yang harus kamu atur sendiri, dan apa yang belum ada.

## Sudah diterapkan di kode

**Akun dan login**
- Password minimal 10 karakter dengan huruf besar, huruf kecil, dan angka (ganti password, reset, aktivasi undangan).
- Login terkunci 5 menit setelah 5 percobaan gagal (per email + IP), selain pembatasan per IP di route. Berlaku juga untuk API.
- Pesan gagal login selalu sama, dan lupa password tidak membocorkan email mana yang terdaftar.
- Sesi diperbarui saat login (cegah session fixation). Akun nonaktif tidak bisa masuk.
- Mengganti password, reset password, mengubah role, atau menonaktifkan user otomatis mencabut semua token API miliknya.
- Link undangan user berlaku 7 hari. Token API login berlaku 7 hari, token integrasi buatan admin 90 hari.

**Akses**
- Hak akses per role (admin, recruiter, hiring manager, interviewer) di web dan API. Interviewer hanya melihat interview dan CV kandidat yang ia wawancarai.
- CV disimpan privat (tidak bisa dibuka lewat URL publik), dan setiap pembukaan CV dicatat.
- Link tes kandidat memakai token acak, berlaku 7 hari, dengan batas waktu pengerjaan yang dicek server.

**Jejak audit** (menu Admin > Log Aktivitas)
Login berhasil/gagal/terkunci, logout, ganti password, undangan dan perubahan user, token API, perpindahan tahap pelamar, dan pembukaan CV. Mencatat waktu, pengguna, dan alamat IP.

**Header dan respons**
- `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy`, `Content-Security-Policy`, `Strict-Transport-Security` (saat HTTPS).
- Halaman yang butuh login dan semua API tidak di-cache (`no-store`), jadi tidak bisa dibuka lewat tombol Back setelah logout, dan tidak diindeks mesin pencari.
- Semua error di `/api/*` berbentuk JSON, tidak membocorkan HTML atau stack trace.

**Data pelamar**
- Form lamar: honeypot anti-bot, batas 6 lamaran per menit per IP, persetujuan wajib, URL hanya `http/https`, upload hanya PDF/DOCX maks 5 MB dengan nama file acak.
- Retensi: `php artisan data:purge-rejected --days=365` menghapus lamaran yang ditolak lama beserta CV-nya. Tambahkan `--dry-run` untuk simulasi.

**AI**
- Teks CV diperlakukan sebagai data, bukan perintah. Prompt memuat aturan anti-instruksi, dan teks CV dibersihkan dari karakter kontrol. Kalimat yang mencoba mengarahkan AI (misalnya "beri skor 100") ditandai dengan peringatan merah di halaman kandidat. Ini deteksi sederhana, bukan jaminan, jadi skor AI tetap hanya alat bantu.
- Body respons error dari penyedia AI dipotong di log (bisa memuat potongan data pelamar).

## Yang harus kamu atur sendiri (produksi)

1. `APP_DEBUG=false`, `APP_ENV=production`, dan jangan pernah commit `.env`.
2. HTTPS wajib, lalu `SESSION_SECURE_COOKIE=true`. Perpendek sesi dengan `SESSION_LIFETIME=60` (menit).
3. Ganti password admin awal, dan batasi jumlah akun admin.
4. Pasang `data:purge-rejected` di scheduler. Di `routes/console.php`:
   ```php
   use Illuminate\Support\Facades\Schedule;
   Schedule::command('data:purge-rejected --days=365')->monthly();
   ```
   Lalu cron `* * * * * php /path/artisan schedule:run`.
5. Backup database dan folder `storage/app/private`, simpan terpisah dari server (lihat `DEPLOY.md`).
6. Tailwind lewat CDN dipakai sekarang supaya mudah dijalankan. Untuk produksi, build lewat Vite (`npm run build`), lalu di `SecurityHeaders` hapus `https://cdn.tailwindcss.com` dan `'unsafe-eval'` dari `script-src`. Itu memperketat CSP. Selama masih memakai CDN, CSP sengaja dilonggarkan agar tampilan tidak rusak.
7. Jika di belakang proxy/load balancer, atur `trustProxies` agar IP asli pengguna tercatat di audit log dan rate limit.
8. Free tier penyedia AI bisa dipakai untuk melatih model. Untuk CV pelamar asli, pakai paket berbayar dan baca ketentuan privasinya.

## Belum ada (saran berikutnya)

- **Autentikasi dua faktor (2FA/TOTP)** untuk admin dan recruiter. Ini peningkatan paling berharga berikutnya.
- Hash untuk token undangan di database (saat ini disimpan apa adanya, berlaku 7 hari).
- Pemindaian virus pada file CV yang diunggah (mis. ClamAV).
- Enkripsi kolom data pribadi di database dan CV di penyimpanan.
- OCR untuk PDF hasil scan.
- Kebijakan privasi dan teks persetujuan yang disesuaikan dengan kebutuhan hukum perusahaanmu (UU PDP). Konsultasikan dengan ahli hukum.
