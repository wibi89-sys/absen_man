<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Models\WorshipExemption;
use App\Models\WorshipSessionParticipant;
use App\Models\WorshipType;
use App\Services\Worship\WorshipQrService;
use App\Services\Worship\WorshipSessionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WorshipTest extends TestCase
{
    use DatabaseTransactions;

    protected User $petugasUser;

    protected User $adminUser;

    protected User $siswaUser;

    protected User $guruUser;

    protected WorshipType $zuhurType;

    protected SchoolClass $classA;

    protected SchoolClass $classB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->petugasUser = User::where('username', 'keagamaan')->first() ?? User::where('email', 'keagamaan@madrasah.test')->first();
        $this->adminUser = User::where('username', 'admin')->first() ?? User::where('email', 'admin@madrasah.test')->first();
        $this->siswaUser = User::where('username', 'siswa')->first() ?? User::where('email', 'siswa@madrasah.test')->first();
        $this->guruUser = User::where('username', 'guru')->first() ?? User::where('email', 'guru@madrasah.test')->first();

        $this->zuhurType = WorshipType::firstOrCreate(['code' => 'zuhur'], [
            'name' => 'Salat Zuhur Berjamaah',
            'is_mandatory' => true,
        ]);

        $this->classA = SchoolClass::first();
        $this->classB = SchoolClass::skip(1)->first() ?? $this->classA;
    }

    public function test_worship_session_locks_deterministic_denominator_on_creation(): void
    {
        $sessionService = app(WorshipSessionService::class);

        $expectedStudents = Student::where('current_class_id', $this->classA->id)
            ->whereIn('status', ['active', 'aktif'])
            ->get();

        $session = $sessionService->createSession(
            $this->zuhurType->id,
            '2026-11-20',
            1,
            null,
            [$this->classA->id],
            $this->petugasUser->id,
            null,
            'Test Denominator Lock'
        );

        $this->assertDatabaseHas('worship_sessions', ['id' => $session->id]);
        $this->assertEquals($expectedStudents->count(), $session->participants()->count());

        foreach ($expectedStudents as $st) {
            $this->assertDatabaseHas('worship_session_participants', [
                'worship_session_id' => $session->id,
                'student_id' => $st->id,
            ]);
        }
    }

    public function test_active_exemption_automatically_marks_participant_as_exempt(): void
    {
        $femaleStudent = Student::where('current_class_id', $this->classA->id)->first();
        $date = '2026-11-21';

        // Register active sensitive exemption for this date
        WorshipExemption::create([
            'student_id' => $femaleStudent->id,
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-25',
            'reason_category' => 'sharia_halangan',
            'notes' => 'Catatan pribadi siswi halangan syar\'i',
            'approved_by' => $this->adminUser->id,
            'is_confidential' => true,
        ]);

        $sessionService = app(WorshipSessionService::class);
        $session = $sessionService->createSession(
            $this->zuhurType->id,
            $date,
            1,
            null,
            [$this->classA->id],
            $this->petugasUser->id
        );

        $participant = WorshipSessionParticipant::where('worship_session_id', $session->id)
            ->where('student_id', $femaleStudent->id)
            ->first();

        $this->assertNotNull($participant);
        $this->assertEquals('exempt', $participant->status);
    }

    public function test_dynamic_qr_token_generation_and_rotation(): void
    {
        $sessionService = app(WorshipSessionService::class);
        $session = $sessionService->createSession(
            $this->zuhurType->id,
            '2026-11-22',
            1,
            null,
            [$this->classA->id],
            $this->petugasUser->id
        );

        $qrService = app(WorshipQrService::class);
        $token1 = $session->activeQrToken;
        $this->assertNotNull($token1);
        $this->assertTrue($token1->isValid());

        // Generate second token -> rotates and invalidates token 1
        $token2 = $qrService->generateToken($session);
        $this->assertNotEquals($token1->token, $token2->token);
        $this->assertFalse($token1->fresh()->is_active);
        $this->assertTrue($token2->fresh()->is_active);
    }

    public function test_student_can_scan_valid_qr_and_records_attendance(): void
    {
        $sessionService = app(WorshipSessionService::class);
        $session = $sessionService->createSession(
            $this->zuhurType->id,
            '2026-11-23',
            1,
            null,
            [$this->classA->id],
            $this->petugasUser->id
        );

        $student = Student::where('current_class_id', $this->classA->id)
            ->whereIn('status', ['active', 'aktif'])
            ->first();

        $token = $session->activeQrToken;

        $response = $this->actingAs($this->siswaUser)->postJson("/worship/sessions/{$session->id}/scan", [
            'token' => $token->token,
            'identifier' => $student->nis,
            'source' => 'qr_student_camera',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('worship_attendances', [
            'worship_session_id' => $session->id,
            'student_id' => $student->id,
            'source' => 'qr_student_camera',
        ]);

        $participant = WorshipSessionParticipant::where('worship_session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();

        $this->assertEquals('present', $participant->status);
        $this->assertNotNull($participant->scan_record_id);
    }

    public function test_replay_scan_is_rejected(): void
    {
        $sessionService = app(WorshipSessionService::class);
        $session = $sessionService->createSession(
            $this->zuhurType->id,
            '2026-11-24',
            1,
            null,
            [$this->classA->id],
            $this->petugasUser->id
        );

        $student = Student::where('current_class_id', $this->classA->id)
            ->whereIn('status', ['active', 'aktif'])
            ->first();

        $token = $session->activeQrToken;

        // First scan succeeds
        $this->actingAs($this->siswaUser)->postJson("/worship/sessions/{$session->id}/scan", [
            'token' => $token->token,
            'identifier' => $student->nis,
            'source' => 'qr_student_camera',
        ])->assertStatus(200);

        // Second scan with same student in same session must be rejected (anti-replay)
        $secondResponse = $this->actingAs($this->siswaUser)->postJson("/worship/sessions/{$session->id}/scan", [
            'token' => $token->token,
            'identifier' => $student->nis,
            'source' => 'qr_student_camera',
        ]);

        $secondResponse->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_expired_qr_token_is_rejected(): void
    {
        $sessionService = app(WorshipSessionService::class);
        $session = $sessionService->createSession(
            $this->zuhurType->id,
            '2026-11-25',
            1,
            null,
            [$this->classA->id],
            $this->petugasUser->id
        );

        $student = Student::where('current_class_id', $this->classA->id)
            ->whereIn('status', ['active', 'aktif'])
            ->first();

        // Expire token
        $token = $session->activeQrToken;
        $token->update([
            'valid_from' => now()->subMinutes(10),
            'valid_until' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($this->siswaUser)->postJson("/worship/sessions/{$session->id}/scan", [
            'token' => $token->token,
            'identifier' => $student->nis,
            'source' => 'qr_student_camera',
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_student_not_in_denominator_is_rejected(): void
    {
        $sessionService = app(WorshipSessionService::class);
        // Create session only for classA
        $session = $sessionService->createSession(
            $this->zuhurType->id,
            '2026-11-26',
            1,
            null,
            [$this->classA->id],
            $this->petugasUser->id
        );

        // Find a student not in classA (e.g. from classB or another class)
        $outsiderStudent = Student::where('current_class_id', '!=', $this->classA->id)->first();
        if (! $outsiderStudent) {
            $outsiderStudent = Student::create([
                'nis' => 'TEST_OUTSIDER_99',
                'full_name' => 'Siswa Luar Gelombang',
                'gender' => 'L',
                'current_class_id' => 999,
                'status' => 'active',
                'enrollment_date' => now()->toDateString(),
            ]);
        }

        $token = $session->activeQrToken;

        $response = $this->actingAs($this->siswaUser)->postJson("/worship/sessions/{$session->id}/scan", [
            'token' => $token->token,
            'identifier' => $outsiderStudent->nis,
            'source' => 'qr_student_camera',
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_confidentiality_sensitive_exemption_notes_are_masked_for_general_users(): void
    {
        $participant = new WorshipSessionParticipant([
            'status' => 'exempt',
            'verification_notes' => 'Catatan sensitif halangan syar\'i siswi',
        ]);

        // General teacher / unauthorized user
        $safeNotesForTeacher = $participant->getSafeNotesForUser($this->guruUser);
        $this->assertEquals('Dispensasi', $safeNotesForTeacher);
        $this->assertStringNotContainsString('syar\'i', $safeNotesForTeacher);

        // Petugas Keagamaan / Admin authorized user
        $safeNotesForPetugas = $participant->getSafeNotesForUser($this->petugasUser);
        $this->assertEquals('Catatan sensitif halangan syar\'i siswi', $safeNotesForPetugas);
    }

    public function test_multi_tier_verification_and_session_finalization(): void
    {
        $sessionService = app(WorshipSessionService::class);
        $session = $sessionService->createSession(
            $this->zuhurType->id,
            '2026-11-27',
            1,
            null,
            [$this->classA->id],
            $this->petugasUser->id
        );

        $participants = $session->participants;
        $firstP = $participants->first();

        // 1. Wali kelas / Petugas manually verifies first participant as present
        $sessionService->verifyParticipant($firstP, 'present', 'Hadir dikonfirmasi wali kelas', $this->petugasUser->id);
        $this->assertEquals('present', $firstP->fresh()->status);
        $this->assertEquals($this->petugasUser->id, $firstP->fresh()->verified_by);

        // 2. Finalize session: Remaining participants in 'pending_verification' must become 'absent'
        $sessionService->finalizeSession($session, $this->petugasUser->id);

        $this->assertEquals('verified', $session->fresh()->status);
        $this->assertEquals($this->petugasUser->id, $session->fresh()->verified_by);

        $remainingParticipants = $session->participants()->where('id', '!=', $firstP->id)->get();
        foreach ($remainingParticipants as $rp) {
            if ($rp->status !== 'exempt') {
                $this->assertEquals('absent', $rp->status);
            }
        }
    }

    public function test_asar_session_creation_with_eligible_activity_participants(): void
    {
        $sessionService = app(WorshipSessionService::class);
        $students = Student::take(2)->get();

        $session = $sessionService->createAsarSession(
            '2026-11-28',
            $students->pluck('id')->toArray(),
            null,
            $this->petugasUser->id,
            'Salat Asar Anggota Paskibra & OSIM'
        );

        $this->assertEquals('asar', $session->type->code);
        $this->assertEquals(2, $session->participants()->count());
    }
}
