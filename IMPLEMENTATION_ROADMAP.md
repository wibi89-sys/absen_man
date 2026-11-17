# Peta Jalan Implementasi Bertahap (IMPLEMENTATION_ROADMAP.md)
**MADRASAH ONE -- Integrated Student Monitoring System**  
**Versi:** 1.0  
**Tanggal:** 16 November 2026  

---

## 1. Ikhtisar Tahapan Pengembangan

Pembangunan aplikasi MADRASAH ONE mengikuti metode bertahap berdisiplin tinggi sesuai instruksi dokumen Master Spec: **Satu fase diselesaikan, diuji, dan diverifikasi secara tuntas sebelum melangkah ke fase berikutnya.**

```
[Fase 0: Audit & Dokumen] --> [Fase 1: Fondasi Sistem] --> [Fase 2: Absensi Sekolah]
         (SELESAI)                     (AKTIF)
                                          │
[Fase 5: Akademik/BK/Disiplin] <-- [Fase 4: Portal Ortu] <-- [Fase 3: Monitoring Ibadah]
         │
[Fase 6: QA, Optimasi & Produksi]
```

---

## 2. Rincian Setiap Fase & Kriteria Penerimaan

### Fase 0: Audit Lingkungan, Dokumen Arsitektur & Perencanaan
- **Status:** **SELESAI (100%)**
- **Capaian:**
  - Audit lingkungan Laragon (PHP 8.4.12 NTS, MySQL 8.4.3, Composer 2.10.2, Node v22.23.2, npm v10.9.8).
  - Verifikasi kompatibilitas rilis stabil Laravel 13 (`v13.42.0`) dan Livewire 4 (`v4.4.8`).
  - Pembuatan 9 dokumen master teknis (`SYSTEM.md`, `TECHNOLOGY_STACK.md`, `ENVIRONMENT_AUDIT.md`, `DATABASE_DESIGN.md`, `MODULE_ARCHITECTURE.md`, `SECURITY_GUIDELINES.md`, `HOSTING_REQUIREMENTS.md`, `DEPLOYMENT_CPANEL.md`, `IMPLEMENTATION_ROADMAP.md`).
  - Inisialisasi basis data lokal `madrasah_one`.

---

### Fase 1: Fondasi Aplikasi, Autentikasi, RBAC & Master Data
- **Status:** **SELESAI (100%)**
- **Ruang Lingkup:**
  1. Inisialisasi proyek Laravel 13 dengan template Blade, Livewire 4, dan Tailwind CSS 4.
  2. Konfigurasi koneksi MySQL 8.4 dan environment lokal `.env`.
  3. Implementasi sistem autentikasi aman (Login, Logout, Reset Kata Sandi, Proteksi Brute-force).
  4. Implementasi Role-Based Access Control (RBAC) dengan 11 peran bawaan.
  5. Layout antarmuka responsif mobile-first, sidebar dinamis berbasis peran, dan tema visual madrasah yang bersih dan elegan.
  6. Migrasi & CRUD Master Data Inti:
     - Tahun Ajaran & Semester
     - Tingkat & Rombongan Belajar (Kelas)
     - Data Siswa (NIS, NISN, status, kelas aktif)
     - Data Guru & Tenaga Kependidikan (NIP, gelar)
     - Data Orang Tua / Wali & Relasi Verifikasi Anak
     - Data Ruang / Lokasi Madrasah
  7. Audit Log dasar untuk pencatatan aktivitas pengguna.
  8. Seeder data contoh realistis bertanda dummy (`[DUMMY]`) untuk pengujian.
  9. Dashboard awal adaptif sesuai peran pengguna.
- **Kriteria Penerimaan (Gate 1):**
  - Seluruh migrasi dan seeder berhasil dieksekusi tanpa error.
  - Alur login berhasil mengarahkan pengguna ke dashboard sesuai perannya.
  - CRUD master data berfungsi dengan validasi server-side dan konfirmasi aksi.
  - Tampilan diuji responsif pada layar ponsel pintar (viewport 375px) dan desktop.
  - Test suite dasar (Unit & Feature Test) lulus 100%.

---

