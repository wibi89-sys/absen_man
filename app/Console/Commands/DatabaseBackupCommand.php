<?php

namespace App\Console\Commands;

use App\Services\DatabaseManagementService;
use Illuminate\Console\Command;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--description= : Catatan ringkas berkas cadangan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat cadangan database MySQL ke format .sql di storage/app/backups';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseManagementService $service): int
    {
        $this->info('Memulai pencadangan database...');

        try {
            $desc = $this->option('description');
            $result = $service->backup($desc);

            $this->newLine();
            $this->info('✓ Berhasil membuat cadangan database!');
            $this->table(
                ['Parameter', 'Keterangan'],
                [
                    ['Nama Berkas', $result['filename']],
                    ['Ukuran', $result['human_size']],
                    ['Jumlah Tabel', $result['tables_count']],
                    ['Lokasi Berkas', $result['path']],
                    ['Waktu', $result['created_at']],
                ]
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Gagal mencadangkan database: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
