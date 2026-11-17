<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('MADRASAH ONE');
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_user_can_authenticate_using_email(): void
    {
        $user = User::where('email', 'admin@madrasah.test')->first();

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');
    }

    public function test_user_can_authenticate_using_username(): void
    {
        $user = User::where('username', 'siswa')->first();

        $response = $this->post('/login', [
            'login' => 'siswa',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'login' => 'admin@madrasah.test',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');

        $this->assertDatabaseHas('login_audits', [
            'username_entered' => 'admin@madrasah.test',
            'status' => 'failed',
        ]);
    }

    public function test_user_can_logout(): void
    {
        $user = User::where('username', 'admin')->first();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
