# MADRASAH ONE

## Dokumen Master Spesifikasi, Arsitektur, dan Instruksi Implementasi untuk Antigravity

**Versi dokumen:** 1.0\
**Tanggal:** 7 November 2026\
**Status:** Baseline teknis dan kebutuhan awal\
**Nama proyek:** MADRASAH ONE -- Integrated Student Monitoring System\
**Target pengguna:** Madrasah Aliyah (MA), sekitar 300--1.000 siswa\
**Target deployment:** Shared hosting berbasis cPanel, tanpa
ketergantungan wajib pada VPS

------------------------------------------------------------------------

# 1. Instruksi Utama untuk Antigravity

Anda bertindak sebagai **Lead Software Architect, Senior Laravel
Developer, Database Engineer, UI/UX Engineer, QA Engineer, dan DevOps
Engineer** untuk membangun MADRASAH ONE.

Bangun aplikasi secara bertahap, teruji, terdokumentasi, dan kompatibel
dengan lingkungan produksi shared hosting cPanel. Jangan langsung
membuat seluruh modul sekaligus. Mulai dengan audit lingkungan,
dokumentasi desain, pemeriksaan dependency, lalu implementasikan fase
demi fase.

## Prinsip yang tidak boleh dilanggar

1.  **Kompatibilitas lokal dan produksi adalah prioritas.** Lingkungan
    lokal pengguna menggunakan Laragon 2026, Apache 2.4.68, PHP 8.4.12
    NTS, dan MySQL 8.4.3. Hosting cPanel yang dituju menyediakan PHP 8.4
    dan MySQL. Pastikan konfigurasi dan dependency kompatibel dengan
    kedua lingkungan.
2.  Target produksi adalah **shared hosting cPanel**. Aplikasi tidak
    boleh mewajibkan VPS, Docker, Redis, Supervisor, Laravel Octane,
    WebSocket server, Node.js persistent process, atau layanan cloud
    berbayar.
3.  Gunakan satu aplikasi Laravel dengan arsitektur **Modular
    Monolith**. Jangan membuat backend Node.js terpisah.
4.  Frontend harus ringan, mobile-first, mudah dipahami guru dan orang
    tua, serta hemat bandwidth.
5.  Semua proses penting harus memiliki validasi server-side, otorisasi,
    audit trail, dan penanganan kegagalan.
6.  Jangan mengarang data sekolah, kebijakan, jadwal, struktur kelas,
    atau aturan ibadah. Jadikan hal yang belum diketahui sebagai
    konfigurasi yang dapat diatur administrator.
7.  Jangan menganggap QR scan otomatis berarti siswa benar-benar
    mengikuti kegiatan. Scan adalah bukti awal; status akhir harus
    mengikuti alur verifikasi.
8.  Lindungi data pribadi siswa. Informasi pengecualian ibadah yang
    sensitif tidak boleh terlihat oleh pengguna yang tidak berwenang dan
    tidak boleh menjadi bahan penilaian moral.
9.  Jangan menghapus atau mengubah data produksi secara destruktif.
    Semua migrasi harus aman, dapat ditinjau, dan disertai backup
    sebelum perubahan berisiko.
10. Jangan berpindah ke fase berikutnya sebelum fase saat ini lulus
    pengujian dan dokumentasi diperbarui.

Jika ada konflik antara keinginan fitur dan batasan shared hosting,
jelaskan konsekuensi dan berikan solusi yang tetap dapat berjalan pada
shared hosting terlebih dahulu.

------------------------------------------------------------------------

# 2. Gambaran Produk

MADRASAH ONE adalah sistem pemantauan siswa terintegrasi untuk Madrasah
Aliyah. Aplikasi menghubungkan pengelolaan data siswa, kehadiran,
kegiatan pembelajaran, monitoring ibadah, kedisiplinan, bimbingan
konseling, kegiatan siswa, dan komunikasi orang tua.

Tujuan utama:

-   Menyediakan data siswa yang konsisten dan mudah ditelusuri.
-   Mempercepat pencatatan dan rekapitulasi kehadiran.
-   Membantu madrasah memantau aktivitas siswa secara terstruktur.
-   Menyediakan informasi yang relevan bagi orang tua/wali.
-   Membantu pimpinan mengambil keputusan melalui dashboard dan laporan.
-   Mengurangi pencatatan manual dan duplikasi data.
-   Menjaga kerahasiaan data pribadi dan data konseling.

## 2.1 Pengguna dan skala

-   Perkiraan siswa: 300--1.000.
-   Pengguna lain: kepala madrasah, wakil kepala, administrator, guru,
    wali kelas, petugas piket, petugas keagamaan, guru BK, orang
    tua/wali.
-   Penggunaan melalui browser desktop dan smartphone.
-   Koneksi pengguna dapat berupa Wi-Fi sekolah maupun internet seluler
    dengan bandwidth terbatas.
-   Jumlah akun bukan berarti seluruh akun mengakses aplikasi secara
    bersamaan. Lakukan uji beban untuk memperkirakan concurrency yang
    realistis.

## 2.2 Batasan tahap awal

Aplikasi bukan pengganti sistem akademik nasional atau sistem resmi
pemerintah. Integrasi eksternal hanya dilakukan jika tersedia API resmi,
izin, dokumentasi, dan kebutuhan yang jelas. Jangan melakukan scraping
atau mengakses sistem eksternal tanpa otorisasi.

------------------------------------------------------------------------

# 3. Keputusan Teknologi

## 3.1 Baseline stack

