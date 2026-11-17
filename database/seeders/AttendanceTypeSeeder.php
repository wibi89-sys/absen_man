<?php

namespace Database\Seeders;

use App\Models\AttendanceType;
use Illuminate\Database\Seeder;

class AttendanceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'code' => 'masuk',
                'name' => 'Presensi Pagi / Masuk',
                'default_start_time' => '06:30:00',
                'default_end_time' => '07:15:00',
                'late_tolerance_minutes' => 15,
                'is_active' => true,
            ],
            [
                'code' => 'pulang',
                'name' => 'Presensi Kepulangan',
                'default_start_time' => '15:00:00',
                'default_end_time' => '16:30:00',
                'late_tolerance_minutes' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'apel',
                'name' => 'Apel / Upacara Bendera',
                'default_start_time' => '06:45:00',
                'default_end_time' => '07:30:00',
                'late_tolerance_minutes' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($types as $t) {
            AttendanceType::updateOrCreate(['code' => $t['code']], $t);
        }
    }
}