### Fase 2: Absensi Harian Sekolah (School Attendance)
- **Status:** **SELESAI (100%)**
- **Ruang Lingkup:**
  1. Manajemen sesi absensi harian (`attendance_sessions` & `attendance_types`: Masuk pagi, Apel, Pulang).
  2. Antarmuka lembar pencatatan cepat (`attendance.sheet`) per rombel dengan tombol aksi "Tandai Semua Hadir" untuk Guru Piket dan Wali Kelas.
  3. Status kehadiran lengkap: Hadir (`present`), Terlambat (`late` + menit keterlambatan), Sakit (`sick`), Izin (`excused`), Alpa (`absent`), Pulang Awal (`early_departure`).
  4. Unique constraint pada basis data (`['attendance_session_id', 'student_id']`) untuk menjamin tidak ada duplikasi data presensi siswa yang tidak disengaja.
  5. Alur koreksi status presensi interaktif (`attendance.correct`) dengan kewajiban mengisi alasan tertulis untuk kepatuhan audit log (`attendance_corrections`).
  6. Rekapitulasi presensi harian dan rekap periode bulanan per rombel (`attendance.recap`) dengan perhitungan tingkat kehadiran otomatis (%).
- **Kriteria Penerimaan (Gate 2):**
  - Akses presensi dibatasi hanya bagi peran berwenang (`guru_piket`, `wali_kelas`, `admin`, `kepsek`, `waka`, `super_admin`).
  - Pencegahan duplikasi teruji secara unit & feature test.
  - Alur koreksi presensi tersimpan lengkap dengan jejak alasan dan user penanggung jawab.
  - Test suite Feature & Unit bertambah menjadi 23 tests (57 assertions) lulus 100%.

---

### Fase 3: Monitoring Ibadah Terpadu (Zuhur & Asar)
- **Status:** **SELESAI & TERUJI (16 November 2026)**
- **Hasil Implementasi:**
  1. Skema Database Ibadah (`worship_types`, `worship_schedules`, `worship_schedule_groups`, `worship_sessions`, `worship_qr_tokens`, `worship_attendances`, `worship_session_participants`, `worship_exemptions`) dengan indeks optimal.
  2. Ketetapan Denominator Peserta Wajib: Peserta rombel terkunci secara deterministik saat sesi dibuat sebelum proses scan, menjamin siswa yang tidak hadir tetap masuk dalam kalkulasi denominator laporan.
  3. Mesin QR Dinamis Kriptografis (`WorshipQrService`): Token QR bertanda tangan waktu (HMAC SHA-256) berotasi berkala setiap 20-30 detik dengan proteksi anti-replay & anti-screenshot.
  4. Layar QR Proyektor / Masjid (`worship.sessions.display-qr`): Tampilan layar lebar proyektor resolusi tinggi dengan auto-sync token, progress countdown bar, dan penghitung kehadiran real-time.
  5. Antarmuka Scanner Kamera & Fallback Petugas (`worship.sessions.scan`): Scanner berbasis `html5-qrcode` untuk kamera siswa mandiri dan fallback input NIS/barcode scanner bagi siswa tanpa perangkat.
  6. Alur Verifikasi Bertingkat: Bukti scan awal siswa -> Konfirmasi Wali Kelas -> Finalisasi Petugas Keagamaan dengan auto-mark siswa belum scan menjadi 'Tidak Hadir'.
  7. Modul Dispensasi Ibadah Sensitif (`worship.exemptions`): Pengelolaan halangan syar'i siswi dan kondisi medis dengan jaminan kerahasiaan mutlak (tertutup untuk guru umum/publik, rekap umum hanya menampilkan label netral "Dispensasi").
  8. Rekapitulasi Ibadah Komprehensif (`worship.recap`): Laporan kehadiran berperiode, per rombel, pemisahan Zuhur & Asar, serta fitur ekspor CSV.
- **Kriteria Penerimaan (Gate 3):**
  - 10 Feature test baru mencakup deterministik denominator, auto-dispensasi, rotasi QR, pencegahan replay, penolakan token kedaluwarsa, penolakan siswa luar gelombang, proteksi privasi halangan syar'i, verifikasi bertingkat, dan sesi Asar.
  - Test suite keseluruhan meningkat menjadi **33 tests (96 assertions) lulus 100%**.
  - Formatter Laravel Pint lulus tanpa peringatan.
  - Asset frontend terkompilasi optimal via Vite.

---