Gunakan kombinasi berikut sebagai baseline:

  -----------------------------------------------------------------------
  Lapisan                             Teknologi
  ----------------------------------- -----------------------------------
  Framework backend                   Laravel 13.x

  Runtime PHP lokal                   PHP 8.4.12 NTS

  Runtime PHP produksi                PHP 8.4 pada cPanel

  Database lokal                      MySQL 8.4.3

  Database produksi                   MySQL yang disediakan hosting
                                      cPanel

  UI server-rendered                  Laravel Blade

  Interaksi dinamis                   Livewire 4.x, setelah verifikasi
                                      kompatibilitas dependency

  CSS                                 Tailwind CSS 4.x

  JavaScript ringan                   Alpine.js sesuai integrasi Livewire

  Asset bundler                       Vite

  Package manager frontend            npm

  Dependency backend                  Composer

  Web server lokal                    Apache Laragon

  Web server produksi                 Apache cPanel

  Scheduler                           Laravel Scheduler melalui cPanel
                                      Cron Job

  Queue awal                          Database queue atau proses sinkron
                                      yang terkontrol

  Cache awal                          File cache atau database cache

  Session                             Database atau file, pilih
                                      berdasarkan hasil pengujian hosting

  Version control                     Git
  -----------------------------------------------------------------------

**Penting:** jangan mengunci nomor patch dependency berdasarkan tebakan.
Gunakan rilis stabil yang tersedia pada saat instalasi, periksa
constraint PHP/Laravel, dokumentasikan versi aktual di `composer.lock`
dan `package-lock.json`, lalu jalankan seluruh pengujian.

## 3.2 Mengapa tidak menggunakan SPA terpisah?

Untuk tahap awal, gunakan Blade + Livewire agar:

-   Backend dan tampilan berada dalam satu aplikasi.
-   Tidak memerlukan server Node.js di produksi.
-   Navigasi dan interaksi cukup dinamis tanpa kompleksitas SPA penuh.
-   Deployment lebih sederhana di cPanel.
-   Beban JavaScript dan bandwidth dapat dikendalikan.

Jangan menambahkan React, Vue, Next.js, Inertia, atau backend Node.js
terpisah kecuali ada kebutuhan terukur dan persetujuan perubahan
arsitektur.

## 3.3 Node.js hanya untuk build

Node.js digunakan pada komputer pengembangan untuk memasang dependency
dan membangun aset frontend. Server produksi tidak perlu menjalankan
Node.js secara terus-menerus.

Contoh proses lokal:

``` bash
npm install
npm run build
```

Folder hasil build `public/build` harus ikut diunggah ke hosting. Jangan
menjalankan development server Vite di produksi.

## 3.4 Teknologi yang tidak diwajibkan

Tidak boleh ada ketergantungan wajib terhadap:

-   VPS atau dedicated server.
-   Docker.
-   Redis.
-   Laravel Octane.
-   Horizon.
-   Supervisor.
-   WebSocket server.
-   Node.js persistent runtime.
-   Kubernetes.
-   Elasticsearch.
-   Layanan notifikasi berbayar.
-   Layanan penyimpanan cloud berbayar.

Fitur tambahan boleh dievaluasi kemudian, tetapi aplikasi inti harus
tetap berfungsi tanpa komponen tersebut.

------------------------------------------------------------------------

# 4. Pemeriksaan Lingkungan Sebelum Coding

Sebelum membuat aplikasi, lakukan pemeriksaan berikut dan simpan
hasilnya di `ENVIRONMENT_AUDIT.md`.

## 4.1 Lingkungan lokal yang diketahui

-   OS: Windows 11.
-   Laragon: 2026 v8.7.0.
-   Apache: 2.4.68.
-   PHP aktif yang tersedia: 8.4.12 NTS.
-   PHP alternatif yang tersedia: 8.3.33.
-   MySQL: 8.4.3.
-   Node.js: tersedia melalui Laragon, versi aktual harus diperiksa.
-   Composer: periksa ketersediaan dan versinya.

Gunakan PHP 8.4.12 sebagai baseline lokal agar sesuai dengan PHP 8.4
pada hosting. Pastikan ekstensi PHP yang diperlukan aktif.

Periksa:

``` bash
php -v
php -m
composer -V
node -v
npm -v
mysql --version
```

Ekstensi yang harus diperiksa sekurang-kurangnya:

-   Ctype
-   cURL
-   DOM
-   Fileinfo
-   Filter
-   Hash
-   Mbstring
-   OpenSSL
-   PCRE
-   PDO
-   PDO MySQL
-   Session
-   Tokenizer
-   XML
-   ZIP
-   GD (jika pemrosesan gambar diperlukan)
-   Intl (jika diperlukan oleh dependency)

## 4.2 Pemeriksaan hosting cPanel

Informasi yang sudah diketahui:

-   Hosting menggunakan cPanel.
-   PHP 8.4 tersedia sebagai account default.
-   Database hosting menggunakan MySQL.
-   Resource hosting dapat ditingkatkan/di-upgrade sesuai kebutuhan.
-   Targetnya shared hosting, bukan VPS.

Hal yang tetap harus diperiksa sebelum deployment:

1.  Versi lengkap MySQL.
2.  Apakah SSH/Terminal tersedia.
3.  Apakah Composer tersedia melalui Terminal.
4.  Ekstensi PHP yang dapat diaktifkan.
5.  Batas ukuran database.
6.  Batas CPU, RAM/Physical Memory, Entry Processes, I/O, IOPS, dan
    jumlah proses.
7.  Batas inode dan disk.
8.  Apakah Cron Job tersedia.
9.  Apakah document root subdomain dapat diarahkan ke folder `public`.
10. Apakah backup database dan file tersedia.
11. Batas upload dan execution time PHP.
12. Apakah outbound SMTP atau layanan email tersedia jika notifikasi
    email dibutuhkan.

Jangan menyimpulkan kapasitas hosting hanya dari versi PHP dan MySQL.
Uji beban pada paket hosting sebenarnya sebelum peluncuran.

## 4.3 Aturan kompatibilitas

-   Gunakan fitur SQL yang kompatibel dengan MySQL hosting.
-   Jangan bergantung pada konfigurasi MySQL yang hanya tersedia di
    lingkungan lokal.
