<?php

namespace App\Console\Commands;

use App\Services\DatabaseManagementService;
use Illuminate\Console\Command;

class DatabaseResetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:reset 
                            {--mode=transactions : Mode reset: "transactions" (hanya riwayat transaksi) atau "fresh" (reset total bersih untuk produksi)}
                            {--password= : Password untuk super admin (hanya jika mode=fresh)}
                            {--force : Jalankan tanpa konfirmasi interaktif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mereset database menjadi bersih/kosong dengan pencadangan darurat otomatis';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseManagementService $service): int
    {
        $mode = $this->option('mode') ?: 'transactions';
        if (! in_array($mode, ['transactions', 'fresh', 'clean'])) {
            $this->error('Mode tidak valid. Gunakan --mode=transactions atau --mode=fresh');

            return Command::FAILURE;
        }

        $this->warn('===========================================================');
        $this->warn(" PERINGATAN: ANDA AKAN MENJALANKAN RESET DATABASE (MODE: {$mode})");
        $this->warn('===========================================================');

        if ($mode === 'transactions') {
            $this->line('Mode "transactions" akan mengosongkan:');
            $this->line('- Seluruh riwayat presensi harian & keterlambatan');
            $this->line('- Seluruh riwayat salat berjamaah & dispensasi ibadah');
            $this->line('- Log audit, catatan konseling BK, dan pesan komunikasi');
            $this->line('Data master sekolah, akun guru, rombel, dan siswa TETAP AMAN.');
        } else {
            $this->line('Mode "fresh" (Clean Slate) akan:');
            $this->line('- Menghapus SELURUH tabel dan data dummy pengujian');
            $this->line('- Menjalankan migrasi database bersih dari awal');
            $this->line('- Menyiapkan 1 akun Super Administrator (superadmin) untuk produksi');
        }

        if (! $this->option('force')) {
            $confirmText = $this->ask('Ketik "RESET" untuk mengonfirmasi tindakan ini');
            if ($confirmText !== 'RESET') {
                $this->info('Reset database dibatalkan.');

                return Command::SUCCESS;
            }
        }

        $this->info('Membuat pencadangan darurat otomatis...');

        try {
            $password = $this->option('password');
            $result = $service->reset($mode, $password);

            $this->newLine();
            $this->info("✓ {$result['message']}");
            $this->comment("Cadangan pengaman darurat telah disimpan di: {$result['safety_backup']}");

            if (! empty($result['admin_credentials'])) {
                $this->newLine();
                $this->info('Kredensial Super Administrator Baru:');
                $this->table(
                    ['Username', 'Email', 'Password Default'],
                    [[
                        $result['admin_credentials']['username'],
                        $result['admin_credentials']['email'],
                        $result['admin_credentials']['default_password'],
                    ]]
                );
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Gagal mereset database: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
