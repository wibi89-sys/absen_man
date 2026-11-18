<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\DisciplineCategory;
use App\Models\Role;
use App\Models\SchoolSetting;
use App\Models\Semester;
use App\Models\User;
use App\Models\WorshipType;
use Database\Seeders\AttendanceTypeSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SchoolSettingSeeder;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseManagementService
{
    protected string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (! File::exists($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
    }

    /**
     * Get list of database tables for the current connection database.
     */
    public function getTables(): array
    {
        $dbName = config('database.connections.mysql.database');
        $rows = DB::select('SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = "BASE TABLE" ORDER BY TABLE_NAME ASC', [$dbName]);

        return array_map(fn ($r) => $r->TABLE_NAME, $rows);
    }

    /**
     * Get database size in bytes.
     */
    public function getDatabaseSize(): array
    {
        try {
            $dbName = config('database.connections.mysql.database');
            $result = DB::select('SELECT SUM(data_length + index_length) as size_bytes, COUNT(TABLE_NAME) as total_tables FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?', [$dbName]);

            $bytes = (int) ($result[0]->size_bytes ?? 0);
            $totalTables = (int) ($result[0]->total_tables ?? 0);

            return [
                'bytes' => $bytes,
                'human' => $this->formatBytes($bytes),
                'tables_count' => $totalTables,
            ];
        } catch (\Throwable $e) {
            return [
                'bytes' => 0,
                'human' => '0 MB',
                'tables_count' => 0,
            ];
        }
    }

    /**
     * Create a full pure-PHP PDO backup of the MySQL database.
     */
    public function backup(?string $description = null, bool $isAuto = false): array
    {
        $dbName = config('database.connections.mysql.database');
        $timestamp = now()->format('Y-m-d_His');
        $prefix = $isAuto ? 'auto_backup_before_reset_' : 'backup_MADRASAH_ONE_';
        $filename = "{$prefix}{$timestamp}.sql";
        $filePath = "{$this->backupDir}/{$filename}";

        $handle = fopen($filePath, 'w');
        if (! $handle) {
            throw new Exception("Gagal membuat berkas cadangan di: {$filePath}");
        }

        try {
            $pdo = DB::connection()->getPdo();

            // SQL Header
            fwrite($handle, "-- ==========================================================\n");
            fwrite($handle, "-- MADRASAH ONE - Integrated Student Monitoring System\n");
            fwrite($handle, "-- Database Backup (Pure PHP Portable Dump)\n");
            fwrite($handle, '-- Waktu Dibuat: '.now()->toIso8601String()."\n");
            fwrite($handle, "-- Database: {$dbName}\n");
            if ($description) {
                fwrite($handle, "-- Catatan: {$description}\n");
            }
            fwrite($handle, "-- ==========================================================\n\n");

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
            fwrite($handle, "SET time_zone = \"+00:00\";\n\n");

            $tables = $this->getTables();

            foreach ($tables as $table) {
                // Table structure
                fwrite($handle, "-- ----------------------------------------------------------\n");
                fwrite($handle, "-- Struktur Tabel: `{$table}`\n");
                fwrite($handle, "-- ----------------------------------------------------------\n");
                fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

                $createTableQuery = DB::select("SHOW CREATE TABLE `{$table}`");
                if (! empty($createTableQuery)) {
                    $createRow = (array) $createTableQuery[0];
                    $createTableSql = $createRow['Create Table'] ?? array_values($createRow)[1] ?? null;
                    if ($createTableSql) {
                        fwrite($handle, "{$createTableSql};\n\n");
                    }
                }

                // Table rows dump in chunks
                fwrite($handle, "-- Data Isi Tabel: `{$table}`\n");

                $chunkSize = 200;
                $offset = 0;

                while (true) {
                    $rows = DB::table($table)->offset($offset)->limit($chunkSize)->get();
                    if ($rows->isEmpty()) {
                        break;
                    }

                    $insertSql = "INSERT INTO `{$table}` VALUES \n";
                    $valuesList = [];

                    foreach ($rows as $row) {
                        $rowArray = (array) $row;
                        $escapedValues = [];

                        foreach ($rowArray as $val) {
                            if ($val === null) {
                                $escapedValues[] = 'NULL';
                            } elseif (is_int($val) || is_float($val)) {
                                $escapedValues[] = $val;
                            } elseif (is_bool($val)) {
                                $escapedValues[] = $val ? '1' : '0';
                            } else {
                                $escapedValues[] = $pdo->quote((string) $val);
                            }
                        }

                        $valuesList[] = '('.implode(', ', $escapedValues).')';
                    }

                    $insertSql .= implode(",\n", $valuesList).";\n";
                    fwrite($handle, $insertSql);

                    $offset += $chunkSize;
                }

                fwrite($handle, "\n");
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
            fwrite($handle, "-- Selesai Pembuatan Cadangan Database.\n");

            fclose($handle);

            $fileSize = filesize($filePath);

            return [
                'success' => true,
                'filename' => $filename,
                'path' => $filePath,
                'size_bytes' => $fileSize,
                'human_size' => $this->formatBytes($fileSize),
                'tables_count' => count($tables),
                'created_at' => now()->toIso8601String(),
            ];
        } catch (\Throwable $e) {
            fclose($handle);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
            Log::error('Database backup failed: '.$e->getMessage());
            throw new Exception('Proses cadangan database gagal: '.$e->getMessage());
        }
    }

    /**
     * List all available database backups in storage.
     */
    public function listBackups(): array
    {
        if (! File::exists($this->backupDir)) {
            return [];
        }

        $files = File::files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'sql') {
                continue;
            }

            $bytes = $file->getSize();
            $filename = $file->getFilename();

            $backups[] = [
                'filename' => $filename,
                'path' => $file->getPathname(),
                'bytes' => $bytes,
                'human_size' => $this->formatBytes($bytes),
                'modified_timestamp' => $file->getMTime(),
                'created_at' => Carbon::createFromTimestamp($file->getMTime())->setTimezone('Asia/Jakarta')->translatedFormat('d M Y H:i:s'),
                'is_auto' => str_starts_with($filename, 'auto_backup'),
            ];
        }

        usort($backups, fn ($a, $b) => $b['modified_timestamp'] <=> $a['modified_timestamp']);

        return $backups;
    }

    /**
     * Restore database from an existing file or uploaded file.
     */
    public function restore(?string $filename = null, ?UploadedFile $uploadedFile = null): array
    {
        $filePath = null;

        if ($uploadedFile) {
            if ($uploadedFile->getClientOriginalExtension() !== 'sql') {
                throw new Exception('File yang diunggah harus berekstensi .sql');
            }
            $tempName = 'upload_restore_'.now()->format('Y-m-d_His').'.sql';
            $filePath = $uploadedFile->storeAs('backups', $tempName);
            $filePath = storage_path('app/'.$filePath);
        } elseif ($filename) {
            $sanitized = basename($filename);
            $candidate = "{$this->backupDir}/{$sanitized}";
            if (! File::exists($candidate)) {
                throw new Exception("File cadangan '{$sanitized}' tidak ditemukan.");
            }
            $filePath = $candidate;
        } else {
            throw new Exception('Nama berkas cadangan atau berkas unggahan diperlukan untuk proses restore.');
        }

        // Before restoration, take an automated safety snapshot
        try {
            $this->backup('Otomatis sebelum restore berkas '.$filename, true);
        } catch (\Throwable $e) {
            Log::warning('Automated pre-restore snapshot failed: '.$e->getMessage());
        }

        try {
            $pdo = DB::connection()->getPdo();
            $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

            // Disable foreign key checks
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0;');

            // Read SQL file statement by statement
            $handle = fopen($filePath, 'r');
            if (! $handle) {
                throw new Exception("Tidak dapat membaca berkas cadangan di: {$filePath}");
            }

            $currentQuery = '';

            while (! feof($handle)) {
                $line = fgets($handle);

                // Skip comments and empty lines
                $trimmed = trim($line);
                if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                    continue;
                }

                $currentQuery .= $line;

                if (str_ends_with($trimmed, ';')) {
                    try {
                        $pdo->exec($currentQuery);
                    } catch (\Throwable $qe) {
                        Log::warning('SQL Restore notice: '.$qe->getMessage().' in query: '.substr($currentQuery, 0, 100));
                    }
                    $currentQuery = '';
                }
            }

            fclose($handle);

            $pdo->exec('SET FOREIGN_KEY_CHECKS=1;');

            // Clear cache
            Artisan::call('optimize:clear');

            return [
                'success' => true,
                'message' => 'Database berhasil direstore dari berkas cadangan.',
                'filename' => basename($filePath),
            ];
        } catch (\Throwable $e) {
            Log::error('Restore database error: '.$e->getMessage());
            throw new Exception('Gagal memulihkan database: '.$e->getMessage());
        }
    }

    /**
     * Delete a backup file.
     */
    public function deleteBackup(string $filename): bool
    {
        $sanitized = basename($filename);
        $filePath = "{$this->backupDir}/{$sanitized}";

        if (File::exists($filePath)) {
            return File::delete($filePath);
        }

        return false;
    }

    /**
     * Download backup file response.
     */
    public function downloadBackup(string $filename): BinaryFileResponse
    {
        $sanitized = basename($filename);
        $filePath = "{$this->backupDir}/{$sanitized}";

        if (! File::exists($filePath)) {
            abort(404, 'File backup tidak ditemukan.');
        }

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Reset database with safety backup.
     * Modes:
     * - 'transactions': Clears only attendance, worship logs, exemptions, notifications, counselling, audits. Preserves users & master data.
     * - 'fresh': Clears everything, runs fresh migration and seeds clean baseline production data (clean superadmin, roles, types, settings) without any dummy records.
     */
    public function reset(string $mode = 'transactions', ?string $newAdminPassword = null): array
    {
        // 1. Mandatory emergency safety backup
        $safetyBackup = $this->backup("Pencadangan Darurat Otomatis Sebelum Reset ({$mode})", true);

        if ($mode === 'transactions') {
            $transactionTables = [
                'attendance_corrections',
                'attendance_qr_tokens',
                'daily_attendances',
                'attendance_sessions',
                'worship_attendances',
                'worship_session_participants',
                'worship_exemptions',
                'worship_qr_tokens',
                'worship_sessions',
                'parent_messages',
                'student_activity_attendances',
                'discipline_records',
                'counseling_sessions',
                'login_audits',
                'audit_logs',
            ];

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            foreach ($transactionTables as $t) {
                if (Schema::hasTable($t)) {
                    DB::table($t)->truncate();
                }
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            Artisan::call('optimize:clear');

            return [
                'success' => true,
                'mode' => 'transactions',
                'message' => 'Seluruh data transaksi (presensi, ibadah, perizinan, dan catatan log) berhasil dikosongkan. Data master guru, siswa, dan rombel tetap aman.',
                'safety_backup' => $safetyBackup['filename'],
            ];
        }

        if ($mode === 'fresh' || $mode === 'clean') {
            // Full Clean Slate Production Reset
            Artisan::call('migrate:fresh', ['--force' => true]);

            // Seed clean production baseline
            $this->seedCleanProduction($newAdminPassword);

            Artisan::call('optimize:clear');

            return [
                'success' => true,
                'mode' => 'fresh',
                'message' => 'Database berhasil direset bersih untuk produksi (Clean Slate). Seluruh data dummy telah dihapus dan sistem siap diisi data riil madrasah.',
                'safety_backup' => $safetyBackup['filename'],
                'admin_credentials' => [
                    'username' => 'superadmin',
                    'email' => 'admin@madrasah.sch.id',
                    'default_password' => $newAdminPassword ?: 'AdminMadrasah2026!',
                ],
            ];
        }

        throw new Exception("Mode reset tidak valid: {$mode}");
    }

    /**
     * Seed initial clean production records without dummy users or fake logs.
     */
    public function seedCleanProduction(?string $adminPassword = null): void
    {
        // 1. Roles & Permissions
        (new RoleSeeder)->run();

        // 2. School Setting
        (new SchoolSettingSeeder)->run();
        SchoolSetting::where('id', 1)->update(['enable_demo_mode' => false]);

        // 3. Attendance Types
        (new AttendanceTypeSeeder)->run();

        // 4. Worship Types
        WorshipType::updateOrCreate(['code' => 'zuhur'], [
            'name' => 'Salat Zuhur Berjamaah',
            'is_mandatory' => true,
            'description' => 'Salat wajib Zuhur berjamaah terjadwal di masjid madrasah.',
        ]);
        WorshipType::updateOrCreate(['code' => 'asar'], [
            'name' => 'Salat Asar Berjamaah',
            'is_mandatory' => true,
            'description' => 'Salat wajib Asar berjamaah bagi peserta kegiatan madrasah.',
        ]);
        WorshipType::updateOrCreate(['code' => 'dhuha'], [
            'name' => 'Salat Dhuha Bersama',
            'is_mandatory' => false,
            'description' => 'Pembiasaan salat dhuha pagi sebelum kegiatan pembelajaran.',
        ]);

        // 5. Discipline Categories (Baseline)
        $categoriesData = [
            ['name' => 'Keterlambatan Hadir (> 15 Menit)', 'type' => 'violation', 'points' => 5, 'description' => 'Siswa tiba di madrasah setelah bel masuk berbunyi.'],
            ['name' => 'Atribut Seragam Tidak Lengkap', 'type' => 'violation', 'points' => 5, 'description' => 'Tidak mengenakan perlengkapan seragam madrasah yang ditentukan.'],
            ['name' => 'Meninggalkan Salat Berjamaah Tanpa Izin', 'type' => 'violation', 'points' => 15, 'description' => 'Tidak berada di shaf salat tanpa keterangan dispensasi yang sah.'],
            ['name' => 'Juara Kompetisi Sains Madrasah (KSM)', 'type' => 'achievement', 'points' => 30, 'description' => 'Penghargaan prestasi akademik madrasah.'],
            ['name' => 'Hafalan Tahfiz Al-Qur\'an', 'type' => 'achievement', 'points' => 25, 'description' => 'Apresiasi kelulusan tasmi hafalan Al-Qur\'an.'],
            ['name' => 'Petugas Upacara / Adzan Teladan', 'type' => 'achievement', 'points' => 10, 'description' => 'Kedisiplinan dan kontribusi aktif ibadah madrasah.'],
        ];
        foreach ($categoriesData as $cat) {
            DisciplineCategory::updateOrCreate(['name' => $cat['name']], $cat);
        }

        // 6. Active Academic Year & Semester
        $ay = AcademicYear::updateOrCreate(['name' => '2026/2027'], [
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        Semester::updateOrCreate(['academic_year_id' => $ay->id, 'semester_number' => 1], [
            'name' => 'Semester Ganjil 2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);

        // 7. Initial Super Administrator Account
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $adminRole = Role::where('name', 'admin')->first();

        $superAdmin = User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Administrator Madrasah',
                'email' => 'admin@madrasah.sch.id',
                'password' => Hash::make($adminPassword ?: 'AdminMadrasah2026!'),
                'is_active' => true,
            ]
        );
        if ($superAdminRole) {
            $superAdmin->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }

        // 8. Initial Admin TU Account
        $adminUser = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator Tata Usaha',
                'email' => 'tu@madrasah.sch.id',
                'password' => Hash::make($adminPassword ?: 'AdminMadrasah2026!'),
                'is_active' => true,
            ]
        );
        if ($adminRole) {
            $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);
        }
    }

    /**
     * Format bytes to human readable format.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }
}