-   Jangan menggunakan ekstensi PHP yang tidak tersedia di cPanel.
-   Jangan menyimpan file upload di direktori publik jika file bersifat
    privat.
-   Jangan mengandalkan symbolic link jika hosting tidak mengizinkannya;
    sediakan strategi deployment alternatif yang aman.
-   Jangan mengasumsikan akses root server.
-   Jangan mengasumsikan proses background dapat berjalan terus-menerus.

------------------------------------------------------------------------

# 5. Arsitektur Aplikasi

Gunakan **Modular Monolith**: satu proyek Laravel, satu database utama,
modul dipisahkan berdasarkan domain bisnis.

Contoh struktur yang disarankan:

``` text
app/
├── Domain/
│   ├── Identity/
│   ├── MasterData/
│   ├── Attendance/
│   ├── Worship/
│   ├── Academic/
│   ├── Discipline/
│   ├── Counseling/
│   ├── Activities/
│   ├── ParentPortal/
│   ├── Reporting/
│   └── Notifications/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Policies/
├── Providers/
├── Services/
├── Support/
└── Jobs/
```

Struktur tersebut adalah pedoman, bukan alasan membuat abstraksi
berlebihan. Gunakan pola yang konsisten dan mudah dipahami tim
pengembang.

Setiap modul harus memiliki batas tanggung jawab jelas. Controller tidak
boleh berisi seluruh logika bisnis. Gunakan Form Request untuk validasi,
Policy/Gate untuk otorisasi, Service/Action untuk proses bisnis yang
kompleks, dan event/listener hanya jika benar-benar dibutuhkan.

------------------------------------------------------------------------

# 6. Peran dan Hak Akses

Sediakan role berikut. Hak akses harus dapat dikonfigurasi, tetapi role
dasar tersedia melalui seeder.

  -----------------------------------------------------------------------
  Role                                Ruang lingkup
  ----------------------------------- -----------------------------------
  Super Admin                         Seluruh konfigurasi dan pengelolaan
                                      sistem

  Admin Madrasah                      Operasional aplikasi dan master
                                      data sesuai kewenangan

  Kepala Madrasah                     Dashboard pimpinan, laporan,
                                      persetujuan tertentu

  Wakil Kepala                        Data sesuai bidang tugas

  Guru                                Kelas/mata pelajaran yang
                                      ditugaskan

  Wali Kelas                          Data dan pemantauan siswa di kelas
                                      binaannya

  Guru BK                             Data konseling sesuai penugasan dan
                                      kerahasiaan

  Petugas Keagamaan                   Jadwal, sesi, dan verifikasi
                                      kegiatan ibadah

  Guru Piket                          Kehadiran dan kejadian sesuai
                                      jadwal piket

  Siswa                               Data diri terbatas, jadwal,
                                      kehadiran, ringkasan aktivitas
                                      miliknya

  Orang Tua/Wali                      Informasi anak yang memiliki relasi
                                      sah dengan akun
  -----------------------------------------------------------------------

Prinsip:

-   Terapkan least privilege.
-   Jangan mengandalkan penyembunyian tombol di frontend.
-   Setiap akses data harus divalidasi di backend.
-   Orang tua hanya dapat melihat anak yang terhubung secara sah.
-   Guru hanya melihat data siswa sesuai penugasan, kecuali diberi
    kewenangan tambahan.
-   Catatan BK dan alasan pengecualian ibadah memiliki akses sangat
    terbatas.
-   Super Admin tetap tercatat dalam audit log ketika mengubah data
    sensitif.

------------------------------------------------------------------------

# 7. Modul Fungsional

## 7.1 Dashboard

Dashboard menyesuaikan role.

### Dashboard pimpinan

-   Jumlah siswa aktif.
-   Jumlah rombongan belajar.
-   Kehadiran hari ini.
-   Siswa terlambat.
-   Siswa tidak hadir.
-   Ringkasan monitoring ibadah.
-   Ringkasan kegiatan dan kedisiplinan.
-   Grafik tren berdasarkan rentang tanggal.
-   Notifikasi/atensi yang membutuhkan tindak lanjut.

### Dashboard wali kelas

-   Jumlah siswa kelas.
-   Kehadiran kelas hari ini.
-   Siswa belum terkonfirmasi.
-   Ringkasan aktivitas kelas.
-   Pengingat tindak lanjut.

### Dashboard orang tua

-   Ringkasan anak.
-   Kehadiran yang telah diverifikasi.
-   Informasi akademik yang telah dipublikasikan.
-   Pengumuman.
-   Riwayat komunikasi yang memang ditujukan kepada orang tua.

Dashboard tidak boleh memuat seluruh rekaman mentah. Gunakan query
agregasi, filter periode, pagination, dan cache seperlunya.

## 7.2 Master Data

Kelola:

-   Tahun ajaran.
-   Semester.
-   Tingkat/kelas.
-   Rombongan belajar.
-   Siswa.
-   Guru dan tenaga kependidikan.
-   Mata pelajaran.
-   Penugasan guru.
-   Orang tua/wali.
-   Relasi siswa-orang tua.
-   Ruang/lokasi.
-   Jenis kegiatan.
-   Jadwal sekolah.
-   Kalender akademik.

Fitur:

-   Tambah, ubah, nonaktifkan, dan lihat data.
-   Impor siswa melalui CSV/XLSX jika aman dan diperlukan.
-   Validasi duplikasi NIS/NISN sesuai kebijakan.
-   Riwayat perpindahan kelas dan status siswa.
-   Arsip tahun ajaran sebelumnya tanpa menghapus histori.

Jangan hard-delete data siswa yang sudah memiliki transaksi. Gunakan
status aktif/nonaktif/keluar/lulus dan mekanisme arsip.

## 7.3 Absensi Sekolah

Sediakan pencatatan:

