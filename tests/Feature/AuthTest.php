<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_register_and_forgot_pages_are_available(): void
    {
        $this->get('/login')->assertOk()->assertSee('Forgot password?')->assertSee('Create an account');
        $this->get('/register')->assertOk()->assertSee('Create an account')->assertSee('Sponsor name');
        $this->get('/forgot-password')->assertOk()->assertSee('Forgot password');
    }

    public function test_a_visitor_can_register_and_log_out(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'sponsor_name' => 'Jordan Lee',
            'email' => 'avery@example.com',
            'password' => 'password1',
            'password_confirmation' => 'password1',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'avery@example.com',
            'sponsor_name' => 'Jordan Lee',
        ]);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_a_user_can_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'avery@example.com',
            'password' => 'password1',
        ]);

        $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password1',
        ])->assertSessionHasErrors('email');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password1',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_user_can_reset_a_forgotten_password(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'avery@example.com',
        ]);

        $this->post('/forgot-password', [
            'email' => 'missing@example.com',
        ])->assertSessionHasErrors('email');

        $this->post('/forgot-password', [
            'email' => $user->email,
        ])->assertSessionHas('status');

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $this->get('/reset-password/'.$token.'?email='.urlencode($user->email))
            ->assertOk()
            ->assertSee('Choose a new password');

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect('/login');

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'new-password',
        ])->assertRedirect('/dashboard');
    }
}
