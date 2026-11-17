<?php

use Illuminate\Contracts\Console\Kernel;

/**
 * ==============================================================================
 * MADRASAH ONE - Web Installation & Maintenance Helper (Shared Hosting)
 * Gunakan file ini jika hosting Anda tidak memiliki akses SSH / Terminal.
 * URL Akses: https://domain-anda.sch.id/install-helper.php?secret=MADRASAH_ONE_SETUP_2026
 * ==============================================================================
 */

// Kunci Rahasia untuk mencegah akses sembarang orang
define('SECRET_KEY', 'MADRASAH_ONE_SETUP_2026');

if (! isset($_GET['secret']) || $_GET['secret'] !== SECRET_KEY) {
    http_response_code(403);
    echo '<!DOCTYPE html><html lang="id"><head><title>Akses Ditolak</title><meta name="viewport" content="width=device-width, initial-scale=1"></head><body style="font-family:sans-serif;background:#0f172a;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;"><div style="text-align:center;padding:2rem;background:#1e293b;border-radius:1rem;border:1px solid #334155;"><h2>🔒 Akses Ditolak</h2><p style="color:#94a3b8;font-size:14px;">Masukkan parameter <code>?secret='.SECRET_KEY.'</code> pada URL untuk membuka installer ini.</p></div></body></html>';
    exit;
}

// Inisialisasi Kernel Laravel
$bootstrapPath = __DIR__.'/../bootstrap/app.php';
if (! file_exists($bootstrapPath)) {
    exit('File bootstrap/app.php tidak ditemukan. Pastikan file ini berada di dalam folder public/ aplikasi Laravel.');
}

require __DIR__.'/../vendor/autoload.php';
$app = require_once $bootstrapPath;
$kernel = $app->make(Kernel::class);
try {
    $kernel->bootstrap();
} catch (\Throwable $e) {
    //
}

$logs = [];
$action = $_POST['action'] ?? null;

// Handle Aksi Hapus Script
if ($action === 'delete_installer') {
    @unlink(__FILE__);
    echo '<!DOCTYPE html><html lang="id"><head><title>Installer Dihapus</title><meta name="viewport" content="width=device-width, initial-scale=1"></head><body style="font-family:sans-serif;background:#0f172a;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;"><div style="text-align:center;padding:2rem;background:#1e293b;border-radius:1rem;border:1px solid #10b981;"><h2>✅ Installer Berhasil Dihapus!</h2><p style="color:#94a3b8;font-size:14px;">File <code>install-helper.php</code> telah dihapus dari server demi keamanan website.</p><a href="/" style="display:inline-block;padding:10px 20px;background:#10b981;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;margin-top:15px;">Buka Website Madrasah One</a></div></body></html>';
    exit;
}

function runArtisanCommand($kernel, $command, $params = [])
{
    try {
        $exitCode = $kernel->call($command, $params);
        $output = $kernel->output();

        return [
            'success' => $exitCode === 0,
            'command' => $command.' '.implode(' ', array_map(fn ($k, $v) => is_numeric($k) ? $v : "$k=$v", array_keys($params), $params)),
            'output' => trim($output),
        ];
    } catch (Throwable $e) {
        return [
            'success' => false,
            'command' => $command,
            'output' => $e->getMessage(),
        ];
    }
}

