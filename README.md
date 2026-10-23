# Smart School - Backend (Laravel)

REST API dan dashboard admin untuk aplikasi mobile [Smart School](https://github.com/mgeraldiarr/smart-school-mobile): login guru (NUPTK) dan wali murid (NISM), presensi wajah, nilai, serta reset kata sandi lewat OTP Gmail.

## Prasyarat

| Kebutuhan | Keterangan |
|---|---|
| **PHP 8.3+ & Composer** | Pasang lewat [php.new](https://php.new) (Laravel Herd). PHP bawaan XAMPP masih 8.2 dan **tidak bisa** dipakai. |
| **XAMPP** | Hanya untuk MySQL (port 3306). |
| **ngrok** | Daftar gratis di [ngrok.com](https://ngrok.com), lalu sekali jalankan `ngrok config add-authtoken <token-kamu>`. Hanya perlu jika teman mengakses dari jaringan lain. |

Cek versi PHP dengan `php -v`. Jika yang muncul 8.2, berarti terminal masih memakai PHP dari XAMPP. Pasang php.new lalu buka ulang terminal.

## Setup pertama kali

1. Clone repo:
   ```bash
   git clone https://github.com/mgeraldiarr/smart-school-laravel.git
   ```
2. Klik dua kali **`setup-dev.bat`**. Skrip ini akan:
   - membuat `.env` dari `.env.example`
   - menjalankan `composer install` dan `php artisan key:generate`
   - menyalakan MySQL XAMPP dan membuat tabel (jika database `db_smart_laravel` belum ada, jawab **yes** saat ditanya)
   - menawarkan pengisian data contoh (akun guru, wali murid, jadwal, nilai)
3. Buka `.env`, lalu isi `NGROK_URL` dengan alamat ngrok milikmu (lihat [dashboard.ngrok.com](https://dashboard.ngrok.com) > Domains). Boleh dikosongkan; alamatnya tetap terlihat di jendela ngrok.

## Menjalankan setiap hari

Klik dua kali **`start-dev.bat`**. Skrip ini menyalakan empat layanan, masing-masing di jendela sendiri:

| Jendela | Fungsi |
|---|---|
| MySQL (XAMPP) | Database |
| Laravel Server | API di `http://127.0.0.1:8000` (juga bisa diakses HP di Wi-Fi yang sama) |
| Queue Worker (Email OTP) | Mengirim email OTP di belakang layar. Jika jendela ini mati, OTP tidak terkirim. |
| ngrok | Alamat publik agar teman di luar rumah bisa mengakses |

Layanan yang sudah menyala dilewati, jadi aman diklik ulang. Tutup jendelanya untuk mematikan.

Setelah mengubah kode email atau `.env`, tutup jendela **Queue Worker** lalu klik `start-dev.bat` lagi agar worker memuat perubahan.

Server backend aktif di `http://127.0.0.1:8000`, dashboard admin di `http://127.0.0.1:8000/admin`.

### Cara manual (tanpa file .bat)

Jika tidak ingin memakai `setup-dev.bat` / `start-dev.bat`, jalankan perintahnya sendiri.

1. Buka **XAMPP Control Panel**, lalu klik **Start** pada modul **MySQL**.
2. Setup pertama kali (cukup sekali):
   ```bash
   copy .env.example .env
   composer install
   php artisan key:generate
   php artisan migrate:fresh --seed
   ```
3. Setiap mulai kerja, buka dua terminal:
   ```bash
   # Terminal 1: server API
   php artisan serve --host=0.0.0.0 --port=8000

   # Terminal 2: pengirim email OTP
   php artisan queue:work
   ```

## Bekerja beda rumah / beda internet (ngrok, gratis)

Jika anggota tim mengerjakan dari tempat berbeda, anggota yang menjalankan backend membuka akses publik lewat ngrok:

1. Jalankan ngrok (otomatis jika memakai `start-dev.bat`):
   ```bash
   ngrok http 8000
   ```
2. Salin alamat **Forwarding** yang muncul (misal `https://nama-kamu.ngrok-free.dev`), lalu bagikan ke tim mobile.
3. Tim mobile menjalankan aplikasi dengan alamat tersebut, **tanpa mengubah kode**:
   ```bash
   flutter run --dart-define=API_URL=https://nama-kamu.ngrok-free.dev/api
   ```

Pilihan `API_URL` lain untuk pengujian tanpa ngrok:

| Perangkat | `API_URL` |
|---|---|
| Android Emulator di laptop yang sama | `http://10.0.2.2:8000/api` |
| HP fisik di Wi-Fi yang sama dengan laptop | `http://192.168.1.XX:8000/api` (IP laptop, cek dengan `ipconfig`) |
| Chrome di laptop yang sama | `http://127.0.0.1:8000/api` |

Laptop yang menjalankan backend harus tetap menyala selama tim bekerja.

## Email OTP

Bawaan `.env.example` memakai `MAIL_MAILER=log`, sehingga **tidak ada email sungguhan yang terkirim**. Kode OTP bisa dilihat di:
- respons API `forgot-password` (field `debug_otp`, hanya saat `APP_ENV=local`)
- `storage/logs/laravel.log`

Untuk mengirim lewat Gmail sungguhan, ubah bagian `MAIL_` di `.env` sesuai komentar di `.env.example`. `MAIL_PASSWORD` harus berisi [App Password Gmail](https://myaccount.google.com/apppasswords), bukan kata sandi Gmail biasa. Jangan commit `.env` dan jangan bagikan App Password di grup.

## Akun contoh (dari seeder)

| Peran | Login | Kata sandi |
|---|---|---|
| Guru | NUPTK `198501152010011002` | `password123` |
| Wali Murid | NISM `20241001` | `password123` |
| Admin (web) | `admin@smartschool.sch.id` | `admin123` |

## Masalah umum

| Gejala | Penyebab & solusi |
|---|---|
| `composer install` gagal: *requires php ^8.3* | Masih memakai PHP XAMPP 8.2. Pasang [php.new](https://php.new). |
| Login dari aplikasi gagal / timeout | Pastikan jendela Laravel Server dan ngrok menyala, dan `API_URL` di aplikasi sesuai alamat ngrok. |
| Kode OTP tidak pernah masuk | Jendela Queue Worker tidak menyala, atau `MAIL_MAILER` masih `log` (cek `debug_otp`). |
| `SQLSTATE[HY000] [2002]` | MySQL XAMPP belum menyala. |
