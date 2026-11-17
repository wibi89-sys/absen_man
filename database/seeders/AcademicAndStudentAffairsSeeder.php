<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\CounselingSession;
use App\Models\DisciplineCategory;
use App\Models\DisciplineRecord;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentActivity;
use App\Models\StudentActivityMember;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;

class AcademicAndStudentAffairsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();
        $academicYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::first();
        $semester = Semester::where('is_active', true)->first();
        $classes = SchoolClass::where('is_active', true)->get();
        $teachers = Teacher::where('status', 'active')->get();
        $students = Student::whereIn('status', ['active', 'aktif'])->get();

        // 1. Subjects
        $subjectsData = [
            ['code' => 'QUR-HADIS', 'name' => "Al-Qur'an Hadis", 'order' => 1],
            ['code' => 'AKIDAH-AKHLAK', 'name' => 'Akidah Akhlak', 'order' => 2],
            ['code' => 'FIKIH', 'name' => 'Fikih Ibadah & Muamalah', 'order' => 3],
            ['code' => 'SKI', 'name' => 'Sejarah Kebudayaan Islam', 'order' => 4],
            ['code' => 'B-ARAB', 'name' => 'Bahasa Arab', 'order' => 5],
            ['code' => 'MTK-WAJIB', 'name' => 'Matematika Wajib', 'order' => 6],
            ['code' => 'B-INDO', 'name' => 'Bahasa Indonesia', 'order' => 7],
            ['code' => 'B-ING', 'name' => 'Bahasa Inggris', 'order' => 8],
        ];

        $createdSubjects = [];
        foreach ($subjectsData as $s) {
            $createdSubjects[] = Subject::updateOrCreate(['code' => $s['code']], $s);
        }

        // 2. Class Subjects Assignment
        if ($classes->isNotEmpty() && $teachers->isNotEmpty() && $academicYear) {
            foreach ($classes->take(2) as $class) {
                foreach (array_slice($createdSubjects, 0, 4) as $index => $sub) {
                    $teacher = $teachers[$index % $teachers->count()];
                    ClassSubject::updateOrCreate(
                        [
                            'school_class_id' => $class->id,
                            'subject_id' => $sub->id,
                            'academic_year_id' => $academicYear->id,
                        ],
                        [
                            'teacher_id' => $teacher->id,
                            'semester_id' => $semester?->id,
                        ]
                    );
                }
            }
        }

        // 3. Discipline Categories
        $categoriesData = [
            ['name' => 'Keterlambatan Hadir (> 15 Menit)', 'type' => 'violation', 'points' => 5, 'description' => 'Siswa tiba di madrasah setelah bel masuk berbunyi.'],
            ['name' => 'Atribut Seragam Tidak Lengkap', 'type' => 'violation', 'points' => 5, 'description' => 'Tidak mengenakan peci, dasi, atau sepatu standar madrasah.'],
            ['name' => 'Meninggalkan Salat Berjamaah Tanpa Izin', 'type' => 'violation', 'points' => 15, 'description' => 'Tidak berada di shaf salat tanpa keterangan dispensasi yang sah.'],
            ['name' => 'Juara 1 Kompetisi Sains Madrasah (KSM)', 'type' => 'achievement', 'points' => 30, 'description' => 'Penghargaan prestasi akademik tingkat kabupaten/provinsi.'],
            ['name' => 'Hafalan Tahfiz 2 Juz Al-Qur\'an', 'type' => 'achievement', 'points' => 25, 'description' => 'Apresiasi kelulusan tasmi hafalan Al-Qur\'an.'],
            ['name' => 'Petugas Upacara / Adzan Teladan', 'type' => 'achievement', 'points' => 10, 'description' => 'Kedisiplinan dan kontribusi aktif kegiatan madrasah.'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[] = DisciplineCategory::updateOrCreate(['name' => $cat['name']], $cat);
        }

        // 4. Discipline Records
        if ($students->isNotEmpty()) {
            DisciplineRecord::updateOrCreate([
                'student_id' => $students[0]->id,
                'description' => 'Terlambat masuk gerbang madrasah 20 menit karena ban motor kempes.',
            ], [
                'category_id' => $categories[0]->id,
                'date' => now()->subDays(2)->toDateString(),
                'action_taken' => 'Teguran lisan dan pembinaan oleh guru piket.',
                'reported_by' => $admin->id,
                'status' => 'resolved',
                'points_awarded' => 5,
            ]);

            if ($students->count() > 1) {
                DisciplineRecord::updateOrCreate([
                    'student_id' => $students[1]->id,
                    'description' => 'Meraih Juara 1 Lomba Kaligrafi Islam tingkat Madrasah Aliyah.',
                ], [
                    'category_id' => $categories[3]->id,
                    'date' => now()->subDays(5)->toDateString(),
                    'action_taken' => 'Penyerahan piagam apresiasi saat upacara hari Senin.',
                    'reported_by' => $admin->id,
                    'status' => 'resolved',
                    'points_awarded' => 30,
                ]);
            }
        }

        // 5. Counseling Sessions (Strict Privacy)
        $counselor = Teacher::whereHas('user.roles', fn ($q) => $q->where('name', 'guru_bk'))->first() ?? $teachers->first();
        if ($students->isNotEmpty() && $counselor) {
            CounselingSession::updateOrCreate([
                'student_id' => $students[0]->id,
                'session_date' => now()->subDays(3)->toDateString(),
            ], [
                'counselor_id' => $counselor->id,
                'category' => 'belajar',
                'confidentiality_level' => 'strictly_confidential',
                'status' => 'completed',
                'case_background' => 'Siswa merasa kesulitan mengatur waktu belajar hafalan tahfiz dengan tugas matematika.',
                'action_plan' => 'Menyusun jadwal belajar terstruktur harian bersama guru pembimbing.',
                'recommendations' => 'Diberikan pendampingan tutor sebaya dan pemantauan mingguan.',
                'follow_up_date' => now()->addDays(7)->toDateString(),
            ]);
        }

        // 6. Student Activities (Ekskul & Organisasi)
        $activitiesData = [
            [
                'name' => 'Gerakan Pramuka Gudep MA One',
                'category' => 'ekstrakurikuler',
                'supervisor_id' => $teachers[0]->id ?? null,
                'day_of_week' => 5, // Jumat
                'start_time' => '14:30:00',
                'end_time' => '16:30:00',
                'location' => 'Lapangan Utama',
                'is_active' => true,
            ],
            [
                'name' => 'Organisasi Siswa Intra Madrasah (OSIM)',
                'category' => 'organisasi',
                'supervisor_id' => $teachers[1]->id ?? $teachers[0]->id ?? null,
                'day_of_week' => 3, // Rabu
                'start_time' => '15:00:00',
                'end_time' => '17:00:00',
                'location' => 'Ruang OSIM',
                'is_active' => true,
            ],
            [
                'name' => 'Palang Merah Remaja (PMR Wira)',
                'category' => 'ekstrakurikuler',
                'supervisor_id' => $teachers[0]->id ?? null,
                'day_of_week' => 4, // Kamis
                'start_time' => '15:00:00',
                'end_time' => '16:45:00',
                'location' => 'UKS Madrasah',
                'is_active' => true,
            ],
        ];

        foreach ($activitiesData as $act) {
            $activity = StudentActivity::updateOrCreate(['name' => $act['name']], $act);

            // Enroll students as members
            foreach ($students->take(3) as $st) {
                StudentActivityMember::updateOrCreate(
                    [
                        'student_activity_id' => $activity->id,
                        'student_id' => $st->id,
                    ],
                    [
                        'role' => 'anggota',
                        'joined_date' => now()->startOfYear()->toDateString(),
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