-   Kehadiran masuk sekolah.
-   Keterlambatan.
-   Ketidakhadiran.
-   Izin.
-   Sakit.
-   Pulang lebih awal.
-   Catatan petugas.

Metode harus dapat dikonfigurasi. Tahap awal dapat menggunakan input
guru piket/wali kelas dan QR jika kebijakan sekolah menyetujuinya.

Ketentuan:

-   Setiap catatan memiliki tanggal, siswa, status, pencatat, waktu, dan
    sumber pencatatan.
-   Cegah duplikasi absensi untuk siswa dan jenis sesi yang sama.
-   Koreksi harus memiliki alasan dan audit log.
-   Orang tua menerima ringkasan setelah data diverifikasi.
-   Jangan mengirim notifikasi berulang untuk perubahan yang sama.
-   Jika QR digunakan, QR tidak boleh menjadi satu-satunya bukti tanpa
    aturan validasi.

## 7.4 Monitoring Ibadah Zuhur dan Asar

Modul ini merupakan fitur penting dan harus dirancang fleksibel sesuai
kebijakan madrasah.

### A. Zuhur

-   Kegiatan Zuhur dapat dibagi menjadi beberapa gelombang/kelompok
    kelas.
-   Jadwal gelombang dapat diatur berdasarkan hari, kelas, lokasi, jam,
    dan petugas.
-   Sistem membuat sesi berdasarkan jadwal yang berlaku.
-   Peserta sesi ditentukan dari kelompok yang dijadwalkan.
-   Perubahan gelombang harus dicatat dan memiliki otorisasi.

### B. Asar

-   Peserta ditentukan berdasarkan jadwal pembelajaran dan kegiatan
    siswa.
-   Siswa yang kegiatan sekolahnya telah selesai sebelum waktu Asar
    tidak otomatis dimasukkan sebagai peserta.
-   Siswa yang masih mengikuti ekstrakurikuler atau kegiatan sekolah
    dapat dimasukkan sesuai kebijakan.
-   Sistem harus dapat mengatur aturan kelayakan peserta dan
    pengecualian.

### C. Alur konfirmasi

1.  Sistem membuat sesi ibadah berdasarkan jadwal.
2.  Sistem membuat daftar peserta yang wajib diperiksa, termasuk siswa
    yang belum melakukan scan.
3.  Petugas menampilkan QR dinamis untuk sesi tersebut.
4.  Siswa melakukan scan melalui perangkat yang diizinkan.
5.  Sistem menyimpan catatan scan sebagai bukti awal.
6.  Wali kelas/guru melakukan konfirmasi sesuai tanggung jawab.
7.  Petugas keagamaan melakukan verifikasi akhir atau finalisasi.
8.  Status final ditampilkan di dashboard dan ringkasan orang tua.

QR scan tidak otomatis menjadikan status hadir final.

### D. QR dinamis dan keamanan

-   Token QR terkait sesi, bukan identitas siswa yang terbuka.
-   Token memiliki masa berlaku singkat.
-   Token ditandatangani atau menggunakan mekanisme acak yang aman.
-   Token tidak boleh dapat digunakan kembali di luar sesi.
-   Terapkan rate limiting.
-   Cegah replay/scan ganda.
-   Validasi sesi aktif, waktu, peserta, dan status.
-   Simpan waktu scan dan identitas pengguna/perangkat bila relevan dan
    proporsional.
-   Jangan mengandalkan GPS sebagai satu-satunya bukti kehadiran.
-   Siapkan input manual sebagai fallback ketika kamera atau jaringan
    bermasalah.

### E. Daftar peserta dan status

Tabel peserta sesi wajib dibuat saat sesi dibuat, bukan hanya ketika
siswa scan. Dengan demikian, siswa yang tidak scan tetap masuk
denominator rekap.

Status minimal:

-   `pending_verification`
-   `present`
-   `late`
-   `absent`
-   `excused`
-   `exempt`

Makna dan transisi status harus didokumentasikan. Status final tidak
boleh ditentukan hanya dari scan.

### F. Pengecualian sensitif

-   Sistem dapat mencatat pengecualian sesuai kebijakan madrasah.
-   Alasan sensitif, termasuk kondisi pribadi siswa, hanya dapat dilihat
    oleh petugas yang berwenang.
-   Guru umum dan orang tua lain tidak boleh melihat alasan sensitif.
-   Jangan menampilkan alasan sensitif pada dashboard umum atau laporan
    publik.
-   Jangan memberi label moral atau membuat pemeringkatan siswa
    berdasarkan pengecualian.
-   Catat persetujuan/perubahan pengecualian secara terbatas dan audit.

### G. Rekap

-   Rekap per siswa.
-   Rekap per kelas.
-   Rekap per sesi.
-   Rekap Zuhur dan Asar secara terpisah.
-   Rekap berdasarkan periode.
-   Daftar belum diverifikasi.
-   Daftar koreksi.
-   Ekspor hanya bagi role yang berwenang.

## 7.5 Akademik

Tahap awal dapat menyediakan:

-   Mata pelajaran.
-   Jadwal pelajaran.
-   Penugasan guru.
-   Nilai/rekap akademik sesuai kebutuhan madrasah.
-   Catatan perkembangan siswa.
-   Publikasi hasil yang telah disetujui.

Jangan membuat sistem penilaian yang tidak sesuai kebijakan kurikulum
madrasah. Rumus, bobot, dan periode penilaian harus dapat dikonfigurasi.

## 7.6 Kedisiplinan

-   Pencatatan kejadian.
-   Kategori pelanggaran/prestasi.
-   Tanggal dan lokasi.
-   Petugas pencatat.
-   Kronologi dan tindak lanjut.
-   Status penanganan.
-   Lampiran bila diperlukan.

Akses catatan dibatasi sesuai kewenangan. Hindari label permanen yang
merugikan siswa. Perubahan dan penghapusan harus memiliki alasan dan
audit.

