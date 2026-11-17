# Panduan Operasional: Guru, Wali Kelas & Guru Piket
## Aplikasi MADRASAH ONE - Sistem Terpadu Manajemen Madrasah Aliyah

---

### 1. Peran & Ruang Lingkup
- **Guru Piket:** Bertanggung jawab atas pencatatan presensi masuk/harian sekolah untuk seluruh rombel serta pengawasan ketertiban madrasah.
- **Wali Kelas:** Bertanggung jawab atas presensi rombel binaan, pemantauan kepatuhan salat siswa, penanganan pelanggaran/prestasi ringan, dan komunikasi langsung dengan orang tua terverifikasi.
- **Guru Mata Pelajaran:** Bertanggung jawab atas penginputan nilai tugas, kuis, dan ujian formatif/sumatif pada mata pelajaran yang diampu.

---

### 2. Lembar Presensi Cepat Rombel (Harian)

1. Akses menu: **Presensi > Lembar Presensi Cepat** (`/attendance/sheet`).
2. Pilih rombel yang akan diabsen (Wali Kelas otomatis diarahkan ke rombel binaannya).
3. Pilih Jenis Presensi (contoh: *Presensi Masuk Pagi*).
4. Gunakan fitur cepat Alpine.js:
   - Klik tombol hijau **Tandai Semua Hadir** untuk mengisi status seluruh siswa menjadi *Hadir* secara instan.
   - Ubah status siswa tertentu yang tidak hadir menjadi *Terlambat*, *Sakit*, *Izin*, atau *Alpa*.
   - Masukkan menit keterlambatan (jika terlambat) dan catatan keterangan (jika ada).
5. Klik **Simpan Presensi Rombel**.
6. Seluruh data tersimpan secara transaksional (DB Transaction).

---

### 3. Koreksi Presensi (Audit Wajib)

Jika terdapat siswa yang semula tercatat Alpa namun ternyata membawa surat dokter atau hadir terlambat:
1. Akses menu: **Presensi > Jurnal Kehadiran** (`/attendance`).
2. Cari nama siswa atau filter berdasarkan tanggal dan rombel.
3. Klik tombol **Koreksi**.
4. Pilih status baru dan **WAJIB** mengisi kolom alasan koreksi (misal: *"Surat dokter susulan diserahkan orang tua"*).
   > [!IMPORTANT]
   > Sistem menolak koreksi tanpa alasan tertulis. Riwayat koreksi akan dicatat ke dalam tabel `attendance_corrections` untuk audit pengawas.

---

### 4. Penilaian Akademik & Publikasi Nilai

1. Akses menu: **Akademik > Penilaian Siswa** (`/academic/grades`).
2. Pilih kelas dan mata pelajaran yang diampu.
3. Masukkan **Nama Asesmen** (misal: *PH-1 Al-Qur'an Hadis*, *Tugas Fikih Mawaris*, *PTS Ganjil*).
4. Isikan nilai skala 0–100 pada masing-masing baris siswa.
5. Klik **Simpan Nilai Asesmen**.
6. Gunakan tombol **Publikasikan Nilai** agar nilai dapat dilihat oleh orang tua di portal resmi. Nilai berstatus draft tidak akan tampil di portal wali murid.

---

### 5. Catatan Kedisiplinan & Poin Pelanggaran/Prestasi

1. Akses menu: **Kedisiplinan** (`/discipline`).
2. Klik **Catat Kejadian Baru**.
3. Pilih siswa, kategori kedisiplinan, tanggal kejadian, deskripsi peristiwa, dan tindakan awal yang diambil.
4. Sistem otomatis mengkalkulasi poin pelanggaran (+) atau poin prestasi (-) pada profil siswa.
5. Status penanganan dapat dialihkan dari `dilaporkan` -> `diproses` -> `selesai`.

---

### 6. Komunikasi Terarah ke Orang Tua Siswa

Wali kelas dapat berkomunikasi langsung secara resmi tanpa perlu membagikan nomor WhatsApp pribadi:
1. Akses menu: **Pesan Wali Kelas > Tulis Pesan** (`/communication/messages/create`).
2. Pilih siswa binaan yang ingin dihubungi (daftar orang tua terverifikasi akan terpilih otomatis).
3. Masukkan subjek dan isi pesan (contoh: *Pemberitahuan Konsultasi Keterlambatan Berulang*).
4. Klik **Kirim Pesan**.
5. Orang tua akan menerima pesan pada portal mereka, dan sistem mencatat waktu pesan dibaca (`read_at`).
