<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\SchoolLocation;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use DatabaseTransactions;

    public function test_students_cannot_access_master_data_menu(): void
    {
        $siswaUser = User::where('username', 'siswa')->first();

        $response = $this->actingAs($siswaUser)->get('/master/students');
        $response->assertStatus(403);
    }

    public function test_parents_cannot_access_master_data_menu(): void
    {
        $ortuUser = User::where('username', 'orangtua')->first();

        $response = $this->actingAs($ortuUser)->get('/master/teachers');
        $response->assertStatus(403);
    }

    public function test_admin_can_view_students_list(): void
    {
        $adminUser = User::where('username', 'admin')->first();

        $response = $this->actingAs($adminUser)->get('/master/students');
        $response->assertStatus(200);
        $response->assertSee('Kelola Data Siswa');
    }

    public function test_admin_can_create_student(): void
    {
        $adminUser = User::where('username', 'admin')->first();
        $class = SchoolClass::first();

        $nisTest = '202699999';

        $response = $this->actingAs($adminUser)->post('/master/students', [
            'nis' => $nisTest,
            'nisn' => '0099999999',
            'full_name' => '[TEST] Siswa Uji Otomatis',
            'gender' => 'L',
            'current_class_id' => $class?->id,
            'status' => 'active',
            'enrollment_date' => '2026-07-15',
        ]);

        $response->assertRedirect('/master/students');
        $this->assertDatabaseHas('students', [
            'nis' => $nisTest,
            'full_name' => '[TEST] Siswa Uji Otomatis',
        ]);
    }

    public function test_duplicate_nis_is_rejected(): void
    {
        $adminUser = User::where('username', 'admin')->first();
        $existingStudent = Student::first();

        $response = $this->actingAs($adminUser)->post('/master/students', [
            'nis' => $existingStudent->nis,
            'full_name' => 'Siswa Duplikat',
            'gender' => 'L',
            'status' => 'active',
            'enrollment_date' => '2026-07-15',
        ]);

        $response->assertSessionHasErrors('nis');
    }

    public function test_leadership_dashboard_renders(): void
    {
        $kepsekUser = User::where('username', 'kepsek')->first();

        $response = $this->actingAs($kepsekUser)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Executive Monitoring Dashboard');
    }

    public function test_homeroom_dashboard_renders(): void
    {
        $waliKelasUser = User::where('username', 'walikelas')->first();

        $response = $this->actingAs($waliKelasUser)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Portal Wali Kelas');
    }

    public function test_student_dashboard_renders(): void
    {
        $siswaUser = User::where('username', 'siswa')->first();

        $response = $this->actingAs($siswaUser)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Portal Siswa');
    }

    public function test_parent_dashboard_renders(): void
    {
        $ortuUser = User::where('username', 'orangtua')->first();

        $response = $this->actingAs($ortuUser)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Portal Orang Tua');
    }

    public function test_admin_can_update_school_location(): void
    {
        $adminUser = User::where('username', 'admin')->first();
        $location = SchoolLocation::firstOrCreate(
            ['name' => 'Lokasi Uji CRUD'],
            [
                'location_type' => 'madrasah',
                'latitude' => -6.200000,
                'longitude' => 106.800000,
                'radius_meters' => 100,
                'is_active' => true,
            ]
        );

        $response = $this->actingAs($adminUser)->put("/master/locations/{$location->id}", [
            'name' => 'Kampus 2 Al-Ikhlas (Diperbarui)',
            'location_type' => 'classroom',
            'latitude' => -6.211111,
            'longitude' => 106.822222,
            'radius_meters' => 150,
            'capacity' => 450,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/master/locations');
        $this->assertDatabaseHas('school_locations', [
            'id' => $location->id,
            'name' => 'Kampus 2 Al-Ikhlas (Diperbarui)',
            'radius_meters' => 150,
        ]);
    }

    public function test_admin_can_delete_school_location(): void
    {
        $adminUser = User::where('username', 'admin')->first();
        $location = SchoolLocation::create([
            'name' => 'Lokasi Sementara Mau Dihapus',
            'location_type' => 'field',
            'latitude' => -6.222222,
            'longitude' => 106.833333,
            'radius_meters' => 80,
            'is_active' => true,
        ]);

        $response = $this->actingAs($adminUser)->delete("/master/locations/{$location->id}");
        $response->assertRedirect('/master/locations');
        $this->assertDatabaseMissing('school_locations', [
            'id' => $location->id,
        ]);
    }
}