## 7.7 Bimbingan Konseling (BK)

-   Catatan konseling bersifat sangat rahasia.
-   Akses utama hanya guru BK dan pejabat yang secara resmi diberi
    kewenangan.
-   Jangan tampilkan isi catatan BK pada dashboard umum, wali kelas,
    atau portal orang tua secara otomatis.
-   Sediakan catatan tindak lanjut dan jadwal pertemuan.
-   Sediakan pencatatan persetujuan/komunikasi sesuai kebijakan sekolah.
-   Audit setiap akses dan perubahan catatan sensitif.
-   Jangan memasukkan isi konseling ke notifikasi umum.

## 7.8 Kegiatan Siswa

-   Ekstrakurikuler.
-   Kegiatan organisasi.
-   Kegiatan keagamaan.
-   Kegiatan sekolah.
-   Penugasan dan keikutsertaan.
-   Kehadiran kegiatan.
-   Pembina/petugas.

Jadwal kegiatan dapat memengaruhi kelayakan peserta monitoring Asar,
sesuai aturan yang ditetapkan administrator.

## 7.9 Portal Orang Tua/Wali

Orang tua dapat:

-   Melihat ringkasan anak yang terhubung.
-   Melihat kehadiran yang telah diverifikasi.
-   Melihat pengumuman.
-   Melihat informasi akademik yang telah dipublikasikan.
-   Menerima informasi keterlambatan/ketidakhadiran sesuai kebijakan.
-   Mengirim konfirmasi atau tanggapan bila fitur diaktifkan.

Orang tua tidak boleh melihat data siswa lain, catatan BK, catatan
internal petugas, atau alasan sensitif pengecualian ibadah.

Verifikasi relasi orang tua-anak harus dilakukan oleh administrator atau
melalui mekanisme verifikasi yang aman.

## 7.10 Laporan

Sediakan laporan dengan filter:

-   Tanggal/periode.
-   Tahun ajaran/semester.
-   Kelas.
-   Siswa.
-   Jenis kegiatan.
-   Status.
-   Petugas.

Format ekspor dapat berupa PDF/XLSX/CSV sesuai kebutuhan. Jangan membuat
ekspor besar tanpa batas. Terapkan pembatasan dan otorisasi.

------------------------------------------------------------------------

# 8. Rancangan Data dan Database

Gunakan migration Laravel. Semua tabel harus memiliki primary key,
foreign key yang tepat, index, timestamps, dan aturan penghapusan yang
aman.

Rancangan awal berikut adalah domain/tabel yang perlu dievaluasi dan
dinormalisasi. Jangan membuat tabel duplikat hanya karena daftar ini.

## 8.1 Identitas dan akses

-   `users`
-   `roles`
-   `permissions`
-   `role_user` atau struktur role-permission yang dipilih
-   `user_profiles`
-   `login_audits`
-   `audit_logs`

## 8.2 Master data

-   `academic_years`
-   `semesters`
-   `grades`
-   `classes`
-   `students`
-   `student_class_histories`
-   `teachers`
-   `subjects`
-   `teacher_assignments`
-   `parents`
-   `student_parent`
-   `school_locations`
-   `school_calendars`
-   `school_schedules`

## 8.3 Absensi sekolah

-   `attendance_types`
-   `attendance_sessions`
-   `daily_attendances`
-   `attendance_corrections`
-   `attendance_evidence` (opsional, dengan pembatasan file)

## 8.4 Ibadah

-   `worship_types`
-   `worship_locations`
-   `worship_schedules`
-   `worship_schedule_groups`
-   `worship_supervisors`
-   `worship_sessions`
-   `worship_session_groups`
-   `worship_session_participants`
-   `worship_qr_tokens`
-   `worship_attendances`
-   `worship_verifications`
-   `worship_exemptions`
-   `worship_exception_approvals`
-   `worship_attendance_corrections`

Daftar peserta sesi harus dibuat untuk seluruh siswa yang eligible
sebelum proses scan. Jangan menggunakan tabel scan sebagai satu-satunya
sumber jumlah peserta.

## 8.5 Akademik, disiplin, BK, kegiatan

-   `student_subject_enrollments`
-   `academic_assessments`
-   `academic_scores`
-   `discipline_categories`
-   `discipline_records`
-   `discipline_follow_ups`
-   `counseling_sessions`
-   `counseling_notes`
-   `counseling_follow_ups`
-   `student_activities`
-   `student_activity_members`
-   `student_activity_schedules`
-   `student_activity_attendances`

Tabel BK harus memiliki kontrol akses paling ketat. Pertimbangkan
pemisahan penyimpanan data sensitif dan enkripsi field tertentu setelah
threat model dibuat.

## 8.6 Notifikasi dan laporan

-   `announcements`
-   `notifications`
-   `notification_logs`
-   `report_exports`
-   `scheduled_tasks` bila diperlukan

## 8.7 Standar database

-   Gunakan InnoDB.
-   Gunakan foreign key.
-   Tambahkan index untuk kolom filter, relasi, tanggal, status, dan
    pencarian yang sering digunakan.
-   Hindari index berlebihan.
-   Gunakan transaksi database untuk proses multi-tabel.
-   Gunakan unique constraint untuk mencegah duplikasi absensi.
-   Gunakan `created_by`, `updated_by`, atau audit log untuk transaksi
    penting.
-   Simpan waktu dengan konsisten; tampilkan sesuai zona waktu
    `Asia/Jakarta`.
-   Gunakan soft delete hanya jika memang diperlukan.
-   Jangan menyimpan nilai turunan yang dapat dihitung ulang tanpa
    alasan performa.
-   Hindari menyimpan file besar sebagai BLOB.
-   Sediakan backup dan uji restore.

Buat `DATABASE_DESIGN.md` yang menjelaskan tabel, kolom penting, relasi,
index, unique constraint, dan alasan desain.

------------------------------------------------------------------------

