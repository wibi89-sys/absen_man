<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Header awal agar LiteSpeed tidak menyembunyikan respons dengan generic 500
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MADRASAH ONE - Setup & Web Migration</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #090d16; color: #f1f5f9; margin: 0; padding: 2rem 1rem; }
        .container { max-width: 850px; margin: 0 auto; background: #131c2e; border: 1px solid #1e293b; border-radius: 20px; padding: 2.5rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); }
        h1 { margin-top: 0; color: #10b981; font-size: 24px; }
        .card { background: #0f172a; border: 1px solid #334155; border-radius: 12px; padding: 1.25rem; margin-bottom: 1.25rem; }
        .btn { background: #10b981; color: #022c22; padding: 14px 24px; border: none; border-radius: 12px; font-weight: bold; font-size: 15px; cursor: pointer; transition: 0.2s; width: 100%; box-shadow: 0 10px 15px -3px rgba(16,185,129,0.3); }
        .btn:hover { background: #34d399; }
        .status-ok { color: #34d399; font-weight: bold; }
        .status-fail { color: #f87171; font-weight: bold; }
        .status-warn { color: #fbbf24; font-weight: bold; }
        pre { background: #020617; border: 1px solid #1e293b; border-radius: 10px; padding: 1rem; font-family: monospace; font-size: 12px; color: #38bdf8; max-height: 250px; overflow-y: auto; white-space: pre-wrap; }
        .step { display: flex; align-items: center; gap: 8px; margin: 6px 0; font-size: 14px; }
    </style>
</head>
<body>
<div class="container">
    <h1>MADRASAH ONE - Setup & Web Migration</h1>
    <p style="color:#94a3b8;font-size:14px;">Diagnostik Lingkungan Server & Pelaksana Migrasi Otomatis</p>

    <div class="card">
        <h3 style="margin-top:0;font-size:16px;">1. Pengecekan Lingkungan Dasar</h3>
        <div class="step">
            <span>Versi PHP:</span>
            <span class="<?= version_compare(PHP_VERSION, '8.4.1', '>=') ? 'status-ok' : 'status-fail' ?>">
                <?= PHP_VERSION ?> <?= version_compare(PHP_VERSION, '8.4.1', '>=') ? '✓ (Sesuai)' : '✗ (Wajib >= 8.4.1)' ?>
            </span>
        </div>
        <div class="step">
            <span>Ekstensi MySQL (PDO):</span>
            <span class="<?= extension_loaded('pdo_mysql') ? 'status-ok' : 'status-fail' ?>">
                <?= extension_loaded('pdo_mysql') ? '✓ Aktif' : '✗ Belum Aktif' ?>
            </span>
        </div>
        <div class="step">
            <span>Ekstensi Penting (mbstring, openssl, fileinfo, intl):</span>
            <?php
            $missingExts = [];
            foreach (['mbstring', 'openssl', 'fileinfo', 'intl', 'xml'] as $ext) {
                if (! extension_loaded($ext)) {
                    $missingExts[] = $ext;
                }
            }
            if (empty($missingExts)) {
                echo '<span class="status-ok">✓ Semua Aktif</span>';
            } else {
                echo '<span class="status-fail">✗ Kurang: '.implode(', ', $missingExts).'</span>';
            }
            ?>
        </div>
        <div class="step">
            <span>Folder vendor/:</span>
            <?php
            $vendorExists = file_exists(__DIR__.'/../vendor/autoload.php');
            if ($vendorExists) {
                echo '<span class="status-ok">✓ Terpasang (autoload.php ada)</span>';
            } else {
                echo '<span class="status-fail">✗ Tidak Ditemukan! Upload folder vendor dari vendor-absen-man.zip</span>';
            }
            ?>
        </div>
        <div class="step">
            <span>File .env:</span>
            <?php
            $envPath = __DIR__.'/../.env';
            $envExists = file_exists($envPath);
            if ($envExists) {
                echo '<span class="status-ok">✓ Ditemukan</span>';
            } else {
                echo '<span class="status-warn">⚠️ Belum Ada (Akan dibuat otomatis dari .env.production.example)</span>';
                if (file_exists(__DIR__.'/../.env.production.example')) {
                    @copy(__DIR__.'/../.env.production.example', $envPath);
                }
            }
            ?>
        </div>
        <div class="step">
            <span>Folder storage/ (Izin Tulis):</span>
            <?php
            $storageWritable = is_writable(__DIR__.'/../storage');
            echo $storageWritable ? '<span class="status-ok">✓ Writable</span>' : '<span class="status-fail">✗ Not Writable (Beri izin 775 / 777)</span>';
            ?>
        </div>
        <div class="step">
            <span>Folder bootstrap/cache (Izin Tulis):</span>
            <?php
            $cacheWritable = is_writable(__DIR__.'/../bootstrap/cache');
            echo $cacheWritable ? '<span class="status-ok">✓ Writable</span>' : '<span class="status-fail">✗ Not Writable (Beri izin 775 / 777)</span>';
            ?>
        </div>
    </div>

    <?php
    if (! $vendorExists) {
        echo "<div class='card' style='border-color:#ef4444;'>";
        echo "<h3 style='color:#ef4444;margin-top:0;'>❌ Tidak Dapat Melanjutkan</h3>";
        echo '<p>Folder <code>vendor/</code> belum terpasang di server. Silakan upload dan ekstrak file <b>vendor-absen-man.zip</b> ke folder aplikasi.</p>';
        echo '</div></div></body></html>';
        exit;
    }

// Inisialisasi Framework Laravel dengan Exception Handling Penuh
$appLoaded = false;
$kernel = null;
try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';

    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    $appLoaded = true;
} catch (\Throwable $e) {
    echo "<div class='card' style='border-color:#ef4444;'>";
    echo "<h3 style='color:#ef4444;margin-top:0;'>❌ Gagal Bootstrap Laravel Kernel:</h3>";
    echo '<p style="color:#fecaca;"><b>Pesan:</b> '.htmlspecialchars($e->getMessage()).'</p>';
    echo '<small style="color:#94a3b8;">Lokasi: '.htmlspecialchars($e->getFile()).' baris '.$e->getLine().'</small>';
    echo '<pre>'.htmlspecialchars($e->getTraceAsString()).'</pre>';
    echo '</div>';
}

if ($appLoaded) {
    // Pastikan APP_KEY terpasang
    if (! config('app.key')) {
        $key = 'base64:'.base64_encode(random_bytes(32));
        if (file_exists($envPath)) {
            file_put_contents($envPath, "\nAPP_KEY=$key\n", FILE_APPEND);
        }
        config(['app.key' => $key]);
    }

    // Tangani Aksi Migrasi
    if (isset($_POST['run_setup'])) {
        echo "<div class='card' style='border-color:#10b981;'>";
        echo "<h3 style='color:#34d399;margin-top:0;'>🚀 Menjalankan Instalasi Database...</h3>";
        try {
            $exitCode = $kernel->call('migrate', ['--force' => true]);
            echo '<pre>'.htmlspecialchars($kernel->output()).'</pre>';

            echo "<h4 style='color:#34d399;'>Memasang Data Akun Default (Super Admin, Guru Piket, Siswa, dll)...</h4>";
            $exitCodeSeed = $kernel->call('db:seed', ['--force' => true]);
            echo '<pre>'.htmlspecialchars($kernel->output()).'</pre>';

            $kernel->call('storage:link');
            $kernel->call('optimize:clear');
            $kernel->call('optimize');

            echo "<div style='background:#064e3b;color:#a7f3d0;padding:15px;border-radius:10px;margin-top:15px;font-weight:bold;'>";
            echo '🎉 ALHAMDULILLAH! INSTALASI & SEEDER SELESAI DENGAN SUKSES!';
            echo '</div>';
            echo "<p style='margin-top:20px;'><a href='/' style='background:#10b981;color:#022c22;padding:12px 24px;border-radius:10px;text-decoration:none;font-weight:bold;display:inline-block;'>Buka Beranda MADRASAH ONE &rarr;</a></p>";
        } catch (\Throwable $e) {
            echo "<div style='background:#881337;color:#fecdd3;padding:15px;border-radius:10px;'>";
            echo '<b>Gagal Migrasi:</b> '.htmlspecialchars($e->getMessage()).'<br>';
            echo '<small>Detail: '.htmlspecialchars($e->getFile()).' baris '.$e->getLine().'</small>';
            echo '</div>';
        }
        echo '</div>';
    }

    // Status Koneksi Database
    echo "<div class='card'>";
    echo "<h3 style='margin:0 0 10px 0;font-size:16px;'>2. Status Koneksi Database MySQL:</h3>";
    $canMigrate = false;
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $dbName = \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
        echo "<span class='status-ok'>✓ Terhubung ke MySQL! Database: $dbName</span>";
        $canMigrate = true;
    } catch (\Throwable $e) {
        echo "<span class='status-fail'>✗ Gagal Terhubung: ".htmlspecialchars($e->getMessage()).'</span>';
        echo '<p style="color:#94a3b8;font-size:13px;margin:8px 0 0 0;">Pastikan pengaturan DB_DATABASE, DB_USERNAME, dan DB_PASSWORD pada file <code>.env</code> di hosting sudah sesuai dengan database yang dibuat di cPanel.</p>';
    }
    echo '</div>';

    // Tombol Aksi
    if ($canMigrate) {
        echo "<form method='POST'>";
        echo "<button type='submit' name='run_setup' value='1' class='btn'>";
        echo '🚀 Klik Di Sini: Jalankan Migrasi & Buat Akun Default';
        echo '</button>';
        echo '</form>';
    }
}
?>
</div>
</body>
</html>