### Fase 4: Portal Orang Tua & Komunikasi Terverifikasi
- **Status:** **SELESAI & TERUJI (16 November 2026)**
- **Hasil Implementasi:**
  1. Skema Basis Data Komunikasi (`announcements`, `parent_messages`) dan relasi relasional terverifikasi (`student_parent` is_verified).
  2. Pertahanan IDOR (Insecure Direct Object Reference) Tingkat Tinggi: Orang tua hanya dapat mengakses data siswa jika relasi telah diverifikasi sah oleh admin (`hasVerifiedChild()`). Akses terhadap siswa lain atau tautan yang belum disetujui diblokir dengan HTTP 403 Forbidden.
  3. Dashboard Khusus Orang Tua (`dashboard.parent`): Menampilkan kartu ringkasan putra-putri yang terhubung sah, snapshot presensi masuk harian, snapshot status salat berjamaah, pengumuman madrasah, dan pesan langsung dari wali kelas.
  4. Halaman Deep-Dive Siswa (`parent.children.show`): Riwayat presensi sekolah 30 hari, riwayat salat berjamaah, dan proteksi kerahasiaan siswi (alasan medis/syar'i ditutupi secara aman dengan label netral "Dispensasi").
  5. Papan Pengumuman Madrasah (`communication.announcements` & `parent.announcements`): Pengelolaan pengumuman terarah (semua, khusus orang tua, khusus siswa, khusus guru) dengan fitur pin to top dan kategori (akademik, ibadah, presensi, darurat).
  6. Pesan Terarah Wali Kelas (`communication.messages` & `parent.messages`): Saluran komunikasi resmi dari wali kelas ke orang tua terverifikasi disertai fitur penanda status pesan telah dibaca (`markAsRead`).
- **Kriteria Penerimaan (Gate 4):**
  - 10 Feature test baru mencakup akses dashboard orang tua sah, peringatan tautan belum disetujui, pembuktian blokir IDOR pada tautan belum terverifikasi dan siswa orang lain, penutupan kebocoran catatan dispensasi sensitif, filter pengumuman khusus orang tua, dan pengiriman pesan wali kelas.
  - Test suite keseluruhan meningkat menjadi **43 tests (124 assertions) lulus 100%**.
  - Formatter Laravel Pint lulus tanpa peringatan.
  - Asset frontend terkompilasi optimal via Vite.

---

### Fase 5: Akademik, Kedisiplinan, Bimbingan Konseling (BK) & Kegiatan Siswa
- **Status:** **SELESAI & TERUJI (16 November 2026)**
- **Hasil Implementasi:**
  1. Skema Database Akademik & Kesiswaan (`subjects`, `class_subjects`, `academic_grades`, `discipline_categories`, `discipline_records`, `counseling_sessions`, `student_activities`, `student_activity_members`, `student_activity_attendances`).
  2. Manajemen Mata Pelajaran & Penugasan Guru ke Rombel (`SubjectController`).
  3. Lembar Penilaian Massal Guru (`AcademicGradeController`): Entri nilai per asesmen dan sakelar publikasi nilai ke portal orang tua.
  4. Buku Catatan Kedisiplinan Siswa (`DisciplineController`): Kategori pelanggaran & prestasi, akumulasi poin kedisiplinan, dan alur penanganan status (`dilaporkan` -> `diproses` -> `selesai`).
  5. Modul Bimbingan Konseling (BK) Sangat Rahasia (`CounselingController`): Isolasi ketat hak akses hanya untuk `guru_bk` dan `super_admin`. Seluruh akses dan pembuatan kasus diaudit secara otomatis.
  6. Manajemen Ekstrakurikuler & Organisasi Siswa (`StudentActivityController`): Keanggotaan, pencatatan presensi kegiatan sore, dan generator sesi salat Asar otomatis berdasarkan peserta kegiatan sore.
- **Kriteria Penerimaan (Gate 5):**
  - 7 Feature test baru mencakup penugasan mapel, entri nilai & toggle publikasi, siklus kedisiplinan, isolasi privasi BK (blokir 403 untuk guru/orang tua), dan generator sesi Asar.
  - Test suite meningkat menjadi **50 tests (150 assertions) lulus 100%**.

---

### Fase 6: QA Komprehensif, Optimasi Kinerja, Deployment cPanel & Serah Terima
- **Status:** **SELESAI & TERUJI (16 November 2026) - APLIKASI SIAP PRODUKSI**
- **Hasil Implementasi:**
  1. **Full Regression & Security QA (`SecurityAndEdgeCasesTest`):**
     - Pembuktian mitigasi brute-force via Laravel RateLimiter pada endpoint login.
     - Penegakan matriks RBAC (Role-Based Access Control) di 11 peran pengguna.
     - Pembuktian pertahanan IDOR (Insecure Direct Object Reference) pada portal orang tua.
     - Pembuktian isolasi data sensitif: dispensasi syar'i siswi ditutupi label netral "Dispensasi", sesi konseling BK terisolasi penuh.
     - Pencegahan XSS pada konten pengumuman dan catatan kesiswaan melalui Blade auto-escaping.
     - Rate limiting pada endpoint pemindaian QR proyektor (`throttle:120,1` dan `throttle:60,1`).
  2. **Optimasi Performa & Query Eloquent (Shared Hosting Ready):**
     - Audit N+1 query: penambahan eager loading dan batching pada portal orang tua dan seluruh modul harian.
     - Verifikasi kompilasi cache produksi: `php artisan route:cache`, `config:cache`, dan `view:cache` 100% sukses tanpa issue closure route.
     - Kompilasi asset Vite produksi (`npm.cmd run build`) tuntas dalam <350ms.
  3. **Infrastruktur & Prosedur Backup cPanel:**
     - Perintah Artisan khusus shared hosting `php artisan madrasah:backup` dengan unbuffered streaming cursor, dump struktur DDL & DML, dan rotasi otomatis 7 arsip terakhir.
     - Penjadwalan harian otomatis via Laravel Scheduler (`Schedule::command('madrasah:backup')->dailyAt('01:00')`).
     - Pembuatan berkas root `.htaccess` khusus shared hosting cPanel yang mengalihkan request ke folder `/public/` secara aman serta memblokir akses ke berkas sensitif (`.env`, `.git`, `composer.*`, `storage`).
     - Penambahan Security Headers (`X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection`, `Referrer-Policy`) pada `public/.htaccess`.
  4. **Buku Panduan Operasional Lengkap:**
     - `docs/PANDUAN_ADMIN_TU.md` (Administrasi, verifikasi orang tua, backup, audit).
     - `docs/PANDUAN_GURU_WALI_KELAS.md` (Lembar presensi cepat, nilai, kedisiplinan, pesan orang tua).
     - `docs/PANDUAN_PETUGAS_IBADAH.md` (Layar proyektor QR, scanner, dispensasi syar'i, rekap Asar).
     - `docs/PANDUAN_ORANG_TUA.md` (Monitoring presensi anak, kepatuhan salat, pengumuman, pesan).
- **Kriteria Penerimaan (Gate 6):**
  - Seluruh test suite mencapai **57 tests (177 assertions) lulus 100% tanpa error**.
  - Formatter Laravel Pint lulus 100% bersih.
  - Web Server Apache Laragon & MySQL FastCGI merespons HTTP 200 OK di `http://madrasah-one.test/`.

---

## 3. Catatan Keputusan yang Masih Terbuka (Daftar Pertanyaan untuk Madrasah)

Berikut adalah daftar parameter sekolah yang sementara menggunakan nilai default/fleksibel dan dapat disesuaikan kemudian oleh Administrator melalui antarmuka pengaturan:

1. **Identitas Resmi:** Nama resmi madrasah, logo resmi, dan alamat lengkap untuk kop dokumen laporan.
2. **Aturan Jam Keterlambatan:** Jam batas toleransi masuk sekolah sebelum siswa ditandai "Terlambat".
3. **Pembagian Gelombang Zuhur:** Daftar kelas mana saja yang masuk Gelombang 1 dan Gelombang 2.
4. **Kebijakan Absensi Asar:** Apakah seluruh siswa wajib mengikuti salat Asar atau hanya siswa yang memiliki jadwal ekskul/kegiatan sore.
5. **Format Penomoran NIS/NISN:** Standar validasi format digit nomor induk siswa.
6. **Kredensial cPanel:** Subdomain resmi yang akan digunakan untuk mengarahkan document root aplikasi.
