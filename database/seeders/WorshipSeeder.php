<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\SchoolLocation;
use App\Models\Student;
use App\Models\User;
use App\Models\WorshipExemption;
use App\Models\WorshipSchedule;
use App\Models\WorshipScheduleGroup;
use App\Models\WorshipType;
use App\Services\Worship\WorshipSessionService;
use Illuminate\Database\Seeder;

class WorshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Worship Types
        $zuhur = WorshipType::updateOrCreate(['code' => 'zuhur'], [
            'name' => 'Salat Zuhur Berjamaah',
            'is_mandatory' => true,
            'description' => 'Salat wajib Zuhur berjamaah terjadwal dalam beberapa gelombang.',
        ]);

        $asar = WorshipType::updateOrCreate(['code' => 'asar'], [
            'name' => 'Salat Asar Berjamaah',
            'is_mandatory' => true,
            'description' => 'Salat wajib Asar berjamaah bagi peserta kegiatan sore dan ekstrakurikuler.',
        ]);

        $dhuha = WorshipType::updateOrCreate(['code' => 'dhuha'], [
            'name' => 'Salat Dhuha Bersama',
            'is_mandatory' => false,
            'description' => 'Pembiasaan salat dhuha pagi sebelum kegiatan pembelajaran.',
        ]);

        // 2. Schedules & Groups
        $academicYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::first();
        $location = SchoolLocation::first();
        $classes = SchoolClass::where('is_active', true)->get();
        $admin = User::first();

        if ($academicYear && $classes->count() >= 2 && $admin) {
            $schedule = WorshipSchedule::updateOrCreate([
                'worship_type_id' => $zuhur->id,
                'academic_year_id' => $academicYear->id,
                'name' => 'Jadwal Reguler Zuhur Berjamaah',
                'day_of_week' => 1, // Senin
            ], [
                'start_time' => '12:00:00',
                'end_time' => '12:45:00',
                'is_active' => true,
            ]);

            // Wave 1
            WorshipScheduleGroup::updateOrCreate([
                'worship_schedule_id' => $schedule->id,
                'school_class_id' => $classes[0]->id,
            ], [
                'wave_number' => 1,
                'location_id' => $location?->id ?? 1,
            ]);

            // Wave 2
            WorshipScheduleGroup::updateOrCreate([
                'worship_schedule_id' => $schedule->id,
                'school_class_id' => $classes[1]->id,
            ], [
                'wave_number' => 2,
                'location_id' => $location?->id ?? 1,
            ]);

            // 3. Seed an active exemption for a female student
            $femaleStudent = Student::where('gender', 'P')->first() ?? Student::first();
            if ($femaleStudent) {
                WorshipExemption::updateOrCreate([
                    'student_id' => $femaleStudent->id,
                    'start_date' => now()->subDay()->toDateString(),
                ], [
                    'end_date' => now()->addDays(5)->toDateString(),
                    'reason_category' => 'sharia_halangan',
                    'notes' => 'Dispensasi halangan syar\'i siswi terverifikasi oleh petugas keputrian.',
                    'approved_by' => $admin->id,
                    'is_confidential' => true,
                ]);
            }

            // 4. Seed today's session via WorshipSessionService
            $sessionService = app(WorshipSessionService::class);
            $sessionService->createSession(
                $zuhur->id,
                now()->toDateString(),
                1,
                $location?->id,
                [$classes[0]->id, $classes[1]->id],
                $admin->id,
                $schedule->id,
                'Sesi Zuhur Reguler Hari Ini (Gelombang 1)'
            );
        }
    }
}
