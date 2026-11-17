<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\ParentModel;
use App\Models\SchoolClass;
use App\Models\SchoolLocation;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentClassHistory;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tahun Ajaran & Semester
        $academicYear = AcademicYear::updateOrCreate(
            ['name' => '2026/2027'],
            [
                'start_date' => '2026-07-15',
                'end_date' => '2027-06-25',
                'is_active' => true,
            ]
        );

        $sem1 = Semester::updateOrCreate(
            ['academic_year_id' => $academicYear->id, 'semester' => '1'],
            ['is_active' => true]
        );

        $sem2 = Semester::updateOrCreate(
            ['academic_year_id' => $academicYear->id, 'semester' => '2'],
            ['is_active' => false]
        );

        // 2. Tingkat (Grades)
        $grade10 = Grade::updateOrCreate(['level' => 10], ['name' => 'X']);
        $grade11 = Grade::updateOrCreate(['level' => 11], ['name' => 'XI']);
        $grade12 = Grade::updateOrCreate(['level' => 12], ['name' => 'XII']);

        // 3. Lokasi
        $locations = [
            ['name' => 'Masjid Utama Al-Ikhlas', 'location_type' => 'masjid', 'capacity' => 500],
            ['name' => 'Musholla Putri Khadijah', 'location_type' => 'musholla', 'capacity' => 350],
            ['name' => 'Ruang Kelas X-A', 'location_type' => 'classroom', 'capacity' => 36],
            ['name' => 'Ruang Kelas XI-MIPA', 'location_type' => 'classroom', 'capacity' => 36],
            ['name' => 'Lapangan Upacara Madrasah', 'location_type' => 'field', 'capacity' => 1000],
            ['name' => 'Ruang Bimbingan Konseling (BK)', 'location_type' => 'counseling_room', 'capacity' => 6],
        ];

        foreach ($locations as $loc) {
            SchoolLocation::updateOrCreate(['name' => $loc['name']], $loc);
        }

        // 4. Guru
        $walikelasUser = User::where('email', 'walikelas@madrasah.test')->first();
        $keagamaanUser = User::where('email', 'keagamaan@madrasah.test')->first();
        $gurubkUser = User::where('email', 'gurubk@madrasah.test')->first();
        $guruUser = User::where('email', 'guru@madrasah.test')->first();
        $piketUser = User::where('email', 'piket@madrasah.test')->first();

        $budiTeacher = Teacher::updateOrCreate(
            ['nip' => '198401152010011002'],
            [
                'user_id' => $walikelasUser?->id,
                'full_name' => 'Budi Santoso',
                'title_prefix' => null,
                'title_suffix' => 'S.Pd.',
                'phone' => '081234567891',
                'status' => 'active',
            ]
        );

        $sitiTeacher = Teacher::updateOrCreate(
            ['nip' => '198705202014022001'],
            [
                'user_id' => $keagamaanUser?->id,
                'full_name' => 'Siti Aminah',
                'title_prefix' => 'Usth.',
                'title_suffix' => 'S.Ag.',
                'phone' => '081234567892',
                'status' => 'active',
            ]
        );

        $nurulTeacher = Teacher::updateOrCreate(
            ['nip' => '199011122018032003'],
            [
                'user_id' => $gurubkUser?->id,
                'full_name' => 'Nurul Hidayati',
                'title_prefix' => null,
                'title_suffix' => 'S.Psi.',
                'phone' => '081234567893',
                'status' => 'active',
            ]
        );

        $yusufTeacher = Teacher::updateOrCreate(
            ['nip' => '198902032015041004'],
            [
                'user_id' => $guruUser?->id,
                'full_name' => 'Muhammad Yusuf',
                'title_prefix' => null,
                'title_suffix' => 'S.Si.',
                'phone' => '081234567894',
                'status' => 'active',
            ]
        );

        $hendraTeacher = Teacher::updateOrCreate(
            ['nip' => '199208192019011005'],
            [
                'user_id' => $piketUser?->id,
                'full_name' => 'Hendra Wijaya',
                'title_prefix' => null,
                'title_suffix' => 'S.Kom.',
                'phone' => '081234567895',
                'status' => 'active',
            ]
        );

        // 5. Rombongan Belajar (Kelas)
        $classXA = SchoolClass::updateOrCreate(
            ['academic_year_id' => $academicYear->id, 'name' => 'X-A'],
            [
                'grade_id' => $grade10->id,
                'homeroom_teacher_id' => $budiTeacher->id,
                'capacity' => 36,
                'is_active' => true,
            ]
        );

        $classXB = SchoolClass::updateOrCreate(
            ['academic_year_id' => $academicYear->id, 'name' => 'X-B'],
            [
                'grade_id' => $grade10->id,
                'homeroom_teacher_id' => $yusufTeacher->id,
                'capacity' => 36,
                'is_active' => true,
            ]
        );

        $classXIMIPA = SchoolClass::updateOrCreate(
            ['academic_year_id' => $academicYear->id, 'name' => 'XI-MIPA 1'],
            [
                'grade_id' => $grade11->id,
                'homeroom_teacher_id' => $sitiTeacher->id,
                'capacity' => 36,
                'is_active' => true,
            ]
        );

        // 6. Siswa
        $siswaUser = User::where('email', 'siswa@madrasah.test')->first();

        $primaryStudent = Student::updateOrCreate(
            ['nis' => '202610001'],
            [
                'user_id' => $siswaUser?->id,
                'nisn' => '0089123451',
                'full_name' => '[DUMMY] Ahmad Fauzan',
                'nickname' => 'Fauzan',
                'gender' => 'L',
                'current_class_id' => $classXA->id,
                'status' => 'active',
                'enrollment_date' => '2026-07-15',
            ]
        );

        StudentClassHistory::updateOrCreate(
            [
                'student_id' => $primaryStudent->id,
                'school_class_id' => $classXA->id,
                'academic_year_id' => $academicYear->id,
                'semester_id' => $sem1->id,
            ],
            ['enrolled_at' => '2026-07-15']
        );

        // Tambahkan siswa contoh lainnya untuk kelas X-A dan XI-MIPA
        $sampleStudents = [
            ['nis' => '202610002', 'nisn' => '0089123452', 'name' => '[DUMMY] Bilqis Zahrani', 'gender' => 'P', 'class' => $classXA->id],
            ['nis' => '202610003', 'nisn' => '0089123453', 'name' => '[DUMMY] Chairul Anam', 'gender' => 'L', 'class' => $classXA->id],
            ['nis' => '202610004', 'nisn' => '0089123454', 'name' => '[DUMMY] Dewi Sartika Putri', 'gender' => 'P', 'class' => $classXA->id],
            ['nis' => '202610005', 'nisn' => '0089123455', 'name' => '[DUMMY] Eko Prasetyo', 'gender' => 'L', 'class' => $classXA->id],
            ['nis' => '202511001', 'nisn' => '0078123461', 'name' => '[DUMMY] Farhan Maulana', 'gender' => 'L', 'class' => $classXIMIPA->id],
            ['nis' => '202511002', 'nisn' => '0078123462', 'name' => '[DUMMY] Ghina Nafisah', 'gender' => 'P', 'class' => $classXIMIPA->id],
            ['nis' => '202511003', 'nisn' => '0078123463', 'name' => '[DUMMY] Hafiz Ramadhan', 'gender' => 'L', 'class' => $classXIMIPA->id],
        ];

        foreach ($sampleStudents as $s) {
            $createdStudent = Student::updateOrCreate(
                ['nis' => $s['nis']],
                [
                    'nisn' => $s['nisn'],
                    'full_name' => $s['name'],
                    'gender' => $s['gender'],
                    'current_class_id' => $s['class'],
                    'status' => 'active',
                    'enrollment_date' => '2026-07-15',
                ]
            );

            StudentClassHistory::updateOrCreate(
                [
                    'student_id' => $createdStudent->id,
                    'school_class_id' => $s['class'],
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $sem1->id,
                ],
                ['enrolled_at' => '2026-07-15']
            );
        }

        // 7. Orang Tua & Hubungan Sah dengan Siswa
        $ortuUser = User::where('email', 'orangtua@madrasah.test')->first();

        $parent = ParentModel::updateOrCreate(
            ['user_id' => $ortuUser?->id],
            [
                'nik' => '3171012345670001',
                'full_name' => '[DUMMY] H. Rahmat Hidayat',
                'relationship_type' => 'ayah',
                'occupation' => 'Wiraswasta',
                'phone' => '081298765432',
                'address' => 'Jl. Kompleks Pendidikan No. 45, Jakarta',
            ]
        );

        // Verifikasi relasi anak-ortu
        $parent->students()->syncWithoutDetaching([
            $primaryStudent->id => [
                'is_primary_contact' => true,
                'is_verified' => true,
                'verified_by' => User::where('username', 'admin')->value('id'),
                'verified_at' => now(),
            ],
        ]);
    }
}
