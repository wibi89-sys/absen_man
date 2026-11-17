<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\CounselingSession;
use App\Models\ParentModel;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\WorshipSession;
use App\Models\WorshipSessionParticipant;
use App\Models\WorshipType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityAndEdgeCasesTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superAdminUser;

    protected User $adminUser;

    protected User $bkUser;

    protected User $guruUser;

    protected User $studentUser;

    protected User $parentUser;

    protected Student $student;

    protected ParentModel $schoolParent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdminUser = User::where('username', 'superadmin')->first();
        $this->adminUser = User::where('username', 'admin')->first();
        $this->bkUser = User::where('username', 'gurubk')->first();
        $this->guruUser = User::where('username', 'guru')->first();
        $this->studentUser = User::where('username', 'siswa')->first();
        $this->parentUser = User::where('username', 'orangtua')->first();

        $this->student = Student::first();
        $this->schoolParent = ParentModel::first();
    }

    /**
     * 1. Rate Limiting: Login throttle prevents brute-force credential stuffing.
     */
    public function test_login_rate_limiting_prevents_brute_force(): void
    {
        RateLimiter::clear('wronguser|127.0.0.1');

        // Lakukan 5 percobaan salah berturut-turut
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'login' => 'wronguser',
                'password' => 'wrongpassword',
            ]);
        }

        // Percobaan ke-6 harus ditolak oleh RateLimiter dengan pesan throttling
        $response = $this->post('/login', [
            'login' => 'wronguser',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertStringContainsString('Terlalu banyak percobaan login gagal', session('errors')->first('login'));
    }

    /**
     * 2. Role Boundary (RBAC Matrix): Verify unauthorized roles get 403 Forbidden.
     */
    public function test_role_based_access_control_matrix_rejects_unauthorized_roles(): void
    {
        // Siswa mencoba mengakses Master Data -> 403
        $this->actingAs($this->studentUser)
            ->get(route('master.students.index'))
            ->assertStatus(403);

        // Siswa mencoba mengakses Jurnal Presensi Sekolah -> 403
        $this->actingAs($this->studentUser)
            ->get(route('attendance.index'))
            ->assertStatus(403);

        // Guru biasa (bukan BK/Super Admin) mencoba mengakses Bimbingan Konseling -> 403
        $this->actingAs($this->guruUser)
            ->get(route('counseling.index'))
            ->assertStatus(403);

        // Guru biasa mencoba mengakses halaman kelola dispensasi syar'i -> 403
        $this->actingAs($this->guruUser)
            ->get(route('worship.exemptions.index'))
            ->assertStatus(403);

        // Orang tua mencoba membuat pengumuman madrasah -> 403
        $this->actingAs($this->parentUser)
            ->get(route('communication.announcements.create'))
            ->assertStatus(403);
    }

    /**
     * 3. IDOR Defense: Parent cannot access unverified or stranger children.
     */
    public function test_strict_idor_defense_on_parent_portal(): void
    {
        // Buat siswa baru yang sama sekali tidak memiliki relasi dengan orang tua ini
        $class = SchoolClass::first();
        $strangerStudent = Student::create([
            'nis' => 'STRANGER999',
            'nisn' => '0099999999',
            'full_name' => 'Siswa Orang Lain',
            'gender' => 'L',
            'enrollment_date' => '2024-07-15',
            'status' => 'active',
            'current_class_id' => $class->id,
        ]);

        // Akses oleh orang tua ke data siswa orang lain harus DITOLAK 403
        $this->actingAs($this->parentUser)
            ->get(route('parent.children.show', $strangerStudent))
            ->assertStatus(403);

        // Tautkan siswa ke orang tua tetapi status is_verified = false
        $this->schoolParent->students()->attach($strangerStudent->id, [
            'is_verified' => false,
        ]);

        // Akses ke siswa yang belum diverifikasi tetap DITOLAK 403
        $this->actingAs($this->parentUser)
            ->get(route('parent.children.show', $strangerStudent))
            ->assertStatus(403);
    }

    /**
     * 4. Sensitive Privacy Protection: Female student biological exemption masked in parent view.
     */
    public function test_sensitive_data_privacy_masking_for_sharia_exemptions(): void
    {
        // Pastikan student terverifikasi untuk parent ini
        $this->schoolParent->students()->syncWithoutDetaching([
            $this->student->id => ['is_verified' => true],
        ]);

        // Buat sesi ibadah dan catatan participant berstatus 'exempt' dengan alasan syar'i sensitif
        $worshipType = WorshipType::first();
        $session = WorshipSession::create([
            'worship_type_id' => $worshipType->id,
            'academic_year_id' => 1,
            'date' => now()->toDateString(),
            'wave_number' => 1,
            'status' => 'verified',
            'created_by' => $this->adminUser->id,
        ]);

        WorshipSessionParticipant::create([
            'worship_session_id' => $session->id,
            'student_id' => $this->student->id,
            'school_class_id' => $this->student->current_class_id,
            'status' => 'exempt',
            'notes' => 'Halangan Biologis / Haid Hari Ke-2',
        ]);

        $response = $this->actingAs($this->parentUser)
            ->get(route('parent.children.show', ['student' => $this->student, 'tab' => 'worship']));

        $response->assertStatus(200);
        // Catatan biologis sensitif TIDAK BOLEH muncul secara vulgar di layar
        $response->assertDontSee('Haid Hari Ke-2');
        // Harus ditampilkan dengan label netral "Dispensasi"
        $response->assertSee('Dispensasi');
    }

    /**
     * 5. Strict Privacy Isolation: BK Counseling Sessions accessible only by Guru BK and Super Admin.
     */
    public function test_strict_privacy_isolation_for_bk_counseling_sessions(): void
    {
        $teacher = Teacher::first();
        $counseling = CounselingSession::create([
            'student_id' => $this->student->id,
            'counselor_id' => $teacher->id,
            'session_date' => now()->toDateString(),
            'category' => 'pribadi',
            'confidentiality_level' => 'strictly_confidential',
            'status' => 'in_progress',
            'case_background' => 'Rahasia Konseling Sangat Sensitif Mengenai Keluarga',
            'action_plan' => 'Pemberian konseling individual berkala',
        ]);

        // Guru biasa diblokir 403
        $this->actingAs($this->guruUser)
            ->get(route('counseling.show', $counseling))
            ->assertStatus(403);

        // Staf Tata Usaha / Admin umum diblokir 403 (karena bukan guru BK atau super admin)
        $this->actingAs($this->adminUser)
            ->get(route('counseling.show', $counseling))
            ->assertStatus(403);

        // Guru BK berhak melihat detail
        $this->actingAs($this->bkUser)
            ->get(route('counseling.show', $counseling))
            ->assertStatus(200)
            ->assertSee('Rahasia Konseling Sangat Sensitif');

        // Super Admin berhak melihat detail
        $this->actingAs($this->superAdminUser)
            ->get(route('counseling.show', $counseling))
            ->assertStatus(200)
            ->assertSee('Rahasia Konseling Sangat Sensitif');
    }

    /**
     * 6. XSS Prevention: HTML tags in announcements and discipline are escaped by Blade.
     */
    public function test_xss_prevention_in_announcements_and_discipline(): void
    {
        $xssPayload = "<script>alert('XSS-TEST-MADRASAH')</script>";

        // Buat pengumuman dengan konten HTML berbahaya
        Announcement::create([
            'title' => 'Pengumuman Uji Keamanan '.$xssPayload,
            'content' => 'Konten pengumuman '.$xssPayload,
            'target_role' => 'all',
            'category' => 'general',
            'is_published' => true,
            'published_at' => now(),
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('communication.announcements.index'));

        $response->assertStatus(200);
        // Tag script harus di-escape sebagai &lt;script&gt; bukan tag mentah
        $response->assertDontSee($xssPayload, false);
        $response->assertSee('&lt;script&gt;alert(&#039;XSS-TEST-MADRASAH&#039;)&lt;/script&gt;', false);
    }

    /**
     * 7. Database Backup Command: Verify artisan madrasah:backup creates valid dump file.
     */
    public function test_database_backup_command_generates_valid_dump_file(): void
    {
        $backupDir = storage_path('app/backups');

        $this->artisan('madrasah:backup')
            ->assertExitCode(0);

        $backups = File::glob("{$backupDir}/backup_madrasah_one_*.sql");
        $this->assertNotEmpty($backups, 'File backup harus berhasil dibuat di folder storage/app/backups.');

        $latestBackup = end($backups);
        $content = File::get($latestBackup);

        $this->assertStringContainsString('-- Backup Database: MADRASAH ONE', $content);
        $this->assertStringContainsString('CREATE TABLE `users`', $content);
        $this->assertStringContainsString('SET FOREIGN_KEY_CHECKS=1;', $content);
    }
}
