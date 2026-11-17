# 🚀 PANDUAN LENGKAP DEPLOYMENT & HOSTING MADRASAH ONE

Dokumen ini memandu Anda langkah-demi-langkah mengunggah dan menginstal aplikasi **MADRASAH ONE** ke server hosting produksi, baik menggunakan **cPanel / Shared Hosting** maupun **VPS Linux (Ubuntu/Debian)**.

---

## 📋 Daftar Alat & Script yang Sudah Tersedia

Kami telah menyiapkan script dan template otomatis di folder proyek:
1. **`package-hosting.ps1`** : Script PowerShell di Windows untuk membuat file ZIP bersih siap upload (otomatis compile Vite, pasang vendor production, dan buang node_modules).
2. **`deploy.sh`** : Script instalasi otomatis 1-klik untuk server **VPS Linux (Ubuntu / Debian)**.
3. **`deploy-cpanel.sh`** : Script deployment untuk cPanel yang memiliki fitur **Terminal / SSH**.
4. **`public/install-helper.php`** : Web Installer interaktif berbasis browser untuk **Shared Hosting tanpa SSH** (menjalankan migrasi, seeder, storage link, dan optimasi via klik tombol).
5. **`.env.production.example`** : Template konfigurasi `.env` siap pakai untuk produksi.
6. **`nginx.conf.example`** : Template konfigurasi VirtualHost Nginx dengan SSL dan optimasi caching.

---

## 🌐 METODE 1: Hosting di cPanel / Shared Hosting (Paling Umum)

Metode ini cocok untuk layanan seperti Niagahoster, DomaiNesia, IDCloudHost, Hostinger, Dewaweb, Rumahweb, dll.

### Langkah 1: Buat Paket ZIP Produksi di Komputer Lokal (Windows)
Buka PowerShell di folder proyek `c:\laragon\www\madrasah-one` dan jalankan:
```powershell
.\package-hosting.ps1
```
*Script ini akan mengompilasi aset Vite (`npm run build`), menyiapkan vendor PHP mode produksi, dan membuat file bernama `madrasah-one-production-YYYYMMDD-HHmm.zip` (ukuran hemat hanya ~25-35 MB).*

---

### Langkah 2: Buat Database MySQL di cPanel
1. Masuk ke **cPanel** akun hosting Anda.
2. Buka menu **MySQL Database Wizard** (atau **Basis Data MySQL**).
3. Buat database baru (contoh: `userhost_madrasah`).
4. Buat user database baru dan buat password yang kuat.
5. Centang **"ALL PRIVILEGES"** (Semua Hak Akses) lalu simpan.
6. Catat: **Nama Database**, **Username Database**, dan **Password Database**.

---

### Langkah 3: Upload & Ekstrak File di File Manager
Ada dua struktur penempatan folder di cPanel (Pilih salah satu):

#### **Rekomendasi Utama (Struktur Paling Aman):**
1. Buka **File Manager** cPanel.
2. Buat folder baru di luar `public_html`, misalnya: `/home/username/madrasah-one/`.
3. Upload file ZIP hasil Langkah 1 ke folder tersebut, lalu klik **Extract**.
4. Ubah **Document Root** domain Anda di menu **Domains** cPanel agar mengarah ke:  
   `/home/username/madrasah-one/public`
   *(Jika Document Root sudah mengarah ke subfolder `/public`, selesai!)*

*(Alternatif: Jika hosting Anda tidak mengizinkan ubah Document Root, pindahkan isi folder `public/` ke dalam `public_html`, lalu sesuaikan path di `index.php`)*.

---

### Langkah 4: Konfigurasi File `.env`
1. Di File Manager cPanel, aktifkan opsi **"Show Hidden Files (dotfiles)"** di pengaturan kanan atas.
2. Cari file `.env` (atau ubah nama `.env.production.example` menjadi `.env`).
3. Klik kanan file `.env` -> **Edit**:
   ```env
   APP_NAME="MADRASAH ONE"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://domain-madrasah-anda.sch.id

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nama_database_yang_dibuat_di_langkah_2
   DB_USERNAME=user_database_yang_dibuat_di_langkah_2
   DB_PASSWORD=password_database_anda

   SESSION_SECURE_COOKIE=true
   FILESYSTEM_DISK=public
   ```
4. Simpan file `.env`.

---

### Langkah 5: Jalankan Instalasi Database (Migrasi & Akun Awal)

#### **Pilihan A: Jika Hosting Anda Memiliki Fitur "Terminal" di cPanel**
1. Buka menu **Terminal** di cPanel.
2. Masuk ke folder aplikasi:
   ```bash
   cd ~/madrasah-one
   bash deploy-cpanel.sh
   ```
3. Ikuti petunjuk di layar. Selesai!

#### **Pilihan B: Jika Hosting Anda TIDAK Memiliki Akses Terminal (Gunakan Web Helper)**
1. Buka browser Anda dan akses URL berikut:
   ```
   https://domain-madrasah-anda.sch.id/install-helper.php?secret=MADRASAH_ONE_SETUP_2026
   ```
2. Anda akan melihat halaman antarmuka **MADRASAH ONE Web Installer**.
3. Centang opsi *"Sertakan akun default & data awal"*.
4. Klik tombol hijau besar: **🚀 Jalankan Instalasi Penuh (Migrasi + Seed + Cache + Storage)**.
5. Tunggu 5-10 detik hingga muncul tanda centang hijau **✓ SUKSES**.
6. **PENTING:** Setelah selesai, klik tombol merah **🗑️ Hapus Installer Ini** agar file helper terhapus otomatis dari server demi keamanan!

