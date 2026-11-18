<?php

namespace Database\Seeders;

use App\Models\SchoolSetting;
use Illuminate\Database\Seeder;

class SchoolSettingSeeder extends Seeder
{
    public function run(): void
    {
        SchoolSetting::firstOrCreate(
            ['id' => 1],
            [
                'school_name' => 'MADRASAH ONE',
                'school_slogan' => 'Integrated Student Monitoring System',
                'npsn' => '20261101',
                'nsm' => '131132730001',
                'school_level' => 'Madrasah Aliyah (MA)',
                'principal_name' => 'Dr. H. Ahmad Fauzi, M.Pd.',
                'principal_nip' => '197508152002121003',
                'school_address' => 'Jl. Pendidikan Karakter No. 99, Kota Bandung',
                'school_phone' => '(022) 7891234',
                'school_email' => 'info@madrasah-one.sch.id',
                'school_website' => 'https://absen.laksanatech.com',
                'enable_demo_mode' => false,
                'gate_welcome_text' => 'Selamat Datang di Madrasah Tercinta. Awali Hari dengan Senyum dan Semangat!',
            ]
        );
    }
}
