<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DatabaseManagementService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DatabaseMaintenanceTest extends TestCase
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

    public function test_admin_can_view_database_maintenance_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('settings.database.index'));

        $response->assertStatus(200);
        $response->assertSee('Pemeliharaan');
        $response->assertSee('Cadangan Database');
        $response->assertSee('Pencadangan Database Instan');
        $response->assertSee('Reset Database');
    }

    public function test_non_admin_cannot_access_database_maintenance(): void
    {
        $response = $this->actingAs($this->student)->get(route('settings.database.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_create_backup(): void
    {
        $response = $this->actingAs($this->admin)->post(route('settings.database.backup'), [
            'description' => 'Test Backup Unit Test',
        ]);

        $response->assertRedirect(route('settings.database.index'));
        $response->assertSessionHas('success');

        $service = app(DatabaseManagementService::class);
        $backups = $service->listBackups();
        $this->assertNotEmpty($backups);
    }

    public function test_admin_cannot_reset_database_with_invalid_confirmation(): void
    {
        $response = $this->actingAs($this->admin)->post(route('settings.database.reset'), [
            'mode' => 'transactions',
            'confirm_text' => 'SALAH',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('confirm_text');
    }

    public function test_admin_cannot_reset_database_with_wrong_password(): void
    {
        $response = $this->actingAs($this->admin)->post(route('settings.database.reset'), [
            'mode' => 'transactions',
            'confirm_text' => 'RESET',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_artisan_db_backup_command_works(): void
    {
        $this->artisan('db:backup', ['--description' => 'Artisan Test'])
            ->assertSuccessful();
    }
}
