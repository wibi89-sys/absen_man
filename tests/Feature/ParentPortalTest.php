<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\ParentMessage;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\User;
use App\Models\WorshipExemption;
use App\Models\WorshipSession;
use App\Models\WorshipSessionParticipant;
use App\Models\WorshipType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ParentPortalTest extends TestCase
{
    use DatabaseTransactions;

    protected User $parentUser;

    protected User $adminUser;

    protected User $waliKelasUser;

    protected ParentModel $parentModel;

    protected Student $verifiedChild;

    protected Student $unverifiedChild;

    protected Student $strangerChild;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parentUser = User::where('username', 'orangtua')->first();
        $this->adminUser = User::where('username', 'admin')->first();
        $this->waliKelasUser = User::where('username', 'walikelas')->first();

        $this->parentModel = ParentModel::firstOrCreate(
            ['user_id' => $this->parentUser->id],
            [
                'nik' => '3271012345678901',
                'full_name' => $this->parentUser->name,
                'relationship_type' => 'ayah',
            ]
        );

        // Prepare 3 students: verified child, unverified child, and stranger child
        $students = Student::take(3)->get();
        $this->verifiedChild = $students[0];
        $this->unverifiedChild = $students[1];
        $this->strangerChild = $students[2] ?? Student::create([
            'nis' => 'STRANGER_999',
            'full_name' => 'Siswa Anak Orang Lain',
            'gender' => 'L',
            'status' => 'active',
            'enrollment_date' => now()->toDateString(),
        ]);

        // Link verified child
        $this->parentModel->students()->syncWithoutDetaching([
            $this->verifiedChild->id => [
                'is_verified' => true,
                'verified_by' => $this->adminUser->id,
                'verified_at' => now(),
            ],
            $this->unverifiedChild->id => [
                'is_verified' => false,
                'verified_by' => null,
                'verified_at' => null,
            ],
        ]);
    }

    public function test_verified_parent_can_access_dashboard_and_see_own_verified_child(): void
    {
        $response = $this->actingAs($this->parentUser)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee($this->verifiedChild->full_name);
        $response->assertSee($this->verifiedChild->nis);
    }

    public function test_unverified_child_shows_pending_warning_banner(): void
    {
        $response = $this->actingAs($this->parentUser)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Pemberitahuan Verifikasi Akun');
        $response->assertSee('1'); // 1 unverified child
    }

    public function test_parent_can_view_verified_child_details_presensi_and_ibadah(): void
    {
        $response = $this->actingAs($this->parentUser)->get("/parent/children/{$this->verifiedChild->id}");

        $response->assertStatus(200);
        $response->assertSee($this->verifiedChild->full_name);
        $response->assertSee('Presensi Sekolah');
        $response->assertSee('Monitoring Ibadah');
    }

    public function test_strict_idor_defense_parent_cannot_access_unverified_child_detail(): void
    {
        // Unverified relationship must be rejected with HTTP 403 Forbidden
        $response = $this->actingAs($this->parentUser)->get("/parent/children/{$this->unverifiedChild->id}");

        $response->assertStatus(403);
    }

    public function test_strict_idor_defense_parent_cannot_access_stranger_child_detail(): void
    {
        // Stranger child (zero link) must be rejected with HTTP 403 Forbidden
        $response = $this->actingAs($this->parentUser)->get("/parent/children/{$this->strangerChild->id}");

        $response->assertStatus(403);
    }

    public function test_parent_sees_masked_worship_exemption_without_sensitive_leak(): void
    {
        // Create an exemption for the verified child
        WorshipExemption::create([
            'student_id' => $this->verifiedChild->id,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'reason_category' => 'sharia_halangan',
            'notes' => 'Catatan sangat rahasia kondisi biologis mahasiswi/siswi',
            'approved_by' => $this->adminUser->id,
            'is_confidential' => true,
        ]);

        $zuhurType = WorshipType::where('code', 'zuhur')->first();
        $session = WorshipSession::create([
            'worship_type_id' => $zuhurType->id,
            'date' => now()->toDateString(),
            'wave_number' => 1,
            'status' => 'verified',
            'created_by' => $this->adminUser->id,
        ]);

        $participant = WorshipSessionParticipant::create([
            'worship_session_id' => $session->id,
            'student_id' => $this->verifiedChild->id,
            'school_class_id' => $this->verifiedChild->current_class_id ?? 1,
            'status' => 'exempt',
            'verification_notes' => 'Dispensasi: Halangan Syar\'i',
        ]);

        $response = $this->actingAs($this->parentUser)->get("/parent/children/{$this->verifiedChild->id}?tab=worship");

        $response->assertStatus(200);
        $response->assertSee('Dispensasi');
        // Verify internal medical/sharia notes are NOT leaked
        $response->assertDontSee('sangat rahasia kondisi biologis');
    }

    public function test_parent_can_view_announcements_targeted_to_parents_and_all(): void
    {
        Announcement::create([
            'title' => 'Pengumuman Khusus Guru',
            'content' => 'Rapat dewan guru hari senin',
            'category' => 'general',
            'target_role' => 'teachers',
            'published_at' => now()->subHour(),
            'created_by' => $this->adminUser->id,
        ]);

        Announcement::create([
            'title' => 'Pengumuman Khusus Orang Tua Murid',
            'content' => 'Pertemuan wali murid semester genap',
            'category' => 'general',
            'target_role' => 'parents',
            'published_at' => now()->subHour(),
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->parentUser)->get('/parent/announcements');

        $response->assertStatus(200);
        $response->assertSee('Pengumuman Khusus Orang Tua Murid');
        // Parent must NOT see teacher-only announcements
        $response->assertDontSee('Pengumuman Khusus Guru');
    }

    public function test_parent_can_view_messages_and_mark_as_read(): void
    {
        $message = ParentMessage::create([
            'student_id' => $this->verifiedChild->id,
            'parent_id' => $this->parentModel->id,
            'sender_id' => $this->waliKelasUser->id,
            'title' => 'Catatan Disiplin dan Ibadah',
            'message' => 'Ananda menunjukkan keaktifan yang sangat baik.',
            'category' => 'general',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->parentUser)->get('/parent/messages');
        $response->assertStatus(200);
        $response->assertSee('Catatan Disiplin dan Ibadah');

        // Mark as read
        $markResponse = $this->actingAs($this->parentUser)->post("/parent/messages/{$message->id}/read");
        $markResponse->assertRedirect();

        $this->assertTrue($message->fresh()->is_read);
        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_teacher_can_send_message_to_verified_parent(): void
    {
        $response = $this->actingAs($this->waliKelasUser)->post('/communication/messages', [
            'student_id' => $this->verifiedChild->id,
            'parent_id' => $this->parentModel->id,
            'title' => 'Informasi Perkembangan Akademik',
            'message' => 'Nilai harian ananda mengalami peningkatan.',
            'category' => 'academic',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('parent_messages', [
            'student_id' => $this->verifiedChild->id,
            'parent_id' => $this->parentModel->id,
            'title' => 'Informasi Perkembangan Akademik',
        ]);
    }

    public function test_teacher_cannot_send_message_to_unverified_parent(): void
    {
        // Try sending message for unverified child link
        $response = $this->actingAs($this->waliKelasUser)->post('/communication/messages', [
            'student_id' => $this->unverifiedChild->id,
            'parent_id' => $this->parentModel->id,
            'title' => 'Pesan Tidak Sah',
            'message' => 'Pesan ini tidak boleh terkirim.',
            'category' => 'general',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('parent_messages', [
            'student_id' => $this->unverifiedChild->id,
            'title' => 'Pesan Tidak Sah',
        ]);
    }
}
