<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SchoolSettingSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            MasterDataSeeder::class,
            AttendanceTypeSeeder::class,
            WorshipSeeder::class,
            ParentPortalSeeder::class,
            AcademicAndStudentAffairsSeeder::class,
        ]);
    }
}
