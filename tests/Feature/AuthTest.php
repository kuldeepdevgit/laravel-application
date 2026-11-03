<?php

namespace Tests\Feature;

use App\Mail\TemplateMessage;
use App\Models\Company;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_register_and_forgot_pages_are_available(): void
    {
        $this->get('/login')->assertOk()->assertSee('Forgot password?')->assertSee('Create an account')->assertSee('Green Smart India E Services')->assertSee('images/green-smart-india.png', false);
        $this->get('/register')->assertOk()->assertSee('Create an account')->assertSee('Sponsor name')->assertSee('Phone number')->assertSee('Position')->assertSee('Left')->assertSee('Right')->assertSee('Green Smart India E Services')->assertSee('images/green-smart-india.png', false)->assertDontSee('Confirm password');
        $this->get('/forgot-password')->assertOk()->assertSee('Forgot password');
    }

    public function test_a_sponsor_username_returns_the_full_name(): void
    {
        User::factory()->create([
            'name' => 'Jordan Lee',
            'user_name' => 'GSI6185588',
        ]);

        $this->getJson('/register/sponsor?ref=GSI6185588')
            ->assertOk()
            ->assertJson(['name' => 'Jordan Lee']);

        $this->getJson('/register/sponsor?ref=nobody')
            ->assertNotFound()
            ->assertJson(['name' => null]);
    }

    public function test_a_referral_link_fills_the_sponsor_name(): void
    {
        $this->get('/register?ref=GSI6185588')
            ->assertOk()
            ->assertSee('value="GSI6185588"', false);
    }

    public function test_registration_rejects_an_unknown_sponsor(): void
    {
        $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'sponsor_name' => 'Nobody',
            'position' => 'left',
            'phone_number' => '9876543210',
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
            'position' => 'left',
            'phone_number' => '9876543210',
            'email' => 'avery@example.com',
            'password' => 'password1',
        ])->assertRedirect('/registered')->assertSessionHas('signup_password', 'password1');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'avery@example.com',
            'sponsor_name' => 'Jordan Lee',
            'position' => 'left',
            'phone_number' => '9876543210',
            'user_role' => 'users',
        ]);
        $userName = User::query()->where('email', 'avery@example.com')->value('user_name');
        $this->assertMatchesRegularExpression('/^GS\d{6}$/', $userName);

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
            'position' => 'right',
            'phone_number' => '9123456789',
            'email' => 'second@example.com',
            'password' => 'password1',
        ])->assertRedirect('/registered');

        $secondName = User::query()->where('email', 'second@example.com')->value('user_name');

        $this->assertDatabaseHas('users', [
            'email' => 'second@example.com',
            'sponsor_name' => 'gsi1000001',
            'position' => 'right',
        ]);
        $this->assertMatchesRegularExpression('/^GS\d{6}$/', $secondName);
        $this->assertNotSame($userName, $secondName);
    }

    public function test_registration_uses_the_active_company_shortcode(): void
    {
        Company::factory()->create([
            'name' => 'Inactive Co',
            'shortcode' => 'ZZZ',
            'is_active' => false,
        ]);
        Company::factory()->create([
            'name' => 'Green Smart India',
            'shortcode' => 'GS',
            'is_active' => true,
        ]);
        User::factory()->create([
            'name' => 'Jordan Lee',
            'user_name' => 'GSI1000001',
        ]);

        $this->post('/register', [
            'name' => 'Test User',
            'sponsor_name' => 'Jordan Lee',
            'position' => 'left',
            'phone_number' => '9876543210',
            'email' => 'avery@example.com',
            'password' => 'password1',
        ])->assertRedirect('/registered');

        $userName = User::query()->where('email', 'avery@example.com')->value('user_name');
        $this->assertMatchesRegularExpression('/^GS\d{6}$/', $userName);

        $this->post('/logout');

        $this->post('/register', [
            'name' => 'Second User',
            'sponsor_name' => $userName,
            'position' => 'right',
            'phone_number' => '9123456789',
            'email' => 'second@example.com',
            'password' => 'password1',
        ])->assertRedirect('/registered');

        $secondName = User::query()->where('email', 'second@example.com')->value('user_name');
        $this->assertMatchesRegularExpression('/^GS\d{6}$/', $secondName);
        $this->assertNotSame($userName, $secondName);
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
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'avery@example.com',
        ]);

        $this->post('/forgot-password', [
            'email' => 'missing@example.com',
        ])->assertSessionHasErrors('email');

        $this->post('/forgot-password', [
            'email' => $user->email,
        ])->assertSessionHas('status');

        $token = '';
        Mail::assertSent(TemplateMessage::class, function (TemplateMessage $mail) use (&$token): bool {
            if ($mail->template->key !== EmailTemplate::FORGOT_PASSWORD) {
                return false;
            }

            $token = basename((string) parse_url($mail->tokens['{reset_url}'], PHP_URL_PATH));

            return $token !== '';
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
