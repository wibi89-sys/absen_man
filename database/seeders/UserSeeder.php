<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('password123');

        $users = [
            [
                'name' => '[DUMMY] Super Admin Sistem',
                'email' => 'superadmin@madrasah.test',
                'username' => 'superadmin',
                'role' => 'super_admin',
            ],
            [
                'name' => '[DUMMY] Staf Tata Usaha Admin',
                'email' => 'admin@madrasah.test',
                'username' => 'admin',
                'role' => 'admin',
            ],
            [
                'name' => '[DUMMY] Drs. H. Ahmad Dahlan, M.Pd.',
                'email' => 'kepsek@madrasah.test',
                'username' => 'kepsek',
                'role' => 'kepsek',
            ],
            [
                'name' => '[DUMMY] Ust. Ridwan Kamil, M.Ag.',
                'email' => 'waka@madrasah.test',
                'username' => 'waka',
                'role' => 'waka',
            ],
            [
                'name' => '[DUMMY] Siti Aminah, S.Ag.',
                'email' => 'keagamaan@madrasah.test',
                'username' => 'keagamaan',
                'role' => 'petugas_keagamaan',
            ],
            [
                'name' => '[DUMMY] Nurul Hidayati, S.Psi.',
                'email' => 'gurubk@madrasah.test',
                'username' => 'gurubk',
                'role' => 'guru_bk',
            ],
            [
                'name' => '[DUMMY] Budi Santoso, S.Pd.',
                'email' => 'walikelas@madrasah.test',
                'username' => 'walikelas',
                'role' => 'wali_kelas',
            ],
            [
                'name' => '[DUMMY] Hendra Wijaya, S.Kom.',
                'email' => 'piket@madrasah.test',
                'username' => 'piket',
                'role' => 'guru_piket',
            ],
            [
                'name' => '[DUMMY] Muhammad Yusuf, S.Si.',
                'email' => 'guru@madrasah.test',
                'username' => 'guru',
                'role' => 'guru',
            ],
            [
                'name' => '[DUMMY] Ahmad Fauzan (Siswa X-A)',
                'email' => 'siswa@madrasah.test',
                'username' => 'siswa',
                'role' => 'siswa',
            ],
            [
                'name' => '[DUMMY] Bapak H. Rahmat (Wali Ahmad)',
                'email' => 'orangtua@madrasah.test',
                'username' => 'orangtua',
                'role' => 'orang_tua',
            ],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'username' => $data['username'],
                    'password' => $defaultPassword,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $role = Role::where('name', $data['role'])->first();
            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }

            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'gender' => in_array($data['role'], ['petugas_keagamaan', 'guru_bk']) ? 'P' : 'L',
                    'birth_place' => 'Jakarta',
                    'birth_date' => '1985-05-15',
                    'address' => 'Jl. Madrasah No. 12, Kompleks Pendidikan',
                ]
            );
        }
    }
}
