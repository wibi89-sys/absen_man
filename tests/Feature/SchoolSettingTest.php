<?php

namespace Tests\Feature;

use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SchoolSettingTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('username', 'admin')->first()
            ?? User::factory()->create(['username' => 'admin', 'is_active' => true]);

        $this->student = User::where('username', 'siswa')->first()
            ?? User::factory()->create(['username' => 'siswa', 'is_active' => true]);
    }

    public function test_admin_can_view_school_settings_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Madrasah');
        $response->assertSee('Identitas Lembaga Pendidikan');
    }

    public function test_non_admin_cannot_access_school_settings(): void
    {
        $response = $this->actingAs($this->student)->get(route('settings.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_update_school_settings(): void
    {
        Storage::fake('public');

        $payload = [
            'school_name' => 'MAN 1 KOTA BANDUNG',
            'school_slogan' => 'Maju Bermartabat dan Berkarakter',
            'npsn' => '20269999',
            'nsm' => '131132739999',
            'school_level' => 'Madrasah Aliyah (MA)',
            'principal_name' => 'Prof. Dr. H. Mulyadi, M.Ag.',
            'principal_nip' => '197001011995031001',
            'school_address' => 'Jl. Terusan Buahbatu No. 123, Bandung',
            'school_phone' => '(022) 7501234',
            'school_email' => 'kontak@man1bandung.sch.id',
            'school_website' => 'https://man1bandung.sch.id',
            'enable_demo_mode' => '1',
            'gate_welcome_text' => 'Selamat datang di Kampus Juara!',
        ];

        $response = $this->actingAs($this->admin)->put(route('settings.update'), $payload);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('school_settings', [
            'school_name' => 'MAN 1 KOTA BANDUNG',
            'principal_name' => 'Prof. Dr. H. Mulyadi, M.Ag.',
            'enable_demo_mode' => true,
        ]);

        // Verify cache was updated
        $current = SchoolSetting::current();
        $this->assertEquals('MAN 1 KOTA BANDUNG', $current->school_name);
    }

    public function test_admin_can_upload_school_logo(): void
    {
        Storage::fake('public');

        $logoFile = UploadedFile::fake()->image('madrasah_logo.png', 200, 200);

        $payload = [
            'school_name' => 'MAN 2 KOTA JAKARTA',
            'school_level' => 'Madrasah Aliyah (MA)',
            'principal_name' => 'Drs. H. Sudirman, M.Pd.',
            'logo' => $logoFile,
        ];

        $response = $this->actingAs($this->admin)->put(route('settings.update'), $payload);

        $response->assertRedirect(route('settings.index'));

        $setting = SchoolSetting::current();
        $this->assertNotNull($setting->logo_path);
        Storage::disk('public')->assertExists($setting->logo_path);
    }

    public function test_login_page_renders_dynamic_school_settings(): void
    {
        SchoolSetting::current()->update([
            'school_name' => 'MADRASAH UNGGULAN 2026',
            'school_slogan' => 'Pusat Prestasi Sains & Agama',
            'enable_demo_mode' => true,
        ]);
        Cache::forget('school_setting');

        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('MADRASAH UNGGULAN 2026');
        $response->assertSee('Pusat Prestasi Sains & Agama');
        $response->assertSee('Akun Uji Coba (Tester / Demo)');
    }

    public function test_login_page_hides_demo_accounts_when_disabled(): void
    {
        SchoolSetting::current()->update([
            'school_name' => 'SEKOLAH FORMAL MURNI',
            'enable_demo_mode' => false,
        ]);
        Cache::forget('school_setting');

        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('SEKOLAH FORMAL MURNI');
        $response->assertDontSee('Akun Uji Coba (Tester / Demo)');
    }
}
