<?php

namespace App\Console\Commands;

use App\Models\SchoolSetting;
use App\Services\DatabaseManagementService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ProductionInitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:production-init 
                            {--clean-database : Kosongkan seluruh database dummy dan siapkan clean slate untuk data riil}
                            {--admin-password= : Password untuk super admin (jika clean database)}
                            {--force : Lewati konfirmasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menyiapkan sistem untuk lingkungan produksi (Production Readiness & Optimization)';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseManagementService $service): int
    {
        $this->info('===========================================================');
        $this->info(' MADRASAH ONE - INISIALISASI LINGKUNGAN PRODUKSI');
        $this->info('===========================================================');

        // 1. Matikan mode demo di pengaturan sekolah
        $this->line('1. Memastikan mode demo nonaktif...');
        SchoolSetting::where('id', 1)->update(['enable_demo_mode' => false]);
        $this->info('   ✓ Mode demo dinonaktifkan.');

        // 2. Opsi bersihkan database
        if ($this->option('clean-database')) {
            $this->newLine();
            $this->warn('2. Mereset database ke kondisi bersih (Clean Slate Produksi)...');
            if (! $this->option('force')) {
                if (! $this->confirm('Apakah Anda yakin ingin mengosongkan data dummy dan membuat basis data produksi baru?', false)) {
                    $this->info('Pembersihan database dilewati.');
                } else {
                    $service->reset('fresh', $this->option('admin-password'));
                    $this->info('   ✓ Database bersih untuk produksi berhasil disiapkan.');
                }
            } else {
                $service->reset('fresh', $this->option('admin-password'));
                $this->info('   ✓ Database bersih untuk produksi berhasil disiapkan.');
            }
        } else {
            $this->line('2. Menjalankan migrasi database terbaru...');
            Artisan::call('migrate', ['--force' => true]);
            $this->info('   ✓ Migrasi database up-to-date.');
        }

        // 3. Bersihkan Cache Bootstrap
        $this->newLine();
        $this->line('3. Menyiapkan cache bersih...');
        Artisan::call('optimize:clear');
        $this->info('   ✓ Cache aplikasi dibersihkan dan siap.');

        $this->newLine();
        $this->info('===========================================================');
        $this->info(' SISTEM SIAP DIGUNAKAN DI VERSI PRODUKSI!');
        $this->info(' Catatan: Di server hosting, jalankan "php artisan optimize"');
        $this->info('===========================================================');

        return Command::SUCCESS;
    }
}
