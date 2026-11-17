<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AttendanceMachine;
use App\Models\AttendanceSession;
use App\Models\AttendanceType;
use App\Models\SchoolLocation;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MultiMethodAttendanceTest extends TestCase
{
    use DatabaseTransactions;

    protected User $studentUser;

    protected User $piketUser;

    protected User $adminUser;

    protected Student $student;

    protected SchoolLocation $schoolLocation;

    protected AttendanceSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studentUser = User::where('username', 'siswa')->first();
        $this->piketUser = User::where('username', 'piket')->first();
        $this->adminUser = User::where('username', 'admin')->first();

        $this->student = Student::where('user_id', $this->studentUser->id)->first() ?? Student::first();
        $this->student->update([
            'rfid_uid' => 'NFC_TEST_UID_9999',
            'qr_token' => 'QR_TEST_TOKEN_1234567890',
        ]);

        // Setup Lokasi Madrasah: Monas Jakarta (-6.175392, 106.827153) dengan radius 100m
        $this->schoolLocation = SchoolLocation::firstOrCreate(
            ['name' => 'Lokasi Utama Kampus Madrasah'],
            [
                'location_type' => 'madrasah',
                'latitude' => -6.175392,
                'longitude' => 106.827153,
                'radius_meters' => 100,
                'is_active' => true,
            ]
        );
        $this->schoolLocation->update([
            'latitude' => -6.175392,
            'longitude' => 106.827153,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $type = AttendanceType::firstOrCreate(
            ['code' => 'masuk'],
            [
                'name' => 'Presensi Masuk Pagi',
                'default_start_time' => '06:30:00',
                'default_end_time' => '07:15:00',
                'late_tolerance_minutes' => 15,
                'is_active' => true,
            ]
        );

        $activeYear = AcademicYear::first();

        $this->session = AttendanceSession::firstOrCreate(
            [
                'attendance_type_id' => $type->id,
                'date' => now()->toDateString(),
            ],
            [
                'academic_year_id' => $activeYear?->id ?? 1,
                'title' => 'Presensi Masuk Pagi Uji Coba',
                'status' => 'open',
                'created_by' => $this->adminUser->id,
            ]
        );
    }

    /**
     * Metode 1: Presensi GPS Mandiri Siswa (Diterima jika di dalam radius, Ditolak jika di luar radius).
     */
    public function test_method_1_self_gps_attendance_validates_radius(): void
    {
        $schoolLoc = SchoolLocation::where('is_active', true)->whereNotNull('latitude')->first();
        $baseLat = $schoolLoc ? (float) $schoolLoc->latitude : -6.2088;
        $baseLon = $schoolLoc ? (float) $schoolLoc->longitude : 106.8456;

        // Posisi di luar radius: jarak ~5.5 km
        $outsideResponse = $this->actingAs($this->studentUser)
            ->postJson(route('attendance.self-gps'), [
                'latitude' => $baseLat + 0.05,
                'longitude' => $baseLon,
                'accuracy' => 10,
            ]);

        $outsideResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
        $this->assertStringContainsString('di luar radius madrasah', $outsideResponse->json('message'));

        // Posisi di dalam radius: tepat di titik koordinat madrasah (jarak 0 meter)
        $insideResponse = $this->actingAs($this->studentUser)
            ->postJson(route('attendance.self-gps'), [
                'latitude' => $baseLat,
                'longitude' => $baseLon,
                'accuracy' => 5,
            ]);

        $insideResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('daily_attendances', [
            'student_id' => $this->student->id,
            'source' => 'self_mobile_gps',
        ]);
    }

    /**
     * Metode 2: Pindai Kartu OSIS (QR Kartu Pelajar & Barcode Gun) di Pos Gerbang Kiosk.
     */
    public function test_method_2_gate_kiosk_qr_and_barcode_scanning(): void
    {
        // 2A. Guru Piket scan QR Kartu OSIS Siswa
        $qrResponse = $this->actingAs($this->piketUser)
            ->postJson(route('attendance.gate-scanner.process'), [
                'identifier' => 'QR_TEST_TOKEN_1234567890',
                'method' => 'qr_card',
            ]);

        $qrResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('daily_attendances', [
            'student_id' => $this->student->id,
            'source' => 'student_qr_scan',
        ]);

        // 2B. Barcode Gun scan NIS Siswa
        $barcodeResponse = $this->actingAs($this->piketUser)
            ->postJson(route('attendance.gate-scanner.process'), [
                'identifier' => $this->student->nis,
                'method' => 'barcode_nis',
            ]);

        $barcodeResponse->assertStatus(200);
    }

    /**
     * Metode 2B: Pindai Kartu OSIS Digital QR Dinamis (Anti-Screenshot/Anti-Titip Absen).
     */
    public function test_method_2b_dynamic_student_osis_qr_card_rotation_and_anti_replay(): void
    {
        // 1. Siswa request dynamic token untuk kartu OSIS digitalnya
        $tokenRes = $this->actingAs($this->studentUser)
            ->getJson(route('attendance.student-osis-token'));

        $tokenRes->assertStatus(200);
        $dynamicToken = $tokenRes->json('token');
        $this->assertStringStartsWith('STDYN.', $dynamicToken);
        $this->assertGreaterThan(0, $tokenRes->json('remaining_seconds'));

        // 2. Guru piket scan dynamic token kartu OSIS di pos gerbang
        $scanRes = $this->actingAs($this->piketUser)
            ->postJson(route('attendance.gate-scanner.process'), [
                'identifier' => $dynamicToken,
                'method' => 'qr_card',
            ]);

        $scanRes->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('daily_attendances', [
            'student_id' => $this->student->id,
            'source' => 'dynamic_student_qr_card',
        ]);

        // 3. Scan ulang token yang sama (Replay attack / screenshot dibagikan ke teman) harus DITOLAK
        $replayRes = $this->actingAs($this->piketUser)
            ->postJson(route('attendance.gate-scanner.process'), [
                'identifier' => $dynamicToken,
                'method' => 'qr_card',
            ]);

        $replayRes->assertStatus(422);

        // 4. Token yang dimanipulasi hash-nya harus DITOLAK
        $tamperedRes = $this->actingAs($this->piketUser)
            ->postJson(route('attendance.gate-scanner.process'), [
                'identifier' => 'STDYN.'.$this->student->id.'.99999999.invalidhash',
                'method' => 'qr_card',
            ]);

        $tamperedRes->assertStatus(422);
    }

    /**
     * Metode 3: Layar TV QR Dinamis di Gerbang yang dipindai kamera HP siswa.
     */
    public function test_method_3_dynamic_rotating_qr_display_and_mobile_scan(): void
    {
        // Guru piket / proyektor buka layar token dinamis
        $tokenResponse = $this->actingAs($this->piketUser)
            ->getJson(route('attendance.dynamic-qr-token'));

        $tokenResponse->assertStatus(200);
        $token = $tokenResponse->json('token');
        $this->assertNotEmpty($token);

        // Siswa memindai token QR dinamis yang valid
        $scanResponse = $this->actingAs($this->studentUser)
            ->postJson(route('attendance.scan-dynamic-qr'), [
                'token' => $token,
            ]);

        $scanResponse->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('daily_attendances', [
            'student_id' => $this->student->id,
            'source' => 'dynamic_qr_scan',
        ]);

        // Token palsu / diutak-atik harus DITOLAK 422
        $fakeResponse = $this->actingAs($this->studentUser)
            ->postJson(route('attendance.scan-dynamic-qr'), [
                'token' => 'TOKEN_PALSU_BUATAN_SISWA_ISENG',
            ]);

        $fakeResponse->assertStatus(422);
    }

    /**
     * Metode 4: Tap Kartu NFC / RFID Pelajar.
     */
    public function test_method_4_nfc_card_tap_attendance(): void
    {
        $nfcResponse = $this->actingAs($this->piketUser)
            ->postJson(route('attendance.gate-scanner.process'), [
                'identifier' => 'NFC_TEST_UID_9999',
                'method' => 'nfc_uid',
            ]);

        $nfcResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('daily_attendances', [
            'student_id' => $this->student->id,
            'source' => 'nfc_tap',
        ]);
    }

    /**
     * Metode 5: Mesin Absensi Biometrik Solution X100C via LAN ADMS Push.
     */
    public function test_method_5_lan_biometric_machine_solution_x100c_sync(): void
    {
        // 5A. Daftarkan mesin di Master Data
        $machine = AttendanceMachine::create([
            'name' => 'Solution X100C Gerbang Utama',
            'ip_address' => '192.168.1.201',
            'port' => 4370,
            'protocol' => 'adms_http',
            'location_name' => 'Pos Satpam Gerbang Depan',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('attendance_machines', ['ip_address' => '192.168.1.201']);

        // 5B. Mesin Solution X100C mem-push log kehadiran via ADMS HTTP POST
        $admsPayload = "PIN={$this->student->nis}\tTIME=2026-11-17 06:45:10\tSTATUS=0\n";

        $response = $this->call(
            'POST',
            '/api/machine/cdata',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain'],
            $admsPayload
        );

        $response->assertStatus(200);
        $this->assertStringContainsString('OK: 1', $response->getContent());

        $this->assertDatabaseHas('daily_attendances', [
            'student_id' => $this->student->id,
            'source' => 'machine_lan',
        ]);
    }
}
