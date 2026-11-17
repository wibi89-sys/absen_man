<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\ParentMessage;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class ParentPortalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();
        $parentUser = User::where('username', 'orangtua')->first();
        $waliKelasUser = User::where('username', 'walikelas')->first() ?? $admin;

        // 1. Seed Announcements
        Announcement::updateOrCreate([
            'title' => 'Jadwal Pelaksanaan Penilaian Akhir Semester (PAS) Ganjil',
        ], [
            'content' => 'Diberitahukan kepada seluruh Bapak/Ibu Orang Tua dan Wali Murid bahwa Penilaian Akhir Semester (PAS) Ganjil Tahun Ajaran 2026/2027 akan diselenggarakan mulai tanggal 1 Desember 2026. Mohon bimbingan belajar putra-putri di rumah dimaksimalkan.',
            'category' => 'academic',
            'target_role' => 'all',
            'is_pinned' => true,
            'published_at' => now()->subDay(),
            'created_by' => $admin->id,
        ]);

        Announcement::updateOrCreate([
            'title' => 'Kewajiban Salat Zuhur & Asar Berjamaah Terpadu',
        ], [
            'content' => 'Madrasah One menerapkan sistem absensi salat berjamaah berbasis QR dinamis. Orang tua dapat memantau keterlaksanaan ibadah harian putra-putrinya melalui menu Monitoring Ibadah pada portal ini.',
            'category' => 'worship',
            'target_role' => 'parents',
            'is_pinned' => false,
            'published_at' => now()->subHours(5),
            'created_by' => $admin->id,
        ]);

        Announcement::updateOrCreate([
            'title' => 'Ketertiban Jam Masuk Madrasah Pukul 06.45 WIB',
        ], [
            'content' => 'Gerbang madrasah ditutup pukul 07.00 WIB. Siswa yang hadir setelah pukul 06.45 WIB akan tercatat masbuk/terlambat pada jurnal piket madrasah.',
            'category' => 'attendance',
            'target_role' => 'parents',
            'is_pinned' => false,
            'published_at' => now()->subHours(2),
            'created_by' => $admin->id,
        ]);

        // 2. Verified Student-Parent Relationship for testing
        if ($parentUser) {
            $parentModel = ParentModel::firstOrCreate(
                ['user_id' => $parentUser->id],
                [
                    'nik' => '3271012345678901',
                    'full_name' => $parentUser->name,
                    'relationship_type' => 'ayah',
                    'phone' => '081234567890',
                ]
            );

            $students = Student::take(2)->get();
            if ($students->isNotEmpty()) {
                // Child 1: VERIFIED
                $parentModel->students()->syncWithoutDetaching([
                    $students[0]->id => [
                        'is_primary_contact' => true,
                        'is_verified' => true,
                        'verified_by' => $admin->id,
                        'verified_at' => now(),
                    ],
                ]);

                // Child 2: UNVERIFIED (Pending verification test)
                if ($students->count() > 1) {
                    $parentModel->students()->syncWithoutDetaching([
                        $students[1]->id => [
                            'is_primary_contact' => false,
                            'is_verified' => false,
                            'verified_by' => null,
                            'verified_at' => null,
                        ],
                    ]);
                }

                // 3. Parent Message from Wali Kelas
                ParentMessage::updateOrCreate([
                    'student_id' => $students[0]->id,
                    'parent_id' => $parentModel->id,
                    'title' => 'Apresiasi Kedisiplinan & Karakter Ibadah Siswa',
                ], [
                    'sender_id' => $waliKelasUser->id,
                    'message' => "Assalamu'alaikum Wr. Wb. Bapak/Ibu Wali Murid, kami mengapresiasi ananda yang selalu hadir tepat waktu dan aktif dalam salat berjamaah di masjid madrasah. Terus pertahankan semangat belajarnya.",
                    'category' => 'general',
                    'is_read' => false,
                ]);
            }
        }
    }
}
