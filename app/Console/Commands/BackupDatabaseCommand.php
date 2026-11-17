<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'madrasah:backup {--tables= : Nama tabel spesifik dipisahkan koma} {--keep=7 : Jumlah file arsip backup yang disimpan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat backup database SQL lengkap dan aman untuk lingkungan Shared Hosting cPanel';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai proses backup database Madrasah One...');

        $connection = config('database.default');
        if ($connection !== 'mysql') {
            $this->warn("Koneksi database adalah '{$connection}'. Perintah ini dioptimalkan untuk MySQL.");
        }

        $backupDir = storage_path('app/backups');
        if (! File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true, true);
        }

        $timestamp = Carbon::now()->format('Y_m_d_His');
        $filename = "backup_madrasah_one_{$timestamp}.sql";
        $filepath = "{$backupDir}/{$filename}";

        $fileHandle = fopen($filepath, 'w');
        if (! $fileHandle) {
            $this->error("Gagal membuat file backup di: {$filepath}");

            return self::FAILURE;
        }

        // Header SQL
        $appVersion = config('app.name', 'Madrasah One');
        $now = Carbon::now()->toIso8601String();
        fwrite($fileHandle, "-- ========================================================\n");
        fwrite($fileHandle, "-- Backup Database: {$appVersion}\n");
        fwrite($fileHandle, "-- Dibuat pada: {$now}\n");
        fwrite($fileHandle, "-- ========================================================\n\n");
        fwrite($fileHandle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($fileHandle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
        fwrite($fileHandle, "SET NAMES utf8mb4;\n\n");

        $pdo = DB::connection()->getPdo();

        // Ambil daftar tabel
        $tablesList = $this->option('tables');
        if ($tablesList) {
            $tables = array_filter(array_map('trim', explode(',', $tablesList)));
        } else {
            $tables = [];
            $dbName = config('database.connections.mysql.database');
            $rawTables = DB::select('SHOW TABLES');
            $property = 'Tables_in_'.$dbName;

            foreach ($rawTables as $row) {
                $tableArray = (array) $row;
                $tables[] = reset($tableArray);
            }
        }

        $this->info('Total tabel yang akan di-backup: '.count($tables));

        $bar = $this->output->createProgressBar(count($tables));
        $bar->start();

        foreach ($tables as $table) {
            // Struktur Tabel
            fwrite($fileHandle, "\n-- --------------------------------------------------------\n");
            fwrite($fileHandle, "-- Struktur tabel: `{$table}`\n");
            fwrite($fileHandle, "-- --------------------------------------------------------\n");
            fwrite($fileHandle, "DROP TABLE IF EXISTS `{$table}`;\n");

            $createTableResult = DB::select("SHOW CREATE TABLE `{$table}`");
            if (! empty($createTableResult)) {
                $createRow = (array) $createTableResult[0];
                $createSql = $createRow['Create Table'] ?? reset($createRow);
                fwrite($fileHandle, $createSql.";\n\n");
            }

            // Data Tabel
            $totalRows = DB::table($table)->count();
            if ($totalRows > 0) {
                fwrite($fileHandle, "-- Dumping data untuk tabel: `{$table}` (Total: {$totalRows} baris)\n");

                foreach (DB::table($table)->cursor() as $row) {
                    $rowArray = (array) $row;
                    $columns = array_keys($rowArray);
                    $escapedColumns = array_map(fn ($col) => "`{$col}`", $columns);

                    $values = [];
                    foreach ($rowArray as $value) {
                        if (is_null($value)) {
                            $values[] = 'NULL';
                        } elseif (is_numeric($value)) {
                            $values[] = (string) $value;
                        } else {
                            $values[] = $pdo->quote((string) $value);
                        }
                    }

                    $insertSql = sprintf(
                        "INSERT INTO `%s` (%s) VALUES (%s);\n",
                        $table,
                        implode(', ', $escapedColumns),
                        implode(', ', $values)
                    );

                    fwrite($fileHandle, $insertSql);
                }

                fwrite($fileHandle, "\n");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        fwrite($fileHandle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fwrite($fileHandle, '-- Selesai backup pada: '.Carbon::now()->toIso8601String()."\n");
        fclose($fileHandle);

        $fileSize = round(filesize($filepath) / 1024, 2);
        $this->info("Backup database berhasil dibuat: {$filename} ({$fileSize} KB)");
        Log::info("Backup database Madrasah One berhasil dibuat: {$filepath} ({$fileSize} KB)");

        // Rotasi Backup (Bersihkan file lama melebihi limit)
        $keepLimit = (int) $this->option('keep');
        $existingBackups = File::glob("{$backupDir}/backup_madrasah_one_*.sql");
        if (count($existingBackups) > $keepLimit) {
            rsort($existingBackups); // Urutkan dari yang terbaru ke terlama
            $backupsToDelete = array_slice($existingBackups, $keepLimit);

            foreach ($backupsToDelete as $oldBackup) {
                File::delete($oldBackup);
                $this->line('Menghapus backup lama: '.basename($oldBackup));
            }
        }

        return self::SUCCESS;
    }
}
