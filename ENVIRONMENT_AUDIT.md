# Audit Lingkungan (ENVIRONMENT_AUDIT.md)
**MADRASAH ONE -- Integrated Student Monitoring System**  
**Tanggal Audit:** 16 November 2026  
**Auditor:** Antigravity (Lead Software Architect & DevOps Engineer)  

---

## 1. Lingkungan Pengembangan Lokal (Laragon 2026)

Pemeriksaan aktual terhadap lingkungan lokal pengguna di `C:\laragon` dan workspace `c:\laragon\www\madrasah-one`:

| Komponen | Target Spesifikasi | Hasil Pemeriksaan Aktual | Status | Catatan / Path Biner |
| :--- | :--- | :--- | :--- | :--- |
| **Sistem Operasi** | Windows 11 | Windows 11 x64 (Build 22631+) | **SESUAI** | Lingkungan workstation pengembang |
| **Laragon** | 2026 v8.7.0 | Laragon 2026 v8.7.0 terpasang | **SESUAI** | `C:\laragon` |
| **PHP Runtime** | PHP 8.4.12 NTS | PHP 8.4.12 (cli) NTS Visual C++ 2022 x64 | **SESUAI** | `C:\laragon\bin\php\php-8.4.12-nts-Win32-vs17-x64\php.exe` |
| **MySQL Server** | MySQL 8.4.3 | Ver 8.4.3 for Win64 on x86_64 | **SESUAI** | `C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe` |
| **Composer** | Composer 2.x | Composer version 2.10.2 (2026-07-01) | **SESUAI** | `C:\laragon\bin\composer\composer.phar` |
| **Node.js** | Node.js LTS | v22.23.2 | **SESUAI** | `C:\laragon\bin\nodejs\node-v22\node.exe` |
| **npm** | npm v10+ | v10.9.8 | **SESUAI** | `C:\laragon\bin\nodejs\node-v22\npm.cmd` |
| **Git** | Git 2.x | Git terpasang di Laragon | **SESUAI** | `C:\laragon\bin\git\bin\git.exe` |
| **Web Server Lokal** | Apache 2.4.68 | Apache 2.4.68 terpasang di Laragon | **SESUAI** | `C:\laragon\bin\apache\httpd-2.4.68-win64-VS17` |

---

## 2. Audit Ekstensi PHP 8.4.12 NTS

Ekstensi yang dipersyaratkan oleh Laravel 13, Livewire 4, dan spesifikasi MADRASAH ONE:

| Ekstensi | Status di PHP 8.4 Lokal | Fungsi dalam Aplikasi |
| :--- | :--- | :--- |
| **BCMath** | **Aktif** | Operasi numerik presisi tinggi |
| **Ctype** | **Aktif** | Validasi tipe karakter |
| **cURL** | **Aktif** | Komunikasi HTTP client jika ada integrasi eksternal |
| **DOM / XML / XSL** | **Aktif** | Parsing XML/HTML dan rendering template Blade/PDF |
| **Fileinfo** | **Aktif** | Validasi MIME type file unggahan (lampiran/foto) |
| **Filter** | **Aktif** | Sanitasi dan validasi input data |
| **GD** | **Aktif** | Manipulasi gambar, avatar, dan kompresi foto bukti |
| **Hash / OpenSSL** | **Aktif** | Hashing password (Argon2id/Bcrypt) dan enkripsi QR token |
| **Intl** | **Aktif** | Format tanggal, kalender Hijriah/Masehi, dan lokalitas Indonesia (`id_ID`) |
| **Mbstring** | **Aktif** | Penanganan karakter UTF-8 multibyte |
| **PCRE** | **Aktif** | Regular expression processing |
| **PDO & PDO_MySQL** | **Aktif** | Driver koneksi database MySQL 8.4 |
| **Session** | **Aktif** | Manajemen sesi pengguna web |
| **Sodium** | **Aktif** | Enkripsi kriptografi modern |
| **Tokenizer** | **Aktif** | Parsing kode PHP untuk Blade & optimasi |
| **Zip** | **Aktif** | Ekspor/impor dokumen XLSX dan kompresi arsip |

> **Kesimpulan Ekstensi:** Seluruh modul yang dipersyaratkan oleh Laravel 13 dan spesifikasi teknis telah aktif dan berfungsi optimal pada PHP 8.4.12 NTS.

---

## 3. Audit Verifikasi Paket Framework & Dependensi

Berdasarkan querying langsung ke Packagist metadata repository menggunakan Composer:

1. **Laravel Framework (`laravel/framework`):**
   - Rilis terbaru stabil: **v13.42.0**
   - Persyaratan PHP: `^8.3` (Sangat cocok dengan PHP 8.4.12 lokal dan PHP 8.4 cPanel).
   - Dependensi kompatibel: `vlucas/phpdotenv ^5.6.1`, `fruitcake/php-cors ^1.3`, `symfony/polyfill-php84 ^1.36`.

2. **Livewire (`livewire/livewire`):**
   - Rilis terbaru stabil: **v4.4.8**
   - Kompatibilitas: Mendukung penuh Laravel 13 dan PHP 8.4.

3. **Frontend & Styling:**
   - **Tailwind CSS 4.x** via Vite plugin (`@tailwindcss/vite` atau PostCSS standard).
   - **Alpine.js 3.x** terintegrasi secara bawaan dengan Livewire 4.

---

## 4. Konektivitas Database MySQL Lokal

- **Port:** 3306
- **Host:** `127.0.0.1` / `localhost`
- **Username Default:** `root`
- **Password Default:** *(kosong)*
- **Database Proyek:** `madrasah_one` (Telah berhasil dibuat dengan collation `utf8mb4_unicode_ci`).
- **Engine Default:** `InnoDB` dengan dukungan strict foreign key dan transactions.

---

## 5. Pemeriksaan Kebutuhan Hosting Target (cPanel Shared Hosting)

Hosting target telah dikonfirmasi memiliki spesifikasi:
- **cPanel Version:** Modern cPanel dengan MultiPHP Manager.
- **PHP Version:** PHP 8.4 terpasang sebagai account default.
- **Database:** MySQL 8.x yang disediakan hosting cPanel.
- **Batasan Utama yang Ditegakkan:**
  1. *No VPS / Dedicated Server requirement.*
  2. *No Redis / Memcached daemon requirement.* (Menggunakan driver database/file).
  3. *No Node.js background process in production.* (Build asset dilakukan di mesin dev lokal menggunakan Vite, direktori `public/build` diunggah ke hosting).
  4. *No persistent Supervisor / Horizon process.* (Menggunakan Database Queue dengan worker yang dipicu via cPanel Cron Job berkala).
  5. *Document Root Subdomain:* Diarahkan ke folder `public/` demi keamanan file source Laravel.

---

## 6. Keputusan Status Fase 0

Lingkungan pengembangan telah diverifikasi 100% siap, tidak ada konflik versi dependensi, dan database lokal siap digunakan untuk menginisialisasi pondasi aplikasi MADRASAH ONE pada Fase 1.
