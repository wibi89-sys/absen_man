<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\CounselingSession;
use App\Models\DisciplineCategory;
use App\Models\DisciplineRecord;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentActivity;
use App\Models\StudentActivityAttendance;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Models\WorshipSession;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AcademicAndStudentAffairsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;

    protected User $bkUser;

    protected User $guruUser;

    protected User $parentUser;

    protected SchoolClass $class;

    protected Teacher $teacher;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('username', 'admin')->first();
        $this->bkUser = User::where('username', 'gurubk')->first();
        $this->guruUser = User::where('username', 'guru')->first();
        $this->parentUser = User::where('username', 'orangtua')->first();

        $this->class = SchoolClass::first();
        $this->teacher = Teacher::first();
        $this->student = Student::where('current_class_id', $this->class->id)->first() ?? Student::first();
    }

    public function test_subject_management_and_assignment_to_class(): void
    {
        $subject = Subject::firstOrCreate(['code' => 'TEST-MAPEL'], [
            'name' => 'Mata Pelajaran Uji Coba',
            'order' => 10,
            'is_active' => true,
        ]);

        $year = AcademicYear::first();

        $response = $this->actingAs($this->adminUser)->post('/academic/subjects/assign', [
            'school_class_id' => $this->class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $this->teacher->id,
            'academic_year_id' => $year->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('class_subjects', [
            'school_class_id' => $this->class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $this->teacher->id,
        ]);
    }

    public function test_batch_grade_entry_and_publication(): void
    {
        $classSubject = ClassSubject::first();

        $response = $this->actingAs($this->guruUser)->post('/academic/grades/batch', [
            'class_subject_id' => $classSubject->id,
            'assessment_name' => 'Ulangan Harian 1',
            'scores' => [
                $this->student->id => 88.5,
            ],
            'notes' => [
                $this->student->id => 'Pemahaman konsep sangat baik',
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('academic_grades', [
            'student_id' => $this->student->id,
            'class_subject_id' => $classSubject->id,
            'assessment_name' => 'Ulangan Harian 1',
            'score' => 88.50,
        ]);

        // Toggle publication
        $publishResponse = $this->actingAs($this->guruUser)->post('/academic/grades/toggle-publish', [
            'class_subject_id' => $classSubject->id,
            'assessment_name' => 'Ulangan Harian 1',
            'is_published' => 1,
        ]);

        $publishResponse->assertRedirect();
        $this->assertDatabaseHas('academic_grades', [
            'class_subject_id' => $classSubject->id,
            'assessment_name' => 'Ulangan Harian 1',
            'is_published' => 1,
        ]);
    }

    public function test_discipline_record_creation_and_status_transition(): void
    {
        $category = DisciplineCategory::firstOrCreate(['name' => 'Test Pelanggaran'], [
            'type' => 'violation',
            'points' => 10,
        ]);

        $response = $this->actingAs($this->adminUser)->post('/discipline', [
            'student_id' => $this->student->id,
            'category_id' => $category->id,
            'date' => now()->toDateString(),
            'description' => 'Test pelanggaran tata tertib madrasah',
            'action_taken' => 'Pembinaan lisan',
            'status' => 'reported',
        ]);

        $response->assertRedirect('/discipline');
        $this->assertDatabaseHas('discipline_records', [
            'student_id' => $this->student->id,
            'category_id' => $category->id,
            'status' => 'reported',
        ]);

        $record = DisciplineRecord::where('student_id', $this->student->id)->latest()->first();

        // Update status to resolved
        $updateResponse = $this->actingAs($this->adminUser)->post("/discipline/{$record->id}/status", [
            'status' => 'resolved',
            'action_taken' => 'Siswa telah menyelesaikan konseling kedisiplinan',
        ]);

        $updateResponse->assertRedirect();
        $this->assertEquals('resolved', $record->fresh()->status);
    }

    public function test_counseling_strict_privacy_guru_bk_and_super_admin_can_access(): void
    {
        $session = CounselingSession::first();

        // Guru BK can view session list and detail
        $this->actingAs($this->bkUser)->get('/counseling')->assertStatus(200);
        $this->actingAs($this->bkUser)->get("/counseling/{$session->id}")->assertStatus(200)
            ->assertSee($session->student->full_name);

        // Super Admin can also view
        $superAdmin = User::where('username', 'superadmin')->first();
        $this->actingAs($superAdmin)->get("/counseling/{$session->id}")->assertStatus(200);
    }

    public function test_counseling_strict_privacy_regular_teacher_and_parent_are_forbidden(): void
    {
        $session = CounselingSession::first();

        // Regular teacher is forbidden (HTTP 403)
        $this->actingAs($this->guruUser)->get('/counseling')->assertStatus(403);
        $this->actingAs($this->guruUser)->get("/counseling/{$session->id}")->assertStatus(403);

        // Parent is forbidden (HTTP 403)
        $this->actingAs($this->parentUser)->get('/counseling')->assertStatus(403);
        $this->actingAs($this->parentUser)->get("/counseling/{$session->id}")->assertStatus(403);
    }

    public function test_student_activity_member_enrollment_and_attendance(): void
    {
        $activity = StudentActivity::first();

        $response = $this->actingAs($this->adminUser)->post("/activities/{$activity->id}/members", [
            'student_id' => $this->student->id,
            'role' => 'pengurus',
            'joined_date' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('student_activity_members', [
            'student_activity_id' => $activity->id,
            'student_id' => $this->student->id,
            'role' => 'pengurus',
            'is_active' => true,
        ]);

        // Record attendance
        $attResponse = $this->actingAs($this->adminUser)->post("/activities/{$activity->id}/attendance", [
            'date' => now()->toDateString(),
            'attendances' => [
                $this->student->id => 'present',
            ],
        ]);

        $attResponse->assertRedirect();
        $this->assertDatabaseHas('student_activity_attendances', [
            'student_activity_id' => $activity->id,
            'student_id' => $this->student->id,
            'status' => 'present',
        ]);
    }

    public function test_activity_attendance_generates_asar_prayer_session(): void
    {
        $activity = StudentActivity::first();
        $today = now()->toDateString();

        // Mark student as present in activity
        StudentActivityAttendance::updateOrCreate(
            [
                'student_activity_id' => $activity->id,
                'date' => $today,
                'student_id' => $this->student->id,
            ],
            ['status' => 'present']
        );

        $response = $this->actingAs($this->adminUser)->post("/activities/{$activity->id}/generate-asar", [
            'date' => $today,
        ]);

        $response->assertRedirect();

        // An Asar prayer session must be created with locked participants
        $session = WorshipSession::where('date', $today)
            ->whereHas('type', fn ($q) => $q->where('code', 'asar'))
            ->latest()
            ->first();

        $this->assertNotNull($session);
        $this->assertDatabaseHas('worship_session_participants', [
            'worship_session_id' => $session->id,
            'student_id' => $this->student->id,
        ]);
    }
}
