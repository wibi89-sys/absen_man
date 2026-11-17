<?php

namespace Database\Factories;

use App\Models\SchoolSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolSetting>
 */
class SchoolSettingFactory extends Factory
{
    protected $model = SchoolSetting::class;

    public function definition(): array
    {
        return [
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
            'logo_path' => null,
            'favicon_path' => null,
            'enable_demo_mode' => true,
            'gate_welcome_text' => 'Selamat Datang di Madrasah Tercinta. Awali Hari dengan Senyum dan Semangat!',
        ];
    }
}
