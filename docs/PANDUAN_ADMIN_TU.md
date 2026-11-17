# Panduan Operasional: Administrator & Staf Tata Usaha (TU)
## Aplikasi MADRASAH ONE - Sistem Terpadu Manajemen Madrasah Aliyah

---

### 1. Hak Akses & Tanggung Jawab
Pengguna dengan peran **Super Admin** dan **Admin (Tata Usaha)** memegang wewenang penuh atas konfigurasi sistem dasar, data induk madrasah, keamanan akun, dan kesinambungan backup data.

---

### 2. Manajemen Data Induk (Master Data)

#### A. Tahun Ajaran & Semester
1. Akses menu: **Master Data > Tahun Ajaran** (`/master/academic-years`).
2. Masukkan nama tahun ajaran baru (contoh: `2026/2027`), tanggal mulai, dan tanggal selesai.
3. Klik tombol **Set Aktif** pada tahun ajaran yang sedang berjalan.
   > [!IMPORTANT]
   > Sistem hanya memperbolehkan tepat 1 (satu) tahun ajaran berstatus aktif dalam satu waktu. Seluruh sesi absensi dan rombel akan terkoneksi otomatis ke tahun ajaran aktif ini.

#### B. Lokasi Presensi & Koordinat GPS
1. Akses menu: **Master Data > Lokasi Sekolah** (`/master/locations`).
2. Tentukan titik koordinat (Latitude, Longitude) dan Radius Geofence (dalam satuan meter, misal: 100m) untuk area madrasah atau masjid.
3. Lokasi ini digunakan sebagai titik validasi saat verifikasi sesi ibadah atau presensi mandiri.

#### C. Rombongan Belajar (Rombel/Kelas)
1. Akses menu: **Master Data > Rombel** (`/master/classes`).
2. Buat kelas baru sesuai tingkatan (Kelas X, XI, XII).
3. Tentukan Wali Kelas dari daftar guru aktif serta kapasitas maksimum kelas (standar: 36 siswa).

#### D. Data Siswa & Guru
1. Akses menu: **Master Data > Data Siswa** (`/master/students`) dan **Data Guru** (`/master/teachers`).
2. Pastikan NIS/NIP terisi unik tanpa duplikasi.
3. Status siswa terdiri dari: `active`, `inactive`, `graduated`, `moved`, `dropped_out`.
   > [!NOTE]
   > Siswa yang berstatus selain `active` tidak akan ditarik ke dalam denominator peserta wajib salat berjamaah atau lembar presensi aktif.

---

### 3. Penautan & Verifikasi Akun Orang Tua (Pencegahan IDOR)

Demi mematuhi regulasi perlindungan data pribadi anak, orang tua yang membuat akun **TIDAK DAPAT LANGSUNG** melihat riwayat nilai atau absensi siswa sebelum diverifikasi sah oleh Staf TU:

1. Akses menu: **Master Data > Orang Tua & Verifikasi** (`/master/parents`).
2. Pada tabel akun orang tua, periksa daftar anak yang diajukan.
3. Klik tombol **Tautkan Siswa** atau klik **Verifikasi Sah**.
4. Begitu status menjadi **Terverifikasi**, orang tua dapat mengakses kartu pemantauan anak di Portal Orang Tua.
5. Jika ada indikasi klaim sepihak atau kesalahan relasi, klik **Batalkan Verifikasi** untuk segera memutus akses.

---

### 4. Prosedur Backup & Pemeliharaan Database

Aplikasi menyediakan perintah Artisan bawaan yang dirancang khusus untuk eksekusi tanpa batas pada shared hosting cPanel:

#### A. Backup Manual via Terminal / SSH / Web Terminal:
```bash
php artisan madrasah:backup
```
- File dump SQL akan disimpan di folder `storage/app/backups/backup_madrasah_one_YYYY_MM_DD_HHmmss.sql`.
- File mencakup seluruh struktur tabel (`DROP/CREATE`) dan data (`INSERT`) dengan integritas relasi foreign keys dinonaktifkan sementara saat restore.

#### B. Otomatisasi Harian via Cron Job cPanel:
Atur baris Cron Job di cPanel pada interval setiap malam pukul 01:00:
```bash
* * * * * /usr/local/bin/php /home/USER_CPANEL/public_html/artisan schedule:run >> /dev/null 2>&1
```
Sistem secara otomatis mengeksekusi `madrasah:backup` setiap pukul 01:00 dini hari dan membatasi penyimpanan ke 7 arsip terakhir untuk menghemat kuota disk.

---

### 5. Audit Trail & Log Keamanan
Setiap perubahan data penting (koreksi absensi, pemberian nilai, tindakan disiplin, login gagal) tercatat otomatis dalam tabel `audit_logs` dan `login_audits` lengkap dengan IP Address, User Agent, dan rekaman nilai sebelum vs sesudah perubahan.
