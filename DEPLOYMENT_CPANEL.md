# Panduan Lengkap Deployment Shared Hosting cPanel (DEPLOYMENT_CPANEL.md)
**MADRASAH ONE -- Integrated Student Monitoring System**  
**Versi:** 1.0  
**Tanggal:** 16 November 2026  

---

## 1. Arsitektur Penempatan Direktori di cPanel

Pilihan tata letak direktori terbaik dan paling aman adalah menempatkan kode sumber Laravel **di luar `public_html`**, sehingga file sensitif seperti `.env`, `storage`, dan direktori `vendor` tidak dapat diakses langsung oleh browser publik.

### Skenario A (Rekomendasi Utama: Menggunakan Subdomain)
Gunakan subdomain madrasah, misalnya `monitoring.madrasah.sch.id`.

```text
/home/USER/
├── madrasah-one/            <-- Seluruh source code aplikasi Laravel
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env                 <-- Aman, berada di luar jangkauan web server
│   └── artisan
│
└── public_html/             <-- Root website utama sekolah (WordPress dsb.)
    └── monitoring/          <-- Subdomain document root diarahkan ke symlink
                             ATAU Document Root Subdomain langsung diarahkan ke:
                             /home/USER/madrasah-one/public
```

---

## 2. Langkah-Langkah Deployment Terstruktur

### Langkah 1: Persiapan dan Kompilasi di Komputer Lokal Pengembang
1. Pastikan seluruh pengujian lokal lulus:
   ```bash
   php artisan test
   ```
2. Kompilasi aset frontend menjadi file statis siap produksi:
   ```bash
   npm run build
   ```
3. Pastikan folder `public/build` berisi file `manifest.json`, file `.css`, dan `.js` yang telah diminifikasi.
4. Buat arsip `.zip` proyek untuk diunggah. **Kecualikan** folder `node_modules`, `.git`, `.github`, dan file `.env` lokal.

### Langkah 2: Pembuatan Basis Data di cPanel
1. Masuk ke cPanel madrasah.
2. Buka menu **MySQL® Databases**.
3. Buat database baru, contoh: `user_madrasahone`.
4. Buat user database baru dan hasilkan kata sandi acak yang kuat.
5. Tambahkan user ke database dengan memilih opsi **ALL PRIVILEGES**.

### Langkah 3: Unggah Berkas & Ekstrak di cPanel File Manager
1. Buka menu **File Manager** di cPanel.
2. Di root `/home/USER/`, buat folder bernama `madrasah-one`.
3. Unggah file zip proyek ke dalam folder tersebut dan lakukan **Extract**.
4. Buat file `.env` baru di `/home/USER/madrasah-one/.env` dengan menyalin format dari `.env.example`.
5. Sesuaikan parameter `.env` produksi:
   ```dotenv
   APP_NAME="MADRASAH ONE"
   APP_ENV=production
   APP_KEY=base64:GENERATE_KEY_ANDA_DISINI
   APP_DEBUG=false
   APP_URL=https://monitoring.madrasah.sch.id

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=user_madrasahone
   DB_USERNAME=user_dbuser
   DB_PASSWORD="PasswordKuatAnda!"

   QUEUE_CONNECTION=database
   CACHE_STORE=database
   SESSION_DRIVER=database
   SESSION_LIFETIME=120
   ```

### Langkah 4: Pengaturan Izin Hak Akses Direktori
Di cPanel File Manager, pastikan folder berikut memiliki izin tulis (`write permission`):
- `storage/` dan seluruh subfoldernya: **775** atau **755**
- `bootstrap/cache/`: **775** atau **755**

### Langkah 5: Eksekusi Migrasi Basis Data
Jika akun cPanel memiliki fitur **Terminal (SSH)**:
```bash
cd /home/USER/madrasah-one
/usr/local/bin/ea-php84 artisan migrate --force
/usr/local/bin/ea-php84 artisan db:seed --class=InitialSystemSeeder --force
/usr/local/bin/ea-php84 artisan config:cache
/usr/local/bin/ea-php84 artisan route:cache
/usr/local/bin/ea-php84 artisan view:cache
```
*Jika tidak ada Terminal:*
Gunakan fitur export SQL lokal dari migrasi awal, lalu impor via menu **phpMyAdmin** di cPanel.

### Langkah 6: Konfigurasi cPanel Cron Job
1. Buka menu **Cron Jobs** di cPanel.
2. Pada bagian *Add New Cron Job*, pilih interval: **Once Per Minute (`* * * * *`)**.
3. Masukkan perintah berikut (sesuaikan path biner PHP 8.4 dan username cPanel):
   ```bash
   /usr/local/bin/ea-php84 /home/USER/madrasah-one/artisan schedule:run >> /dev/null 2>&1
   ```
4. Simpan tugas cron job. Cron ini akan memproses queue database, membersihkan token QR usang, dan membuat rekap otomatis.

---

## 3. Prosedur Pencadangan (Backup) & Pemulihan (Restore)

### Jadwal Backup Rutin
1. **Basis Data:** Dilakukan harian otomatis menggunakan fitur *cPanel Backup Wizard* atau ekspor SQL terjadwal.
2. **Berkas Unggahan Siswa:** Salin direktori `storage/app/private` secara berkala (mingguan) ke penyimpanan eksternal madrasah.

### Prosedur Pemulihan (Restore)
1. Buat database baru di cPanel atau kosongkan tabel yang bermasalah.
2. Impor file cadangan `.sql` terbaru melalui menu **phpMyAdmin**.
3. Pastikan konfigurasi user database di `.env` telah cocok.
4. Uji login sebagai administrator untuk memastikan seluruh relasi data siswa dan absensi pulih sempurna.
