<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
                'description' => 'Konfigurasi teknis menyeluruh, audit log sistem, dan manajemen sistem penuh.',
            ],
            [
                'name' => 'admin',
                'display_name' => 'Admin Madrasah',
                'description' => 'Operasional aplikasi harian, pengelolaan master data siswa, guru, dan rombel.',
            ],
            [
                'name' => 'kepsek',
                'display_name' => 'Kepala Madrasah',
                'description' => 'Akses dashboard pimpinan, pemantauan statistik eksekutif, dan laporan manajerial.',
            ],
            [
                'name' => 'waka',
                'display_name' => 'Wakil Kepala Madrasah',
                'description' => 'Pemantauan data kesiswaan, kurikulum, dan ketertiban madrasah.',
            ],
            [
                'name' => 'guru',
                'display_name' => 'Guru Mata Pelajaran',
                'description' => 'Pencatatan aktivitas belajar mengajar di kelas dan kehadiran siswa yang diajar.',
            ],
            [
                'name' => 'wali_kelas',
                'display_name' => 'Wali Kelas',
                'description' => 'Pemantauan komprehensif, kehadiran harian, dan konfirmasi ibadah siswa di kelas binaan.',
            ],
            [
                'name' => 'guru_bk',
                'display_name' => 'Guru BK',
                'description' => 'Pengelolaan data bimbingan konseling dengan hak akses khusus dan perlindungan privasi ketat.',
            ],
            [
                'name' => 'petugas_keagamaan',
                'display_name' => 'Petugas Keagamaan',
                'description' => 'Manajemen gelombang ibadah, QR dinamis, dispensasi syar\'i, dan finalisasi verifikasi.',
            ],
            [
                'name' => 'guru_piket',
                'display_name' => 'Guru Piket',
                'description' => 'Pencatatan absensi gerbang masuk harian, rekap keterlambatan, dan izin siswa.',
            ],
            [
                'name' => 'siswa',
                'display_name' => 'Siswa',
                'description' => 'Melihat data pribadi, jadwal belajar, riwayat kehadiran, dan pemindaian QR sesi ibadah.',
            ],
            [
                'name' => 'orang_tua',
                'display_name' => 'Orang Tua / Wali',
                'description' => 'Portal transparansi untuk melihat kehadiran dan pengumuman anak yang terhubung sah.',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(['name' => $roleData['name']], $roleData);
        }
    }
}