---

### Langkah 6: Aktifkan SSL / HTTPS (WAJIB!)
Agar fitur **Kamera Scanner QR** dan **Sensor GPS Geofencing** diizinkan oleh browser ponsel siswa dan guru, website **wajib menggunakan HTTPS**:
1. Di cPanel, buka menu **SSL/TLS Status** atau **Let's Encrypt SSL**.
2. Klik tombol **Run AutoSSL** untuk domain madrasah Anda.
3. Pastikan domain Anda dapat diakses dengan gembok hijau aman di `https://domain-madrasah-anda.sch.id`.

---

## 🐧 METODE 2: Hosting di VPS Linux (Ubuntu 22.04 / 24.04 LTS)

Metode ini sangat disarankan untuk madrasah dengan ratusan hingga ribuan siswa agar proses presensi gerbang berlangsung sangat cepat tanpa batasan resource hosting bersama.

### Langkah 1: Persiapan VPS Baru
Hubungkan ke VPS via SSH:
```bash
ssh root@ip-vps-anda
```
Instal PHP 8.4, Nginx, MySQL, dan ekstensi yang dibutuhkan:
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y software-properties-common curl git unzip
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Instal PHP 8.4 & Ekstensi
sudo apt install -y php8.4-fpm php8.4-cli php8.4-mysql php8.4-mbstring \
    php8.4-xml php8.4-curl php8.4-zip php8.4-bcmath php8.4-intl php8.4-gd

# Instal Nginx & MySQL
sudo apt install -y nginx mysql-server certbot python3-certbot-nginx
```

---

### Langkah 2: Buat Database MySQL di VPS
Masuk ke prompt MySQL:
```bash
sudo mysql
```
Jalankan perintah SQL berikut:
```sql
CREATE DATABASE madrasah_one CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'madrasah_user'@'localhost' IDENTIFIED BY 'PasswordSangatKuat123!';
GRANT ALL PRIVILEGES ON madrasah_one.* TO 'madrasah_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

### Langkah 3: Unduh Proyek & Jalankan Script Otomatis
Salin source code proyek ke `/var/www/madrasah-one`:
```bash
cd /var/www
git clone <URL_REPOSITORY_ANDA> madrasah-one
# atau upload ZIP hasil package-hosting.ps1 lalu ekstrak di sini
cd /var/www/madrasah-one
```

Buat script instalasi dapat dieksekusi dan jalankan:
```bash
chmod +x deploy.sh
./deploy.sh
```
*Script ini akan memandu Anda secara otomatis mengisi `.env`, menginstal dependensi composer, menjalankan migrasi database, seeding akun, membuat storage link, hingga mengatur izin folder `www-data`!*

---

### Langkah 4: Konfigurasi VirtualHost Nginx & Pasang SSL
1. Salin template Nginx:
   ```bash
   sudo cp nginx.conf.example /etc/nginx/sites-available/madrasah-one.conf
   ```
2. Edit file tersebut dan sesuaikan `server_name` dengan nama domain madrasah Anda:
   ```bash
   sudo nano /etc/nginx/sites-available/madrasah-one.conf
   ```
3. Aktifkan konfigurasi Nginx:
   ```bash
   sudo ln -s /etc/nginx/sites-available/madrasah-one.conf /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl reload nginx
   ```
4. Pasang Sertifikat SSL Gratis (Let's Encrypt):
   ```bash
   sudo certbot --nginx -d domain-madrasah-anda.sch.id
   ```

---

## 📡 Integrasi Mesin Absensi Biometrik LAN (Solution X100C / ZKTeco) di Server Hosting

Jika madrasah menggunakan mesin absensi fisik di pos gerbang/lobi sekolah (misal Solution X100C):
1. Pastikan mesin absensi terhubung ke jaringan internet sekolah (kabel LAN ke router/switch).
2. Di menu layar mesin absensi:
   - Masuk ke **Menu** -> **Komunikasi (Comm.)** -> **ADMS / Cloud Server**.
   - **Aktifkan Server Cloud:** `ON`
   - **Alamat Server (Server Address):** Masukkan nama domain atau IP server hosting Anda (misal: `domain-madrasah-anda.sch.id`).
   - **Port Server:** `80` (jika HTTP) atau `443` (jika HTTPS).
3. Mesin Solution X100C akan otomatis mengirimkan rekaman sidik jari siswa secara real-time ke endpoint:
   `https://domain-madrasah-anda.sch.id/api/machine/cdata`
4. Di aplikasi MADRASAH ONE, pastikan NIS siswa diisi sama dengan ID/PIN siswa yang didaftarkan pada mesin absensi.

---

## 🔑 Akun Default Setelah Instalasi

Semua akun default langsung aktif dan siap digunakan (tersimpan di file `akun.txt`):

| Peran Pengguna | Username | Password Default |
|---|---|---|
| **Super Admin** | `superadmin` | `password` |
| **Guru Piket** | `piket` | `password` |
| **Kepala Madrasah** | `kepsek` | `password` |
| **Wali Kelas** | `walikelas` | `password` |
| **Guru BK** | `gurubk` | `password` |
| **Siswa** | `siswa` | `password` |
| **Orang Tua / Wali** | `orangtua` | `password` |

> [!IMPORTANT]
> Begitu sistem berhasil online di hosting publik, segera masuk sebagai **Super Admin** dan ubah password akun-akun utama untuk menjaga keamanan madrasah Anda.
