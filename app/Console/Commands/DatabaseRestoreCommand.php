<?php

namespace App\Console\Commands;

use App\Services\DatabaseManagementService;
use Illuminate\Console\Command;

class DatabaseRestoreCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:restore {file? : Nama berkas .sql di storage/app/backups} {--force : Menjalankan tanpa konfirmasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memulihkan (restore) database MySQL dari berkas cadangan .sql';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseManagementService $service): int
    {
        $filename = $this->argument('file');

        if (! $filename) {
            $backups = $service->listBackups();
            if (empty($backups)) {
                $this->warn('Tidak ada berkas cadangan di storage/app/backups.');

                return Command::FAILURE;
            }

            $choices = array_map(fn ($b) => "{$b['filename']} ({$b['human_size']} - {$b['created_at']})", $backups);
            $selected = $this->choice('Pilih berkas cadangan yang ingin dipulihkan:', $choices, 0);

            // Extract filename from choice string
            preg_match('/^([^\s]+)/', $selected, $matches);
            $filename = $matches[1] ?? null;
        }

        if (! $this->option('force')) {
            if (! $this->confirm("PERINGATAN: Memulihkan database akan menimpa data yang ada saat ini dengan berkas '{$filename}'. Lanjutkan?", false)) {
                $this->info('Proses pemulihan dibatalkan.');

                return Command::SUCCESS;
            }
        }

        $this->info("Memulai pemulihan dari berkas '{$filename}'...");

        try {
            $result = $service->restore($filename);

            $this->newLine();
            $this->info("✓ {$result['message']}");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Gagal memulihkan database: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
