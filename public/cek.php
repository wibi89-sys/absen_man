<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (! file_exists(__DIR__.'/../vendor/autoload.php')) {
    echo "<div style='font-family:-apple-system,BlinkMacSystemFont,sans-serif;padding:30px;max-width:850px;margin:auto;background:#0f172a;color:#f8fafc;border-radius:20px;margin-top:20px;'>";
    echo "<h2 style='color:#ef4444;'>❌ Folder vendor/ Belum Ditemukan!</h2>";
    echo "<p style='color:#94a3b8;'>File <code>vendor/autoload.php</code> tidak ditemukan di server. Pastikan Anda mengunggah folder vendor dari proyek <b>absen_man</b> (Madrasah One), bukan dari proyek lain.</p>";
    echo "<p style='color:#cbd5e1;'>File ZIP siap upload: <b>vendor-absen-man.zip</b></p>";
    echo "</div>";
    exit;
}

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// Daftarkan objek HTTP Request ke dalam container Laravel
$request = Request::capture();
$app->instance('request', $request);

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

// Pastikan APP_KEY terpasang
if (! config('app.key')) {
    $envPath = __DIR__.'/../.env';
    if (file_exists($envPath)) {
        file_put_contents($envPath, "\nAPP_KEY=base64:G7zIMCo4Bdyi50Gx5vLA2lwl3VxEvZOp9NxCjR2mJDg=\n", FILE_APPEND);
        config(['app.key' => 'base64:G7zIMCo4Bdyi50Gx5vLA2lwl3VxEvZOp9NxCjR2mJDg=']);
    }
}

echo "<div style='font-family:-apple-system,BlinkMacSystemFont,sans-serif;padding:30px;max-width:850px;margin:auto;background:#0f172a;color:#f8fafc;border-radius:20px;margin-top:20px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.5);'>";
echo "<h1 style='color:#10b981;margin-top:0;'>MADRASAH ONE - Setup & Web Migration</h1>";
echo "<p style='color:#94a3b8;'>PHP Version: <b>".PHP_VERSION."</b> | Driver MySQL: <b style='color:#10b981;'>✓ AKTIF</b></p>";

// Proses Tombol Migrasi
if (isset($_POST['run_setup'])) {
    echo "<div style='background:#020617;border:1px solid #10b981;border-radius:12px;padding:20px;margin:20px 0;'>";
    echo "<h3 style='color:#34d399;margin-top:0;'>🚀 Menjalankan Instalasi Database...</h3>";
    try {
        Artisan::call('migrate', ['--force' => true]);
        echo "<pre style='color:#67e8f9;font-size:12px;max-height:200px;overflow:auto;'>".htmlspecialchars(Artisan::output()).'</pre>';

        echo "<h4 style='color:#34d399;'>Memasang Data Akun Default (Super Admin, Guru Piket, Siswa, dll)...</h4>";
        Artisan::call('db:seed', ['--force' => true]);
        echo "<pre style='color:#67e8f9;font-size:12px;max-height:200px;overflow:auto;'>".htmlspecialchars(Artisan::output()).'</pre>';

        Artisan::call('storage:link');
        Artisan::call('optimize:clear');
        Artisan::call('optimize');

        echo "<div style='background:#064e3b;color:#a7f3d0;padding:15px;border-radius:10px;margin-top:15px;font-weight:bold;'>";
        echo '🎉 ALHAMDULILLAH! INSTALASI & SEEDER SELESAI DENGAN SUKSES!';
        echo '</div>';
        echo "<p style='margin-top:20px;'><a href='/' style='background:#10b981;color:#022c22;padding:12px 24px;border-radius:10px;text-decoration:none;font-weight:bold;display:inline-block;'>Buka Beranda MADRASAH ONE &rarr;</a></p>";
    } catch (Throwable $e) {
        echo "<div style='background:#881337;color:#fecdd3;padding:15px;border-radius:10px;'>";
        echo '<b>Gagal Migrasi:</b> '.htmlspecialchars($e->getMessage()).'<br>';
        echo '<small>Detail: '.htmlspecialchars($e->getFile()).' baris '.$e->getLine().'</small>';
        echo '</div>';
    }
    echo '</div>';
}

// 1. Status Koneksi Database
echo "<div style='background:#1e293b;padding:15px;border-radius:12px;margin-bottom:20px;'>";
echo "<h3 style='margin:0 0 10px 0;font-size:16px;'>1. Status Koneksi Database MySQL:</h3>";
$canMigrate = false;
try {
    DB::connection()->getPdo();
    $dbName = DB::connection()->getDatabaseName();
    echo "<span style='color:#10b981;font-weight:bold;'>✓ Terhubung ke MySQL! Database: $dbName</span>";
    $canMigrate = true;
} catch (Throwable $e) {
    echo "<span style='color:#f87171;font-weight:bold;'>✗ Gagal Terhubung: ".htmlspecialchars($e->getMessage()).'</span>';
}
echo '</div>';

// 2. Tombol Aksi
if ($canMigrate) {
    echo "<form method='POST'>";
    echo "<button type='submit' name='run_setup' value='1' style='background:#10b981;color:#022c22;font-size:16px;font-weight:bold;padding:15px 30px;border:none;border-radius:12px;cursor:pointer;width:100%;box-shadow:0 10px 15px -3px rgba(16,185,129,0.3);'>";
    echo '🚀 Klik Di Sini: Jalankan Migrasi & Buat Akun Default';
    echo '</button>';
    echo '</form>';
} else {
    echo "<p style='color:#fbbf24;'>⚠️ Tombol migrasi dinonaktifkan karena koneksi database belum berhasil.</p>";
}

echo '</div>';
