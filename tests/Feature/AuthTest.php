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
        $this->get('/register')->assertOk()->assertSee('Create an account')->assertSee('Sponsor name')->assertDontSee('Confirm password');
        $this->get('/forgot-password')->assertOk()->assertSee('Forgot password');
    }

    public function test_registration_rejects_an_unknown_sponsor(): void
    {
        $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'sponsor_name' => 'Nobody',
            'email' => 'avery@example.com',
            'password' => 'password1',
        ])->assertRedirect('/register')->assertSessionHasErrors('sponsor_name');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'email' => 'avery@example.com',
        ]);
    }

    public function test_a_visitor_can_register_and_log_out(): void
    {
        User::factory()->create([
            'name' => 'Jordan Lee',
            'user_name' => 'GSI1000001',
        ]);

        $this->post('/register', [
            'name' => 'Test User',
            'sponsor_name' => 'Jordan Lee',
            'email' => 'avery@example.com',
            'password' => 'password1',
        ])->assertRedirect('/registered')->assertSessionHas('signup_password', 'password1');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'avery@example.com',
            'sponsor_name' => 'Jordan Lee',
            'user_role' => 'users',
        ]);
        $userName = User::query()->where('email', 'avery@example.com')->value('user_name');
        $this->assertMatchesRegularExpression('/^GSI\d{7}$/', $userName);

        $this->get('/registered')
            ->assertOk()
            ->assertSee('Username')
            ->assertSee($userName)
            ->assertSee('password1')
            ->assertSee('Dashboard');

        $this->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Save your username and password')
            ->assertDontSee('password1');

        $this->get('/registered')->assertRedirect('/dashboard');

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();

        $this->post('/register', [
            'name' => 'Second User',
            'sponsor_name' => 'gsi1000001',
            'email' => 'second@example.com',
            'password' => 'password1',
        ])->assertRedirect('/registered');

        $this->assertDatabaseHas('users', [
            'email' => 'second@example.com',
            'sponsor_name' => 'gsi1000001',
        ]);
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