// Proses Aksi Instalasi
if ($action === 'run_full_setup') {
    // 1. Key Generate jika belum ada
    if (! config('app.key')) {
        $logs[] = runArtisanCommand($kernel, 'key:generate', ['--force' => true]);
    }
    // 2. Storage Link
    $logs[] = runArtisanCommand($kernel, 'storage:link');
    // 3. Migrate
    $logs[] = runArtisanCommand($kernel, 'migrate', ['--force' => true]);
    // 4. Seed
    if (isset($_POST['seed_database'])) {
        $logs[] = runArtisanCommand($kernel, 'db:seed', ['--force' => true]);
    }
    // 5. Optimize
    $logs[] = runArtisanCommand($kernel, 'optimize:clear');
    $logs[] = runArtisanCommand($kernel, 'optimize');
} elseif ($action === 'run_migrate_only') {
    $logs[] = runArtisanCommand($kernel, 'migrate', ['--force' => true]);
} elseif ($action === 'run_optimize_only') {
    $logs[] = runArtisanCommand($kernel, 'optimize:clear');
    $logs[] = runArtisanCommand($kernel, 'optimize');
} elseif ($action === 'run_storage_link') {
    $logs[] = runArtisanCommand($kernel, 'storage:link');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Web Installer & Maintenance | MADRASAH ONE</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #090d16; color: #f1f5f9; margin: 0; padding: 2rem 1rem; }
        .container { max-width: 750px; margin: 0 auto; background: #131c2e; border: 1px solid #1e293b; border-radius: 20px; padding: 2.5rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); }
        h1 { margin-top: 0; color: #10b981; font-size: 22px; display: flex; align-items: center; gap: 10px; }
        .badge { background: #064e3b; color: #6ee7b7; font-size: 11px; padding: 4px 10px; border-radius: 9999px; font-weight: bold; border: 1px solid #047857; }
        .card { background: #0f172a; border: 1px solid #334155; border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem; }
        .btn { background: #10b981; color: #022c22; padding: 12px 20px; border: none; border-radius: 10px; font-weight: bold; font-size: 13px; cursor: pointer; transition: 0.2s; text-decoration: none; display: inline-block; }
        .btn:hover { background: #34d399; }
        .btn-outline { background: transparent; border: 1px solid #475569; color: #cbd5e1; }
        .btn-outline:hover { background: #1e293b; color: #fff; }
        .btn-danger { background: #e11d48; color: #fff; }
        .btn-danger:hover { background: #f43f5e; }
        .log-box { background: #020617; border: 1px solid #1e293b; border-radius: 10px; padding: 1rem; font-family: monospace; font-size: 12px; color: #38bdf8; max-height: 250px; overflow-y: auto; margin-top: 1rem; }
        .log-item { margin-bottom: 8px; border-bottom: 1px dashed #1e293b; padding-bottom: 6px; }
        .log-success { color: #4ade80; }
        .log-fail { color: #fb7185; }
        .env-info { font-size: 12px; color: #94a3b8; display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>
            <span>MADRASAH ONE</span>
            <span class="badge">Web Installer Hosting</span>
        </h1>
        <p style="color:#94a3b8;font-size:13px;margin-top:4px;">
            Alat bantu instalasi database dan konfigurasi Laravel untuk Shared Hosting (cPanel / DirectAdmin) tanpa SSH.
        </p>

        <!-- Informasi Lingkungan -->
        <div class="card">
            <strong style="font-size:13px;color:#f8fafc;">Status Lingkungan Server:</strong>
            <div class="env-info">
                <div>PHP Version: <span style="color:#38bdf8;font-weight:bold;"><?= PHP_VERSION ?></span></div>
                <div>Database Driver: <span style="color:#38bdf8;font-weight:bold;"><?= function_exists('config') && app()->bound('config') ? config('database.default', 'mysql') : 'mysql' ?></span></div>
                <div>Database Name: <span style="color:#38bdf8;font-weight:bold;"><?= function_exists('config') && app()->bound('config') ? config('database.connections.'.config('database.default').'.database', '-') : '-' ?></span></div>
                <div>APP_KEY: <span style="color:<?= (function_exists('config') && app()->bound('config') && config('app.key')) ? '#4ade80' : '#fb7185' ?>;font-weight:bold;"><?= (function_exists('config') && app()->bound('config') && config('app.key')) ? 'Terkonfigurasi' : 'Belum Ada' ?></span></div>
            </div>
        </div>

        <!-- Log Output Eksekusi -->
        <?php if (! empty($logs)) { ?>
        <div class="card" style="border-color:#059669;">
            <strong style="font-size:13px;color:#34d399;">Hasil Eksekusi:</strong>
            <div class="log-box">
                <?php foreach ($logs as $log) { ?>
                    <div class="log-item">
                        <span class="<?= $log['success'] ? 'log-success' : 'log-fail' ?>">
                            <?= $log['success'] ? '✓ SUKSES' : '✗ GAGAL' ?>:
                        </span>
                        <code>php artisan <?= htmlspecialchars($log['command']) ?></code>
                        <div style="color:#cbd5e1;margin-top:3px;white-space:pre-wrap;"><?= htmlspecialchars($log['output']) ?></div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php } ?>

        <!-- Form Aksi Utama -->
        <form method="POST" class="card" style="display:flex;flex-direction:column;gap:12px;">
            <strong style="font-size:14px;color:#f8fafc;">Pilihan Tindakan:</strong>
            
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#cbd5e1;cursor:pointer;">
                <input type="checkbox" name="seed_database" value="1" checked style="accent-color:#10b981;">
                Sertakan akun default & data awal (Super Admin, Guru Piket, Siswa, dll)
            </label>

            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px;">
                <button type="submit" name="action" value="run_full_setup" class="btn">
                    🚀 Jalankan Instalasi Penuh (Migrasi + Seed + Cache + Storage)
                </button>
                <button type="submit" name="action" value="run_migrate_only" class="btn btn-outline">
                    Hanya Migrasi Database
                </button>
                <button type="submit" name="action" value="run_storage_link" class="btn btn-outline">
                    Buat Storage Link
                </button>
                <button type="submit" name="action" value="run_optimize_only" class="btn btn-outline">
                    Bersihkan & Optimasi Cache
                </button>
            </div>
        </form>

        <!-- Danger Zone: Hapus Installer -->
        <div class="card" style="border-color:#881337;background:#1e111a;display:flex;align-items:center;justify-content:between;">
            <div>
                <strong style="color:#f43f5e;font-size:13px;">Keamanan Sistem:</strong>
                <p style="color:#fda4af;font-size:12px;margin:2px 0 0 0;">Setelah proses instalasi selesai dan website berjalan normal, hapus file installer ini demi keamanan.</p>
            </div>
            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus file install-helper.php dari server?')">
                <button type="submit" name="action" value="delete_installer" class="btn btn-danger" style="white-space:nowrap;margin-left:15px;">
                    🗑️ Hapus Installer Ini
                </button>
            </form>
        </div>

        <div style="text-align:center;margin-top:1.5rem;">
            <a href="/" target="_blank" class="btn btn-outline" style="font-size:12px;">
                Buka Beranda MADRASAH ONE &rarr;
            </a>
        </div>
    </div>
</body>
</html>