# 9. Standar UX/UI dan Efisiensi Bandwidth

Desain harus sederhana, modern, konsisten, dan mudah digunakan oleh guru
serta orang tua yang tidak memiliki latar belakang teknis.

## 9.1 Prinsip desain

-   Mobile-first.
-   Responsive untuk smartphone, tablet, dan desktop.
-   Navigasi jelas berdasarkan role.
-   Bahasa antarmuka Indonesia.
-   Istilah sederhana dan konsisten.
-   Form tidak terlalu panjang.
-   Validasi tampil dekat dengan field.
-   Tombol aksi memiliki label jelas.
-   Tampilkan loading, empty state, error state, dan success state.
-   Konfirmasi sebelum aksi destruktif.
-   Hindari animasi berlebihan dan elemen dekoratif berat.
-   Jangan membuat dashboard penuh grafik yang tidak memiliki fungsi.

## 9.2 Optimasi bandwidth

-   Gunakan pagination.
-   Lazy-load gambar.
-   Kompres gambar sebelum upload.
-   Batasi ukuran file dan tipe file.
-   Gunakan aset CSS/JS hasil build yang diminifikasi.
-   Hindari library besar jika fungsi dapat dibuat sederhana.
-   Hindari polling terlalu sering.
-   Jangan refresh seluruh halaman setiap beberapa detik.
-   Muat data berdasarkan kebutuhan halaman.
-   Gunakan cache untuk data referensi dan statistik yang sesuai.
-   Gunakan tabel server-side untuk data besar.
-   Hindari mengirim payload JSON yang tidak diperlukan.
-   Sediakan tampilan yang tetap nyaman pada koneksi seluler lambat.

## 9.3 Aksesibilitas dan kenyamanan

-   Kontras teks cukup.
-   Ukuran tombol nyaman disentuh.
-   Label input jelas.
-   Status tidak dibedakan hanya berdasarkan warna.
-   Pesan kesalahan mudah dipahami.
-   Tabel dapat digunakan pada layar kecil melalui layout responsif.

------------------------------------------------------------------------

# 10. Keamanan dan Privasi

Lakukan threat modeling sederhana sebelum implementasi modul sensitif.

Persyaratan minimum:

1.  Password di-hash menggunakan mekanisme Laravel.
2.  CSRF protection aktif.
3.  Validasi input server-side.
4.  Authorization menggunakan Policy/Gate/Middleware.
5.  Rate limiting pada login, reset password, dan endpoint QR.
6.  Session aman dan konfigurasi cookie sesuai HTTPS.
7.  APP_DEBUG=false pada produksi.
8.  `.env` tidak boleh dapat diakses publik.
9.  Jangan commit `.env` atau secret ke Git.
10. File privat disimpan di lokasi non-public atau dilayani melalui
    controller yang memeriksa izin.
11. Audit log untuk perubahan data penting.
12. Pembatasan percobaan login dan proteksi brute force.
13. Validasi MIME, ekstensi, ukuran, dan nama file upload.
14. Hindari SQL raw yang tidak menggunakan parameter binding.
15. Jangan menampilkan stack trace atau kredensial kepada pengguna.
16. Terapkan backup terenkripsi jika fasilitas memungkinkan.
17. Batasi data pribadi pada laporan dan ekspor.
18. Terapkan kebijakan retensi data dan penghapusan sesuai aturan
    madrasah.
19. Catat akses terhadap data BK dan data pengecualian sensitif.
20. Jangan memasukkan data pribadi ke layanan AI eksternal tanpa
    persetujuan dan dasar yang sah.

------------------------------------------------------------------------

# 11. Notifikasi

Tahap awal gunakan notifikasi dalam aplikasi.

Email atau WhatsApp hanya diaktifkan jika tersedia kanal resmi,
konfigurasi, persetujuan, dan anggaran yang jelas.

Ketentuan:

-   Notifikasi orang tua dikirim setelah data terkait diverifikasi.
-   Hindari notifikasi ganda.
-   Simpan status pengiriman.
-   Sediakan retry terbatas.
-   Pengiriman massal harus bertahap.
-   Jangan mengirim catatan BK atau alasan sensitif.
-   Jika layanan eksternal gagal, operasional inti aplikasi tetap
    berjalan.

Pada shared hosting, gunakan database queue atau proses terjadwal
melalui Cron Job. Jangan mengasumsikan worker persisten tersedia.

------------------------------------------------------------------------

# 12. Deployment Shared Hosting cPanel

Buat panduan lengkap `DEPLOYMENT_CPANEL.md`.

## 12.1 Prinsip direktori

Pilihan terbaik adalah menempatkan source Laravel di luar `public_html`
dan mengarahkan document root domain/subdomain ke folder `public`.

Jika cPanel tidak memungkinkan perubahan document root:

-   Buat strategi deployment alternatif yang aman.
-   Pastikan `.env`, `vendor`, `storage`, file konfigurasi, dan source
    aplikasi tidak dapat diakses publik.
-   Jangan sekadar menyalin seluruh proyek ke `public_html`.
-   Jangan mengandalkan aturan `.htaccess` sebagai satu-satunya lapisan
    keamanan.
-   Dokumentasikan trade-off dan minta persetujuan sebelum menggunakan
    alternatif.

## 12.2 Proses deployment

Dokumentasikan langkah:

1.  Backup file dan database.
2.  Siapkan database dan user database melalui cPanel.
3.  Upload source code ke direktori aman.
4.  Upload hasil build frontend `public/build`.
5.  Siapkan `.env` produksi.
6.  Jalankan Composer install production jika tersedia.
7.  Atur APP_KEY.
8.  Atur koneksi MySQL.
9.  Jalankan migration setelah backup dan review.
10. Atur permission `storage` dan `bootstrap/cache`.
11. Atur document root.
12. Aktifkan SSL.
13. Jalankan optimasi Laravel yang aman.
14. Atur Cron Job.
15. Jalankan smoke test.
16. Periksa log.
17. Uji backup dan restore.

