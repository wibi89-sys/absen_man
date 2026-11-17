# Dokumen Spesifikasi Sistem (SYSTEM.md)
**MADRASAH ONE -- Integrated Student Monitoring System**  
**Versi:** 1.0  
**Tanggal:** 16 November 2026  

---

## 1. Ikhtisar Sistem

**MADRASAH ONE** adalah platform terpadu pemantauan dan pengelolaan aktivitas siswa yang dirancang khusus untuk Madrasah Aliyah (MA) dengan skala 300 hingga 1.000 siswa. Sistem ini menjembatani seluruh pilar madrasah:
- Pengelolaan data akademik dan kesiswaan.
- Pencatatan absensi harian sekolah yang akurat dan dapat diverifikasi.
- Pemantauan komprehensif ibadah wajib berjamaah (Zuhur bergelombang & Asar berbasis kelayakan kegiatan).
- Pencatatan kedisiplinan dan poin pembinaan siswa.
- Sistem bimbingan dan konseling (BK) dengan privasi tingkat tinggi.
- Manajemen kegiatan ekstrakurikuler & organisasi siswa.
- Portal transparansi informasi terverifikasi bagi orang tua/wali murid.
- Executive dashboard & pelaporan untuk Kepala Madrasah dan pemangku kebijakan.

---

## 2. Prinsip Arsitektur Utama

1. **Modular Monolith:**
   Satu repositori Laravel yang bersih dan terstruktur secara modular berdasarkan domain bisnis, menghindari fragmentasi microservices atau backend terpisah yang membebani shared hosting.
2. **Kesesuaian Shared Hosting cPanel:**
   Arsitektur dirancang agar dapat beroperasi secara optimal dan stabil pada shared hosting cPanel biasa tanpa memerlukan VPS, Redis, Supervisor daemon, atau persistent Node.js runtime.
3. **Validasi Multi-Lapisan (Defense in Depth):**
   Tidak ada keputusan kehadiran atau akses data yang hanya bergantung pada frontend. Semua validasi, otorisasi peran (RBAC), batas sesi, dan audit logging ditegakkan di backend Laravel.
4. **Alur Ibadah Non-Penghakiman & Privasi Sensitif:**
   Pemantauan ibadah bertujuan untuk pembinaan dan pendataan, bukan penghakiman moral. Kondisi khusus dan alasan dispensasi/pengecualian (seperti halangan syar'i/haid atau sakit tertentu) diisolasi dengan kontrol otorisasi ketat, tidak pernah dipublikasikan di dashboard umum, rekap kelas, maupun portal orang tua umum.
5. **Daftar Peserta Deterministik:**
   Setiap sesi absensi atau monitoring ibadah mengunci seluruh daftar siswa yang eligible sebagai peserta (`worship_session_participants`) saat sesi dibuat. Siswa yang belum memindai kode QR tetap terhitung dalam denominator statistik sebagai status `pending_verification` atau `absent` setelah batas waktu, bukan hilang dari rekap.
6. **QR Scan sebagai Bukti Awal:**
   Pemindaian QR Dinamis hanya berfungsi sebagai pencatatan bukti log awal (audit trail). Penentuan status akhir tetap melalui verifikasi wali kelas dan finalisasi pembina/petugas keagamaan.

---

## 3. Profil Pengguna dan Hak Akses (RBAC)

Sistem membedakan peran dan hak akses secara granular:

| Peran (Role) | Ruang Lingkup & Kewenangan |
| :--- | :--- |
| **Super Admin** | Konfigurasi sistem penuh, audit log master, manajemen hak akses, pemeliharaan teknis. |
| **Admin Madrasah** | Operasional harian data master, kalender akademik, jadwal pelajaran, rombel, akun guru & siswa. |
| **Kepala Madrasah** | Executive dashboard, statistik tren kehadiran, ringkasan capaian ibadah, persetujuan laporan. |
| **Wakil Kepala (Waka)** | Akses data sesuai bidang tugas: Waka Kurikulum (akademik), Waka Kesiswaan (disiplin, kegiatan, ibadah). |
| **Guru Mata Pelajaran** | Pencatatan jurnal mengajar, kehadiran kelas yang diajar, input nilai tugas & asesmen. |
| **Wali Kelas** | Pemantauan penuh siswa di rombel binaannya, konfirmasi absensi kelas & kehadiran ibadah gelombang rombelnya. |
| **Petugas Keagamaan** | Manajemen gelombang Zuhur, aturan kelayakan Asar, generate QR dinamis, finalisasi verifikasi ibadah. |
| **Guru Piket** | Absensi gerbang masuk harian, pencatatan keterlambatan, izin meninggalkan sekolah di jam pelajaran. |
| **Guru BK** | Pengelolaan catatan konseling siswa, penanganan tindak lanjut, riwayat sesi konseling rahasia. |
| **Siswa** | Jadwal belajar, riwayat kehadiran pribadi, pemindaian QR sesi ibadah, pengumuman madrasah. |
| **Orang Tua / Wali** | Portal khusus memantau hanya anak kandung/binaan yang telah terhubung dan diverifikasi secara sah. |

---

## 4. Batasan Ruang Lingkup Sistem

- Aplikasi tidak menggantikan sistem pendataan nasional resmi pemerintah (seperti EMIS atau SIMPATIKA), melainkan menjadi sistem pemantauan operasional internal madrasah.
- Fitur notifikasi tahap awal difokuskan pada notifikasi in-app (internal web notification). Pengiriman kanal eksternal seperti WhatsApp Gateway atau SMTP Email bersifat modular dan hanya diaktifkan jika pihak madrasah menyediakan konfigurasi resmi.
