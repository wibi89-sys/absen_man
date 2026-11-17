# Panduan Keamanan & Privasi (SECURITY_GUIDELINES.md)
**MADRASAH ONE -- Integrated Student Monitoring System**  
**Versi:** 1.0  
**Tanggal:** 16 November 2026  

---

## 1. Pemodelan Ancaman (Threat Modeling)

MADRASAH ONE mengelola data vital siswa, data kehadiran berkonsekuensi disiplin, serta catatan bimbingan konseling yang bersifat rahasia. Berikut adalah pemodelan ancaman utama dan mitigasinya:

| Aset Data / Alur Kerja | Ancaman Potensial | Dampak Risiko | Mitigasi Wajib Antigravity |
| :--- | :--- | :--- | :--- |
| **Token QR Ibadah** | Tangkapan layar (*screenshot*) dibagikan ke siswa yang tidak hadir (titip absen). | Manipulasi data kehadiran ibadah. | QR dinamis berputar tiap 15--30 detik dengan token bertanda tangan waktu (*timestamp-bound*). Pemindaian dicocokkan dengan status sesi aktif dan dibatasi 1 scan per siswa per sesi. |
| **Catatan Konseling (BK)**| Akses tidak sah oleh guru lain, staf, atau pihak luar. | Pelanggaran privasi berat, trauma psikologis siswa. | Otorisasi ketat berbasis Policy; isolasi domain query; field latar belakang kasus dienkripsi (*database-level encryption*); audit logging setiap akses buka data. |
| **Pengecualian Ibadah Sensitif** | Status halangan syar'i siswi terekspos di layar umum atau portal orang tua lain. | Perundungan (*bullying*), rasa malu siswa. | Tampilan publik hanya menampilkan label generik "Dispensasi". Detail alasan disembunyikan total dari dashboard guru umum dan wali kelas non-petugas keagamaan. |
| **Portal Orang Tua** | Akun orang tua mengakses rekam jejak siswa yang bukan anaknya (*IDOR attack*). | Kebocoran data pribadi anak lain. | Pengecekan relasi `student_parent` berstatus `is_verified=true` pada setiap query database. Tidak ada parameter URL telanjang yang dapat ditebak (`/parents/students/{id}`). |
| **Dokumen Unggahan** | Unggahan script jahat (webshell `.php`) yang disamarkan sebagai foto surat izin dokter. | Kompromi penuh server hosting cPanel. | Validasi MIME type asli di server menggunakan `fileinfo`, batasan ukuran file maksimum (maks 2MB), penggantian nama file menjadi acak, dan penyimpanan di direktori privat non-web (`storage/app/private`). |
| **Brute Force & DoS** | Percobaan tebak kata sandi massal pada akun siswa/guru. | Akun dibajak, beban CPU hosting melonjak. | Rate limiting ketat pada endpoint login (`throttle:5,1`), proteksi CSRF aktif di seluruh form, dan penguncian sementara akun jika gagal berkali-kali. |

---

## 2. Standar Teknis Keamanan Aplikasi

1. **Autentikasi & Hashing Kata Sandi:**
   - Semua kata sandi di-hash menggunakan algoritma bawaan Laravel (Bcrypt dengan work factor >= 12 atau Argon2id).
   - Panjang kata sandi minimum 8 karakter dengan kombinasi angka dan huruf.
2. **Proteksi Sesi:**
   - Sesi disimpan dalam database (`SESSION_DRIVER=database`).
   - Cookie sesi dikonfigurasi dengan atribut: `HttpOnly = true`, `SameSite = lax/strict`, dan `Secure = true` saat di lingkungan produksi HTTPS.
   - Sesi pengguna otomatis kadaluarsa setelah periode *idle* yang ditentukan (`SESSION_LIFETIME=120`).
3. **Penyimpanan Berkas Privat:**
   - Foto surat sakit, lampiran bukti izin, dan dokumen konseling disimpan di folder `storage/app/private`.
   - Berkas tidak dapat diakses langsung melalui URL publik browser.
   - Berkas dilayani melalui *secure download controller* yang memeriksa kewenangan pengguna melalui Laravel Policy sebelum mengalirkan file (`response()->download()`).
4. **Pencegahan SQL Injection & XSS:**
   - Penggunaan ORM Eloquent dan PDO parameter binding untuk seluruh interaksi basis data.
   - Tidak menggunakan *raw query* tanpa sanitasi parameter binding yang valid.
   - Escape otomatis karakter khusus HTML pada seluruh template Blade (`{{ $data }}`).
5. **Kebijakan Lingkungan Produksi:**
   - Nilai `APP_DEBUG=false` mutlak pada hosting produksi.
   - File konfigurasi rahasia `.env` tidak pernah disertakan ke dalam repositori Git (`.gitignore`).
   - Direktori `public_html` atau root web hanya mengekspos folder `public/` Laravel.

---

## 3. Penanganan Audit Trail

Sistem mencatat rekam jejak digital (`audit_logs`) untuk setiap:
- Perubahan status absensi siswa (koreksi kehadiran dari alpa ke hadir/izin).
- Pembuatan dan verifikasi sesi monitoring ibadah.
- Pendaftaran atau perubahan dispensasi ibadah sensitif.
- Pembukaan rekam konseling oleh guru BK.
- Perubahan hak akses atau pembuatan akun admin baru.
- Ekspor data massal ke format PDF atau Excel.

Log audit bersifat *append-only* dan tidak dapat diedit atau dihapus oleh pengguna biasa maupun admin madrasah.
