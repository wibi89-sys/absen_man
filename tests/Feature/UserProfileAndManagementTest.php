<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserProfileAndManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('Informasi Pribadi');
    }

    public function test_user_can_update_profile_details(): void
    {
        $user = User::factory()->create(['name' => 'Nama Lama', 'phone' => '0811111111']);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Nama Baru Diperbarui',
            'phone' => '0822222222',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baru Diperbarui',
            'phone' => '0822222222',
        ]);
    }

    public function test_user_can_change_password_with_valid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password-123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'old-password-123',
            'password' => 'new-secure-password-456',
            'password_confirmation' => 'new-secure-password-456',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('new-secure-password-456', $user->fresh()->password));
    }

    public function test_user_cannot_change_password_with_invalid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct-password-123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'wrong-current-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('correct-password-123', $user->fresh()->password));
    }

    public function test_admin_can_view_user_management_index(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $response = $this->actingAs($admin)->get(route('master.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna');
    }

    public function test_admin_can_reset_user_password(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $targetUser = User::factory()->create([
            'password' => Hash::make('temporary123'),
        ]);

        $response = $this->actingAs($admin)->post(route('master.users.reset-password', $targetUser), [
            'password' => 'admin-reset-newpass-888',
            'password_confirmation' => 'admin-reset-newpass-888',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('admin-reset-newpass-888', $targetUser->fresh()->password));
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $targetUser = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post(route('master.users.toggle-status', $targetUser));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertFalse($targetUser->fresh()->is_active);

        // Toggle back to active
        $response = $this->actingAs($admin)->post(route('master.users.toggle-status', $targetUser));
        $this->assertTrue($targetUser->fresh()->is_active);
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $response = $this->actingAs($admin)->post(route('master.users.toggle-status', $admin));

        $response->assertSessionHas('error');
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $guru = User::factory()->create();
        $guru->roles()->attach(Role::where('name', 'guru')->first());

        $response = $this->actingAs($guru)->get(route('master.users.index'));

        $response->assertStatus(403);
    }
}
