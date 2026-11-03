# Update v3: REST API, tema GitHub, sidebar, keamanan

Paket ini menimpa file di proyekmu. **Backup folder dan database dulu.**

## 1. Salin file

```powershell
robocopy "C:\path\ke\hr-recruitment-app-v3" "C:\xampp\htdocs\hr-app" /E
```
Jangan menempel manual satu per satu (itu yang dulu membuat `Controller.php` tertukar). Paket ini tidak berisi `Controller.php`, jadi file itu aman.

## 2. Jalankan

```powershell
cd C:\xampp\htdocs\hr-app
composer dump-autoload
php artisan migrate
php artisan optimize:clear
```
`migrate` hanya menambah tabel `audit_logs`. Data lama aman. Seeder tidak perlu dijalankan.

Restart worker (`Ctrl+C`, lalu `php artisan queue:work`).

## 3. Atur zona waktu (supaya jam benar)

Di `.env`:
```env
APP_TIMEZONE=Asia/Jakarta
```
Lalu `php artisan config:clear`. Jam di header memakai zona ini. Data lama yang dibuat sebelum pengaturan ini akan tampil bergeser beberapa jam, itu wajar untuk data uji.

## 4. Yang berubah

- **REST API v1** di `/api/v1/...`. Endpoint lama (`/api/jobs`, dll.) sudah diganti. Lihat `docs/API.md`. File lama `app/Http/Controllers/Api/JobPostingApiController.php` dan `ApplicationApiController.php` sudah tidak dipakai dan boleh dihapus.
- **Tampilan**: tema GitHub (terang, gelap, otomatis), sidebar yang bisa diciutkan (tombol di sidebar atau ikon menu, tersimpan), responsive sampai layar HP, jam dengan nama hari di header.
- **Keamanan**: lihat `docs/SECURITY.md`. Perubahan yang langsung terasa:
  - Password baru minimal 10 karakter dengan huruf besar, kecil, dan angka. Password lama tetap bisa dipakai login.
  - Login terkunci 5 menit setelah 5 kali gagal.
  - Mengganti password mencabut token API.
  - Menu admin baru: **Log Aktivitas**.

## 5. Uji cepat

1. Buka `/login`, masuk, lalu ciutkan sidebar. Muat ulang halaman: keadaannya tetap.
2. Kecilkan jendela browser ke lebar HP: sidebar jadi laci dengan ikon menu.
3. Ganti tema lewat menu avatar (Terang, Gelap, Otomatis).
4. Cek jam di header (hari, tanggal, jam, WIB).
5. API:
   ```powershell
   curl.exe -X POST http://localhost:8000/api/v1/auth/login -H "Accept: application/json" -H "Content-Type: application/json" -d "{\"email\":\"admin@example.com\",\"password\":\"PASSWORDMU\"}"
   ```
6. Login dengan password salah 5 kali: harus terkunci. Lihat jejaknya di **Log Aktivitas**.

## Tes otomatis (opsional)

`tests/Feature/ApiV1Test.php` dan `SecurityTest.php` hanya jalan di SQLite (pengaman agar MySQL-mu tidak terhapus). Atur `DB_CONNECTION=sqlite` dan `DB_DATABASE=:memory:` di `phpunit.xml`, lalu `php artisan test`.