Perintah produksi hanya dijalankan setelah memastikan path dan
environment benar. Jangan menjalankan `migrate:fresh`, `db:wipe`, atau
perintah destruktif pada produksi.

## 12.3 Cron Job

Jika scheduler digunakan, dokumentasikan satu cron entry yang
menjalankan Laravel Scheduler setiap menit, disesuaikan dengan path PHP
CLI dan path aplikasi pada hosting.

Contoh konseptual (path harus diganti sesuai hosting):

``` bash
* * * * * /usr/local/bin/php /home/USER/app/artisan schedule:run >> /dev/null 2>&1
```

Jangan mengasumsikan path PHP CLI di atas pasti sama pada semua
provider.

## 12.4 Backup dan pemulihan

-   Backup database secara terjadwal.
-   Backup file upload.
-   Simpan salinan backup di lokasi terpisah jika tersedia.
-   Tetapkan retensi backup.
-   Dokumentasikan prosedur restore.
-   Uji restore secara berkala.
-   Jangan menganggap backup otomatis provider sebagai satu-satunya
    backup.

------------------------------------------------------------------------

# 13. Pengujian dan Kriteria Penerimaan

Setiap modul wajib memiliki pengujian.

## 13.1 Jenis pengujian

-   Unit test untuk aturan bisnis.
-   Feature test untuk alur pengguna.
-   Authorization test untuk setiap role.
-   Validation test.
-   Database constraint test.
-   Test duplikasi absensi.
-   Test QR expired, replay, dan token tidak valid.
-   Test privasi orang tua.
-   Test akses catatan BK.
-   Test export.
-   Test tampilan responsif secara manual.
-   Smoke test setelah deployment.

## 13.2 Uji beban

Target 1.000 siswa bukan berarti 1.000 request bersamaan. Buat skenario
realistis, misalnya:

-   Guru menginput absensi pada awal hari.
-   Siswa melakukan scan QR pada rentang waktu yang sama.
-   Orang tua membuka portal pada jam tertentu.
-   Admin membuka dashboard dan melakukan rekap.

Uji bertahap pada lingkungan lokal/staging terlebih dahulu, lalu uji
terbatas di hosting dengan persetujuan provider. Pantau CPU, RAM, I/O,
Entry Processes, database connections, response time, dan error rate.

Jangan menyatakan aplikasi siap untuk 1.000 pengguna bersamaan tanpa
hasil pengujian yang mendukung.

## 13.3 Kriteria penerimaan umum

-   Seluruh role hanya dapat mengakses data sesuai kewenangannya.
-   Tidak ada duplikasi absensi yang tidak disengaja.
-   QR tidak dapat digunakan di luar masa berlaku atau sesi.
-   Peserta yang tidak scan tetap terhitung dalam rekap.
-   Status scan belum dianggap final sebelum verifikasi.
-   Orang tua hanya dapat melihat anak yang terhubung.
-   Catatan BK tidak terlihat oleh role umum.
-   Dashboard menggunakan pagination/agregasi.
-   Aplikasi tetap berjalan tanpa Redis dan tanpa VPS.
-   Build frontend dapat dilakukan di lokal dan hasilnya berjalan di
    cPanel.
-   Backup dan restore telah diuji.

------------------------------------------------------------------------

# 14. Roadmap Implementasi

Implementasikan satu fase pada satu waktu.

## Fase 0 -- Audit dan Perencanaan

Tugas:

-   Audit folder proyek dan lingkungan.
-   Periksa PHP, Composer, Node, npm, MySQL, ekstensi PHP.
-   Periksa dependency dan kompatibilitas Laravel 13.
-   Buat dokumentasi arsitektur.
-   Buat ERD awal.
-   Buat threat model ringkas.
-   Buat rencana deployment cPanel.
-   Identifikasi informasi sekolah yang belum tersedia.

Output:

-   `SYSTEM.md`
-   `TECHNOLOGY_STACK.md`
-   `ENVIRONMENT_AUDIT.md`
-   `DATABASE_DESIGN.md`
-   `MODULE_ARCHITECTURE.md`
-   `SECURITY_GUIDELINES.md`
-   `HOSTING_REQUIREMENTS.md`
-   `DEPLOYMENT_CPANEL.md`
-   `IMPLEMENTATION_ROADMAP.md`

Jangan mulai seluruh fitur sebelum audit dan rancangan ditinjau.

## Fase 1 -- Fondasi Aplikasi

-   Instalasi Laravel 13.
-   Konfigurasi database.
-   Autentikasi.
-   Role dan permission.
-   Layout utama.
-   Navigasi responsif.
-   Master data tahun ajaran, semester, kelas, siswa, guru, orang tua.
-   Audit log dasar.
-   Dashboard awal.
-   Seeder data contoh yang jelas ditandai sebagai dummy.

Kriteria selesai:

-   Login/logout berjalan.
-   Hak akses diuji.
-   Master data CRUD berjalan.
-   Migration dan seeder berhasil.
-   Tampilan mobile layak.
-   Tidak ada error kritis.
-   Dokumentasi diperbarui.

## Fase 2 -- Absensi Sekolah

-   Sesi absensi.
-   Input dan verifikasi.
-   Status hadir, terlambat, sakit, izin, alpa, pulang awal.
-   Koreksi dengan alasan.
-   Rekap harian dan periode.
-   Riwayat siswa.
-   Pengujian duplikasi dan otorisasi.

## Fase 3 -- Monitoring Ibadah

-   Master jenis ibadah dan lokasi.
-   Jadwal Zuhur per gelombang.
-   Jadwal Asar berdasarkan kelayakan siswa.
-   Pembuatan sesi.
-   Pembuatan daftar peserta.
-   QR dinamis.
-   Scan dan validasi.
-   Konfirmasi wali kelas.
-   Finalisasi petugas keagamaan.
-   Pengecualian privat.
-   Koreksi dan audit.
-   Rekap per siswa, kelas, sesi, dan periode.

