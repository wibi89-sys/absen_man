# Keputusan Tumpukan Teknologi (TECHNOLOGY_STACK.md)
**MADRASAH ONE -- Integrated Student Monitoring System**  
**Versi:** 1.0  
**Tanggal:** 16 November 2026  

---

## 1. Matriks Tumpukan Teknologi Terpilih

| Lapisan Arsitektur | Teknologi | Versi Terverifikasi | Alasan Pemilihan & Kesesuaian Shared Hosting |
| :--- | :--- | :--- | :--- |
| **Backend Framework** | Laravel | **v13.42.0** | Framework PHP modern dengan keamanan tingkat tinggi, migration terstruktur, ORM Eloquent, Form Request, Policy, serta kompatibilitas penuh dengan ekosistem shared hosting. |
| **PHP Runtime Lokal** | PHP CLI (Laragon) | **8.4.12 NTS x64** | Sesuai dengan konfigurasi default hosting target cPanel yang mendukung PHP 8.4. |
| **PHP Runtime Hosting** | cPanel MultiPHP | **PHP 8.4** | Performa JIT & optimasi memori PHP 8.4 memberikan response time cepat pada shared hosting berdaya sedang. |
| **Database Server** | MySQL | **8.4.3** | Standar database relasional industri dengan dukungan ACID transaction, JSON column, window functions, generated columns, dan index performa tinggi. |
| **Frontend SSR Engine** | Blade Templates | Bawaan Laravel 13 | Rendering cepat langsung dari server tanpa overhead rehidrasi JavaScript yang berat, hemat bandwidth untuk perangkat seluler guru dan orang tua. |
| **Frontend Dynamic Layer**| Livewire | **v4.4.8** | Menyediakan interaktivitas reaktif (seperti pencarian real-time, validasi inline, polling QR token) tanpa memerlukan SPA framework terpisah (React/Vue). |
| **CSS Framework** | Tailwind CSS | **v4.x** | Utility-first styling modern, menghasilkan file CSS statis yang sangat kecil (<50KB gzip) setelah di-compile. |
| **Client-Side Scripting** | Alpine.js | **v3.x** | Terintegrasi erat dengan Livewire untuk menangani interaksi lokal seperti dropdown, modal, dan scanner kamera QR di browser. |
| **Asset Bundler** | Vite | **Bawaan Laravel** | Kompilasi aset super cepat saat pengembangan lokal. Aset hasil build (`public/build`) langsung diunggah ke cPanel tanpa butuh Node.js di server. |
| **Queue Management** | Database Queue | Bawaan Laravel | Antrean job asynchronous diproses menggunakan tabel database (`jobs`) yang dipicu berkala melalui cron job, tanpa perlu Supervisor daemon. |
| **Cache Driver** | Database / File Cache | Bawaan Laravel | Caching query statistik, data referensi kelas, dan token QR tanpa membutuhkan daemon Redis di shared hosting. |
| **Session Driver** | Database Session | Bawaan Laravel | Memudahkan inspeksi sesi aktif pengguna, proteksi session hijacking, dan fitur single-session login. |
| **Scheduler** | Laravel Scheduler | Bawaan Laravel | Seluruh tugas otomatis (pembuatan sesi harian, kadaluarsa token, agregasi rekap) diatur lewat 1 baris cron job di cPanel. |

---

## 2. Prinsip "Zero External Persistent Process" di Produksi

Untuk memastikan MADRASAH ONE dapat berjalan tanpa hambatan pada paket shared hosting cPanel biasa yang memiliki batas *process limit* dan *no-root access*, sistem **secara ketat mengecualikan** dependensi berikut pada lingkungan produksi:

1. **Tanpa Redis / Memcached:**
   Semua caching dan queue memanfaatkan tabel database MySQL yang telah diindeks secara tepat.
2. **Tanpa Node.js Persistent Daemon / PM2 / Next.js:**
   Node.js hanya diinstal di komputer pengembang lokal untuk menjalankan `npm run build`. Server produksi cPanel hanya menyajikan file statis `public/build/manifest.json`, `.css`, dan `.js`.
3. **Tanpa WebSocket Server Terpisah (Soketi / Laravel Reverb):**
   Interaksi reaktif seperti pembaruan log pemindaian QR menggunakan interval polling cerdas Livewire (misalnya `wire:poll.5s` hanya pada halaman sesi aktif pengawas) sehingga menghemat CPU hosting dan kuota proses.
4. **Tanpa Supervisor / Worker Daemon Terus Menerus:**
   Pemrosesan queue dapat berjalan secara sinkron (`sync`) untuk aksi langsung atau melalui perintah artisan singkat yang dijadwalkan via Cron Job (`php artisan queue:work --stop-when-empty`).
5. **Tanpa Ketergantungan Layanan Cloud Berbayar Wajib:**
   Aplikasi inti berjalan mandiri (self-contained) tanpa wajib berlangganan Firebase, Pusher, AWS S3, atau API berbayar lainnya.

---

## 3. Strategi Pengelolaan Dependensi

- Seluruh pustaka Composer didefinisikan secara eksplisit di `composer.json` dan dikunci versinya di `composer.lock`.
- Seluruh paket frontend dikunci versinya di `package.json` dan `package-lock.json`.
- Tidak ada instalasi paket uji coba tanpa justifikasi teknis dan analisis dampak memori di lingkungan shared hosting.
