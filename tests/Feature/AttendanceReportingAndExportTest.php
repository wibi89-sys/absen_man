<?php

namespace Tests\Feature;

use App\Models\ParentModel;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AttendanceReportingAndExportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_authorized_user_can_view_attendance_recap(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('attendance.recap'));

        $response->assertStatus(200);
        $response->assertSee('Rekapitulasi Kehadiran Siswa');
        $response->assertSee('Cetak Rekap Resmi');
        $response->assertSee('Ekspor CSV');
    }

    public function test_authorized_user_can_view_printable_attendance_recap(): void
    {
        $admin = User::where('username', 'admin')->first();
        $class = SchoolClass::first();

        $response = $this->actingAs($admin)->get(route('attendance.recap.print', [
            'class_id' => $class?->id,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertSee('LAPORAN REKAPITULASI PRESENSI KEHADIRAN SISWA');
        $response->assertSee('KEMENTERIAN AGAMA REPUBLIK INDONESIA');
        $response->assertSee('Kepala Madrasah');
    }

    public function test_authorized_user_can_export_attendance_recap_csv(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('attendance.recap.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertTrue(str_contains($response->headers->get('content-disposition') ?? '', 'rekap_presensi'));
    }

    public function test_authorized_user_can_view_printable_worship_recap(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('worship.recap.print', [
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertSee('LAPORAN MONITORING IBADAH');
        $response->assertSee('KEMENTERIAN AGAMA REPUBLIK INDONESIA');
        $response->assertSee('Kepala Madrasah');
    }

    public function test_student_model_provides_clean_primary_parent_whatsapp_phone(): void
    {
        $student = Student::create([
            'nis' => '99999999',
            'full_name' => 'Siswa Uji Coba WA',
            'gender' => 'L',
            'status' => 'active',
            'enrollment_date' => now()->toDateString(),
        ]);

        $parent = ParentModel::create([
            'nik' => '3201123456780001',
            'full_name' => 'Bapak Budi Santoso',
            'phone' => '0812-3456-7890',
            'relationship_type' => 'ayah',
        ]);

        $student->parents()->syncWithoutDetaching([
            $parent->id => [
                'is_primary_contact' => true,
                'is_verified' => true,
            ],
        ]);

        $contact = $student->getPrimaryParentContact();

        $this->assertNotNull($contact);
        $this->assertEquals('6281234567890', $contact['phone']);
        $this->assertEquals('Bapak Budi Santoso', $contact['name']);
    }
}
