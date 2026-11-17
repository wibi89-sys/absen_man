# Panduan Operasional: Petugas Keagamaan & Pembina Ibadah
## Aplikasi MADRASAH ONE - Sistem Terpadu Monitoring Ibadah Terpadu

---

### 1. Prinsip Utama Sistem Monitoring Ibadah
Sistem Monitoring Ibadah MADRASAH ONE dibangun dengan 4 pilar arsitektur:
1. **Non-Punitive Logging:** Pencatatan ibadah bertujuan untuk pembinaan spiritual, bimbingan akhlak, dan pendampingan, bukan untuk penghukuman siswa.
2. **Deterministic Participant Denominator:** Penyebut (total siswa wajib salat) dikunci secara deterministik saat sesi ibadah dibuat. Siswa yang tidak scan otomatis menjadi 'Tidak Hadir', menjamin keakuratan rasio kehadiran.
3. **Multi-Tier Verification:** Scan kamera / barcode hanyalah bukti awal (`pending_verification`), yang kemudian diverifikasi oleh Wali Kelas / Petugas Keagamaan.
4. **Kerahasiaan Halangan Syar'i Siswi:** Siswi yang sedang berhalangan syar'i (haid/nifas) atau dispensasi medis dicatat secara rahasia dan tidak pernah dibuka alasannya ke publik atau guru non-terkait.

---

### 2. Pembuatan Sesi Ibadah Berjamaah

1. Akses menu: **Ibadah > Sesi Ibadah** (`/worship/sessions`).
2. Klik **Buat Sesi Ibadah Baru** (`/worship/sessions/create`).
3. Pilih **Jenis Ibadah**:
   - **Salat Zuhur Berjamaah:** Dapat dibagi menjadi Gelombang 1 dan Gelombang 2 sesuai kapasitas masjid madrasah.
   - **Salat Asar Berjamaah:** Menggunakan denominator siswa yang memiliki kegiatan sore / ekstrakurikuler.
4. Pilih rombel/kelas yang dijadwalkan pada gelombang ini.
5. Tentukan lokasi masjid/musala.
6. Klik **Simpan & Kunci Peserta Sesi**.
   > [!NOTE]
   > Sistem otomatis mengunci daftar siswa aktif dari kelas-kelas terpilih ke tabel `worship_session_participants`. Siswi yang sudah terdaftar dalam tabel dispensasi aktif pada tanggal tersebut akan otomatis ditandai `exempt` (bebas kewajiban) sehingga tidak dihitung sebagai peserta yang harus scan.

---

### 3. Pengoperasian Layar QR Proyektor Masjid

1. Buka tautan sesi ibadah, lalu klik tombol **Tampilkan QR Proyektor** (`/worship/sessions/{session}/display-qr`).
2. Tampilkan halaman ini pada layar monitor besar atau proyektor masjid.
3. Karakteristik Layar Proyektor:
   - Menampilkan QR Code kriptografis (HMAC SHA-256) yang **berotasi secara otomatis setiap 20–30 detik**.
   - Dilengkapi garis hitung mundur (progress countdown bar) sebelum kode berganti.
   - Fitur **Anti-Replay & Anti-Screenshot**: Kode yang difoto dari ponsel lain atau dikirim via chat akan otomatis kedaluwarsa dalam 30 detik.
   - Menampilkan indikator langsung jumlah siswa yang telah berhasil memindai.

---

### 4. Pemindaian Siswa & Fallback Petugas Keagamaan

#### A. Pemindaian Mandiri oleh Siswa:
- Siswa membuka menu **Scan Kehadiran Ibadah** pada ponsel mereka.
- Mengarahkan kamera ponsel ke arah layar proyektor masjid.
- Status siswa berubah seketika menjadi `pending_verification` atau `present`.

#### B. Fallback Input Petugas / Barcode Scanner (Bagi Siswa Tanpa Ponsel):
Bagi madrasah yang membatasi penggunaan gawai pada siswa atau bagi siswa yang kehabisan baterai:
1. Petugas membuka menu **Scan / Verifikasi Sesi** (`/worship/sessions/{session}/scan`).
2. Petugas mengaktifkan mode **Input NIS / Handheld Barcode Scanner**.
3. Arahkan barcode scanner kartu tanda pelajar (KTM) atau ketik NIS siswa dan tekan Enter.
4. Sistem mencatat kehadiran siswa dengan sumber `manual_officer_entry`.

---

### 5. Pengelolaan Dispensasi Syar'i & Medis (Kerahasiaan Mutlak)

Modul ini **HANYA DAPAT DIAKSES** oleh: `petugas_keagamaan`, `guru_bk`, `super_admin`, dan `admin`:
1. Akses menu: **Ibadah > Dispensasi Syar'i** (`/worship/exemptions`).
2. Klik **Tambah Dispensasi**.
3. Pilih nama siswi, tentukan tanggal mulai dan tanggal selesai (standar: 5–7 hari).
4. Pilih kategori: `haid`, `sakit_fisik`, `tugas_luar`, `lainnya`.
5. Isikan catatan medis atau keterangan khusus.
   > [!IMPORTANT]
   > Pada laporan rekapitulasi umum dan portal orang tua, alasan rinci siswi **DITUTUP SECARA PERMANEN** dan hanya berlabel netral: `"Dispensasi"`.

---

### 6. Verifikasi & Finalisasi Sesi Ibadah

Setelah waktu salat dan zikir selesai:
1. Buka halaman detail sesi ibadah (`/worship/sessions/{session}`).
2. Periksa daftar siswa yang telah scan. Petugas dapat menyetujui secara massal dengan tombol **Verifikasi Semua Bukti Scan**.
3. Klik tombol merah **Finalisasi Sesi Ibadah**.
4. Sistem akan secara otomatis:
   - Mengubah seluruh siswa yang belum melakukan scan menjadi berstatus **Tidak Hadir** (`absent`).
   - Mengunci sesi menjadi `completed` / `verified`.
   - Mengirim ringkasan kehadiran ke rekapitulasi sekolah.

---

### 7. Rekapitulasi & Ekspor Laporan
1. Akses menu: **Ibadah > Rekapitulasi Ibadah** (`/worship/recap`).
2. Filter berdasarkan rentang tanggal, rombel, dan jenis salat (Zuhur/Asar).
3. Klik tombol **Ekspor CSV** untuk mencetak laporan resmi evaluasi keagamaan madrasah.
