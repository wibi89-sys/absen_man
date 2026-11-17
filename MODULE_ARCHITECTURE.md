# Arsitektur Modul Aplikasi (MODULE_ARCHITECTURE.md)
**MADRASAH ONE -- Integrated Student Monitoring System**  
**Versi:** 1.0  
**Tanggal:** 16 November 2026  

---

## 1. Pola Arsitektur: Modular Monolith

MADRASAH ONE mengadopsi pola **Modular Monolith** di dalam struktur framework Laravel. Pendekatan ini memisahkan logika domain bisnis ke dalam modul mandiri namun tetap berada dalam satu basis kode dan satu basis data relasional.

### Struktur Direktori yang Diterapkan:

```text
app/
├── Domain/
│   ├── Identity/               # Autentikasi, Akun Pengguna, RBAC, Profil
│   │   ├── Actions/
│   │   ├── Models/
│   │   ├── Policies/
│   │   └── Services/
│   ├── MasterData/             # Tahun Ajaran, Kelas, Siswa, Guru, Orang Tua, Lokasi
│   ├── Attendance/             # Absensi Gerbang/Harian, Piket, Koreksi
│   ├── Worship/                # Ibadah Zuhur (Gelombang), Asar, QR Dinamis, Verifikasi
│   ├── Academic/               # Mata Pelajaran, Jadwal Belajar, Nilai/Capaian
│   ├── Discipline/             # Poin Pelanggaran, Prestasi, Tindak Lanjut
│   ├── Counseling/             # Bimbingan Konseling (BK) dengan Isolasi Ketat
│   ├── Activities/             # Ekstrakurikuler, Organisasi Siswa
│   ├── ParentPortal/           # Ringkasan Khusus Anak Terverifikasi, Pengumuman
│   └── Reporting/              # Generator Rekapitulasi & Ekspor Laporan
├── Http/
│   ├── Controllers/            # Penanganan routing HTTP standar
│   ├── Livewire/               # Komponen reaktif Livewire per modul
│   ├── Middleware/             # Otentikasi, Cek Role, Single Session, dsb.
│   └── Requests/               # Form Request validasi input server-side
├── Support/                    # Helper umum, Nilai Konfigurasi, Enums, Traits
└── Jobs/                       # Background jobs untuk cron & antrean database
```

---

## 2. Batasan Tanggung Jawab Antar-Modul (Module Boundaries)

| Modul Domain | Tanggung Jawab Utama | Ketergantungan Eksternal |
| :--- | :--- | :--- |
| **Identity** | Menangani otentikasi (login/logout), hashing, penugasan peran (RBAC), dan pencatatan audit log pengguna. | Dasar bagi seluruh modul |
| **MasterData** | Mengelola entitas dasar madrasah (Tahun Ajaran, Rombel/Kelas, Siswa, Guru, Relasi Orang Tua). Menjamin integritas relasional data siswa. | `Identity` |
| **Attendance** | Menangani pencatatan absensi harian sekolah oleh guru piket / wali kelas, mendeteksi keterlambatan, dan mencatat riwayat koreksi status. | `MasterData`, `Identity` |
| **Worship** | Menghitung gelombang Zuhur, menentukan kelayakan Asar, menerbitkan token QR dinamis per sesi, mencatat bukti scan, dan memfasilitasi verifikasi bertingkat. | `MasterData`, `Activities`, `Identity` |
| **Activities** | Mencatat keikutsertaan siswa dalam ekskul/organisasi. Menjadi data masukan bagi Modul `Worship` untuk menentukan kelayakan peserta salat Asar berjamaah. | `MasterData` |
| **Discipline** | Menghitung poin pembinaan pelanggaran & apresiasi prestasi siswa. | `MasterData`, `Identity` |
| **Counseling (BK)**| **TERISOLASI:** Pengelolaan rekam sesi konseling pribadi dan catatan rahasia siswa. Hanya dapat diakses oleh Guru BK dan Kepala Madrasah atas izin resmi. | `MasterData`, `Identity` |
| **ParentPortal** | Menyajikan data agregat anak yang telah diverifikasi (kehadiran final, pengumuman, apresiasi). Mengisolasi data siswa lain dan catatan internal madrasah. | `Attendance`, `Worship`, `MasterData` |
| **Reporting** | Agregasi data statistik untuk Kepala Madrasah dan ekspor rekap berkala dalam format cetak/spreadsheet. | Seluruh domain (sesuai otorisasi) |

---

## 3. Aturan Enkapsulasi & Batasan Data Sensitif

1. **Isolasi Catatan Bimbingan Konseling (BK):**
   - Catatan konseling tidak pernah digabungkan ke dalam query global atau relasi eager-loading umum (`Student::with('counseling')`).
   - Akses data konseling diwajibkan melewati `CounselingPolicy` yang secara eksplisit memverifikasi role `guru_bk`.
   - Modul `ParentPortal` dan dashboard guru mata pelajaran sama sekali tidak memiliki route atau komponen ke data konseling.

2. **Perlindungan Dispensasi Ibadah Sensitif (Halangan Syar'i / Kondisi Khusus):**
   - Siswi yang berhalangan syar'i memiliki status `exempt` pada sesi ibadah.
   - Pada rekap publik atau dashboard umum, siswi tersebut hanya tercatat sebagai "Dispensasi/Tercatat" tanpa menampilkan deskripsi medis atau alasan biologis pribadi.
   - Detail alasan hanya dapat dilihat oleh Pembina/Petugas Keagamaan Putri dan Guru BK.

3. **Verifikasi Relasi Akun Orang Tua:**
   - Orang tua tidak dapat langsung melihat data siswa hanya berdasarkan nama yang sama.
   - Relasi harus ditandai `is_verified = true` di tabel `student_parent` oleh staf tata usaha atau admin madrasah setelah mencocokkan dokumen Kartu Keluarga (KK).

---

## 4. Pola Implementasi Teknis (Clean Architecture)

- **Controller Ramping:** Controller atau Komponen Livewire hanya bertugas menerima request, memanggil Form Request / Authorization Policy, dan mendelegasikan proses bisnis ke **Action / Service Class**.
- **Database Transaction:** Semua operasi tulis yang melibatkan lebih dari satu tabel (contoh: pembuatan sesi ibadah sekaligus ratusan entri peserta sesi) wajib dibungkus dalam `DB::transaction()`.
- **Stateless QR Verification:** Pemindaian QR diverifikasi secara real-time terhadap token aktif di database dengan masa berlaku singkat (misalnya 15-30 detik) dan dicek terhadap duplikasi scan peserta.