## Fase 4 -- Portal Orang Tua

-   Relasi akun orang tua dengan siswa.
-   Verifikasi relasi.
-   Dashboard orang tua.
-   Ringkasan absensi terverifikasi.
-   Pengumuman.
-   Notifikasi dalam aplikasi.
-   Pengaturan privasi.

## Fase 5 -- Akademik, Kedisiplinan, BK, Kegiatan

-   Akademik.
-   Kedisiplinan.
-   Bimbingan konseling.
-   Kegiatan siswa.
-   Pembatasan akses sensitif.
-   Laporan dan rekap.

## Fase 6 -- QA, Optimasi, dan Produksi

-   Pengujian regresi.
-   Pengujian keamanan.
-   Uji beban.
-   Optimasi query dan index.
-   Optimasi aset.
-   Backup/restore.
-   Deployment cPanel.
-   Smoke test.
-   Dokumentasi admin dan pengguna.
-   Pelatihan/panduan penggunaan.

------------------------------------------------------------------------

# 15. Cara Kerja Antigravity

1.  Mulai dengan memeriksa isi folder proyek. Jangan menimpa proyek yang
    sudah ada tanpa memahami isinya.
2.  Jika folder kosong, inisialisasi proyek baru. Jika proyek sudah ada,
    audit dahulu.
3.  Buat file dokumentasi fase 0.
4.  Laporkan temuan dan keputusan teknologi sebelum implementasi besar.
5.  Instal dependency stabil yang kompatibel.
6.  Simpan lockfile Composer dan npm.
7.  Implementasikan fase satu per satu.
8.  Setelah setiap fitur, jalankan migration, test, dan pemeriksaan log.
9.  Perbaiki error sebelum lanjut.
10. Jangan membuat data contoh menyerupai data nyata.
11. Jangan mengubah stack tanpa alasan dan persetujuan.
12. Jangan menambahkan package hanya karena populer; jelaskan manfaat,
    ukuran, kompatibilitas, dan biaya pemeliharaan.
13. Setiap perubahan schema harus melalui migration.
14. Jangan menghapus data pengguna dalam proses update.
15. Perbarui dokumentasi ketika ada perubahan desain.

## Format laporan setiap fase

Setelah menyelesaikan satu fase, laporkan:

-   Fitur yang selesai.
-   File penting yang dibuat/diubah.
-   Migration dan tabel baru.
-   Dependency baru dan alasannya.
-   Hasil test.
-   Error yang ditemukan dan penyelesaiannya.
-   Risiko atau pekerjaan yang belum selesai.
-   Cara menjalankan dan menguji fitur.
-   Status kesiapan untuk lanjut ke fase berikutnya.

------------------------------------------------------------------------

# 16. Tugas Pertama yang Harus Dikerjakan

**Jangan langsung membangun seluruh aplikasi.**

Kerjakan urutan berikut:

1.  Audit lingkungan Laragon dan folder proyek.
2.  Pastikan PHP 8.4.12 aktif.
3.  Pastikan MySQL lokal dapat diakses.
4.  Periksa Composer, Node.js, npm, dan ekstensi PHP.
5.  Verifikasi persyaratan Laravel 13 dan Livewire 4 dari
    dokumentasi/package metadata yang aktual.
6.  Buat seluruh dokumentasi Fase 0.
7.  Buat rancangan database dan relasi.
8.  Buat daftar keputusan yang masih membutuhkan informasi dari pihak
    madrasah atau penyedia hosting.
9.  Berikan ringkasan hasil audit dan rencana Fase 1.
10. Setelah pemeriksaan selesai, implementasikan Fase 1 saja.

**Jangan melanjutkan ke Fase 2 sebelum Fase 1 diuji dan dinyatakan
selesai.**

------------------------------------------------------------------------

# 17. Catatan Keputusan yang Masih Terbuka

Jangan menebak hal-hal berikut. Buat daftar pertanyaan dan gunakan
konfigurasi sementara yang dapat diubah:

-   Nama resmi madrasah dan identitas visual.
-   Struktur organisasi dan role final.
-   Tahun ajaran dan kalender akademik.
-   Struktur kelas dan penamaan rombel.
-   Metode absensi sekolah yang akan dipilih.
-   Aturan resmi Zuhur per gelombang.
-   Aturan kelayakan siswa untuk Asar.
-   Petugas yang berwenang memverifikasi ibadah.
-   Kebijakan komunikasi/notifikasi orang tua.
-   Kebijakan retensi data.
-   Detail versi MySQL, ekstensi PHP, dan batas resource hosting.
-   Document root dan akses SSH/Composer di cPanel.

Gunakan data dummy untuk development. Jangan memasukkan data pribadi
siswa nyata sebelum kontrol keamanan dan persetujuan penggunaan sistem
siap.

------------------------------------------------------------------------

# 18. Pernyataan Akhir

MADRASAH ONE harus dibangun sebagai aplikasi yang **andal, ringan, aman,
mudah digunakan, mudah dipelihara, dan realistis untuk shared hosting
cPanel**.

Keberhasilan proyek tidak diukur dari banyaknya teknologi yang
digunakan, tetapi dari:

-   Kesesuaian proses dengan kebutuhan madrasah.
-   Ketepatan dan konsistensi data.
-   Kecepatan penggunaan pada perangkat dan jaringan umum.
-   Keamanan serta kerahasiaan data siswa.
-   Kemudahan guru dan orang tua.
-   Stabilitas aplikasi pada resource hosting yang tersedia.
-   Kemudahan backup, pemulihan, dan pemeliharaan.

**Mulai dari Fase 0. Audit dahulu, dokumentasikan, verifikasi
kompatibilitas, kemudian bangun Fase 1.**
