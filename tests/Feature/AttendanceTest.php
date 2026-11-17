<?php

namespace Tests\Feature;

use App\Models\AttendanceSession;
use App\Models\AttendanceType;
use App\Models\DailyAttendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_students_and_parents_cannot_access_attendance_management(): void
    {
        $siswaUser = User::where('username', 'siswa')->first();
        $ortuUser = User::where('username', 'orangtua')->first();

        $this->actingAs($siswaUser)->get('/attendance')->assertStatus(403);
        $this->actingAs($ortuUser)->get('/attendance')->assertStatus(403);
    }

    public function test_guru_piket_and_wali_kelas_can_access_attendance(): void
    {
        $piketUser = User::where('username', 'piket')->first();
        $waliKelasUser = User::where('username', 'walikelas')->first();

        $this->actingAs($piketUser)->get('/attendance')->assertStatus(200)->assertSee('Jurnal Presensi Harian Siswa');
        $this->actingAs($waliKelasUser)->get('/attendance/sheet')->assertStatus(200)->assertSee('Lembar Input Presensi Rombel');
    }

    public function test_batch_attendance_can_be_stored_for_a_class(): void
    {
        $piketUser = User::where('username', 'piket')->first();
        $class = SchoolClass::where('name', 'X-A')->first();
        $students = Student::where('current_class_id', $class->id)->get();
        $type = AttendanceType::where('code', 'masuk')->first();

        $today = Carbon::today()->format('Y-m-d');
        $session = AttendanceSession::firstOrCreate(
            ['attendance_type_id' => $type->id, 'date' => $today],
            ['academic_year_id' => $class->academic_year_id, 'status' => 'open', 'created_by' => $piketUser->id]
        );

        $payload = [];
        foreach ($students as $index => $st) {
            $payload[$st->id] = [
                'status' => $index === 0 ? 'late' : 'present',
                'minutes_late' => $index === 0 ? 20 : 0,
                'notes' => $index === 0 ? 'Macet perjalanan' : '',
            ];
        }

        $response = $this->actingAs($piketUser)->post('/attendance/batch', [
            'session_id' => $session->id,
            'attendances' => $payload,
        ]);

        $response->assertSessionHas('success');

        // Pastikan tersimpan di basis data
        $firstStudent = $students->first();
        $this->assertDatabaseHas('daily_attendances', [
            'attendance_session_id' => $session->id,
            'student_id' => $firstStudent->id,
            'status' => 'late',
            'minutes_late' => 20,
        ]);
    }

    public function test_duplicate_attendance_is_prevented_and_updated_cleanly(): void
    {
        $piketUser = User::where('username', 'piket')->first();
        $class = SchoolClass::where('name', 'X-A')->first();
        $student = Student::where('current_class_id', $class->id)->first();
        $type = AttendanceType::where('code', 'masuk')->first();

        $today = Carbon::today()->format('Y-m-d');
        $session = AttendanceSession::firstOrCreate(
            ['attendance_type_id' => $type->id, 'date' => $today],
            ['academic_year_id' => $class->academic_year_id, 'status' => 'open', 'created_by' => $piketUser->id]
        );

        // Input pertama: Hadir
        $this->actingAs($piketUser)->post('/attendance/batch', [
            'session_id' => $session->id,
            'attendances' => [
                $student->id => ['status' => 'present', 'notes' => 'Awal'],
            ],
        ]);

        $countInitial = DailyAttendance::where('attendance_session_id', $session->id)
            ->where('student_id', $student->id)
            ->count();

        $this->assertEquals(1, $countInitial);

        // Input kedua pada sesi yang sama: Alpa
        $this->actingAs($piketUser)->post('/attendance/batch', [
            'session_id' => $session->id,
            'attendances' => [
                $student->id => ['status' => 'absent', 'notes' => 'Diperbarui'],
            ],
        ]);

        $countAfter = DailyAttendance::where('attendance_session_id', $session->id)
            ->where('student_id', $student->id)
            ->count();

        // Jumlah baris tetap 1 (tidak duplikat!)
        $this->assertEquals(1, $countAfter);

        $record = DailyAttendance::where('attendance_session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();

        $this->assertEquals('absent', $record->status);
    }

    public function test_attendance_correction_records_audit_trail_with_reason(): void
    {
        $piketUser = User::where('username', 'piket')->first();
        $class = SchoolClass::where('name', 'X-A')->first();
        $student = Student::where('current_class_id', $class->id)->first();
        $type = AttendanceType::where('code', 'masuk')->first();

        $today = Carbon::today()->format('Y-m-d');
        $session = AttendanceSession::firstOrCreate(
            ['attendance_type_id' => $type->id, 'date' => $today],
            ['academic_year_id' => $class->academic_year_id, 'status' => 'open', 'created_by' => $piketUser->id]
        );

        $attendance = DailyAttendance::create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'date' => $today,
            'status' => 'absent',
            'recorded_by' => $piketUser->id,
        ]);

        // Lakukan koreksi dari alpa menjadi sakit dengan surat dokter
        $response = $this->actingAs($piketUser)->post("/attendance/{$attendance->id}/correct", [
            'new_status' => 'sick',
            'reason' => 'Surat keterangan sakit dari dokter diserahkan wali murid.',
        ]);

        $response->assertSessionHas('success');

        // Pastikan status diperbarui
        $attendance->refresh();
        $this->assertEquals('sick', $attendance->status);

        // Pastikan tercatat di tabel attendance_corrections
        $this->assertDatabaseHas('attendance_corrections', [
            'daily_attendance_id' => $attendance->id,
            'old_status' => 'absent',
            'new_status' => 'sick',
            'reason' => 'Surat keterangan sakit dari dokter diserahkan wali murid.',
        ]);
    }

    public function test_attendance_recap_page_is_accessible(): void
    {
        $adminUser = User::where('username', 'admin')->first();

        $response = $this->actingAs($adminUser)->get('/attendance/recap');
        $response->assertStatus(200);
        $response->assertSee('Rekapitulasi Kehadiran Siswa');
    }
}
