# Persyaratan dan Batasan Hosting (HOSTING_REQUIREMENTS.md)
**MADRASAH ONE -- Integrated Student Monitoring System**  
**Versi:** 1.0  
**Tanggal:** 16 November 2026  

---

## 1. Spesifikasi Minimum Shared Hosting cPanel

Untuk mendukung 300--1.000 siswa dan staf madrasah dengan lancar pada shared hosting berbasis cPanel, penyedia hosting harus memenuhi spesifikasi minimum berikut:

| Parameter | Spesifikasi Minimum | Rekomendasi Ideal | Keterangan / Batasan |
| :--- | :--- | :--- | :--- |
| **Panel Kontrol** | cPanel x3 / Jupiter | cPanel Jupiter | Mendukung File Manager, phpMyAdmin, MultiPHP, dan Cron Jobs. |
| **Versi PHP** | PHP 8.4 | PHP 8.4.x | Wajib dipilih melalui MultiPHP Manager di cPanel. |
| **PHP Memory Limit** | 256 MB | 512 MB | Menangani rekap ekspor laporan dan kompilasi Blade. |
| **Max Execution Time**| 60 detik | 120 detik | Cukup untuk batch processing sesi harian dan migrasi. |
| **Upload Max Filesize**| 10 MB | 20 MB | Unggahan bukti izin sakit dan foto profil siswa. |
| **Database Server** | MySQL 8.0+ / MariaDB 10.5+| MySQL 8.4+ | InnoDB engine aktif secara default dengan foreign keys. |
| **Koneksi Simultan DB**| 30 koneksi | 50 koneksi | Optimasi koneksi cepat melalui query efisien dan caching. |
| **Web Server** | Apache 2.4+ / LiteSpeed | LiteSpeed / Apache | Modul `mod_rewrite` wajib aktif untuk routing Laravel. |
| **Fasilitas Cron Job** | 1 tugas per menit (`* * * * *`) | 1 tugas per menit | Untuk mengeksekusi `php artisan schedule:run`. |
| **Ruang Penyimpanan** | 2 GB SSD | 5 GB+ NVMe SSD | Mencakup source code, dependensi vendor, dan lampiran. |
| **Batas Inode** | 100.000 inode | 250.000 inode | Direktori `vendor` dan `node_modules` (jika ada) membutuhkan inode cukup. |

---

## 2. Ekstensi PHP yang Wajib Aktif di cPanel

Pastikan ekstensi berikut dicentang pada menu **MultiPHP INI Editor** atau **Select PHP Version (cPanel CloudLinux)**:
- `bcmath`
- `ctype`
- `curl`
- `dom`
- `fileinfo`
- `gd`
- `hash`
- `intl`
- `json`
- `mbstring`
- `openssl`
- `pcre`
- `pdo`
- `pdo_mysql`
- `session`
- `tokenizer`
- `xml`
- `zip`

---

## 3. Kompatibilitas Khusus Shared Hosting

1. **Konfigurasi Document Root Subdomain:**
   Disarankan menggunakan subdomain seperti `app.madrasah.sch.id` atau `monitoring.madrasah.sch.id`. Pada cPanel, buat subdomain dan arahkan path Document Root ke:
   `/home/USERNAME/madrasah-one/public`
   Dengan cara ini, file inti sistem (`.env`, `app`, `storage`, `vendor`) berada satu tingkat di atas web root dan aman dari akses publik browser.
2. **Ketiadaan Akses SSH / Terminal:**
   Jika paket hosting murah tidak menyediakan terminal SSH, MADRASAH ONE menyediakan:
   - Skrip migrasi terproteksi via web route / controller khusus administrator.
   - Kompilasi frontend dilakukan di komputer lokal pengembang (`npm run build`), lalu seluruh isi folder `public/build` diunggah via cPanel File Manager atau FTP.
3. **Keterbatasan Layanan Background Daemon:**
   Tidak boleh ada instruksi yang mengharuskan eksekusi perintah latar belakang tak terbatas seperti `php artisan queue:listen` atau `npm run dev`. Seluruh tugas latar belakang diproses melalui **Laravel Scheduler** yang dipicu satu kali per menit oleh cPanel Cron Job.
