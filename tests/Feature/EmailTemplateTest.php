<?php

namespace Tests\Feature;

use App\Mail\TemplateMessage;
use App\Models\EmailTemplate;
use App\Models\SmtpSetting;
use App\Models\User;
use App\Services\TemplateMailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmailTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_members_cannot_open_email_templates(): void
    {
        $this->get('/admin/email')->assertRedirect('/login');

        $member = User::factory()->create();
        $template = EmailTemplate::query()->where('key', EmailTemplate::WELCOME)->first();

        $this->actingAs($member)
            ->get('/admin/email')
            ->assertForbidden();

        $this->actingAs($member)
            ->get('/admin/email/'.$template->id.'/edit')
            ->assertForbidden();

        $this->actingAs($member)
            ->put('/admin/email/'.$template->id, [])
            ->assertForbidden();

        $this->actingAs($member)
            ->delete('/admin/email/'.$template->id)
            ->assertForbidden();

        $this->actingAs($member)
            ->post('/admin/email/'.$template->id.'/resend', ['email' => $member->email])
            ->assertForbidden();
    }

    public function test_welcome_forget_password_and_reset_password_templates_are_stored(): void
    {
        $this->assertFalse(Schema::hasColumn('email_templates', 'smtp_host'));

        $templates = EmailTemplate::query()->orderBy('id')->get();

        $this->assertSame([
            EmailTemplate::WELCOME,
            EmailTemplate::FORGOT_PASSWORD,
            EmailTemplate::PASSWORD_RESET,
        ], $templates->pluck('key')->all());

        $this->assertSame([
            'Welcome mail',
            'Forget password',
            'Reset password',
        ], $templates->pluck('name')->all());

        $welcome = $templates->first();
        $this->assertTrue($welcome->is_active);
        $this->assertSame('Welcome to TMS, {name}', $welcome->subject);
        $this->assertStringContainsString('{password}', $welcome->body);
        $this->assertStringContainsString('{reset_url}', $templates[1]->body);
        $this->assertStringContainsString('{login_url}', $templates[2]->body);
    }

    public function test_the_super_admin_can_edit_one_template_without_changing_the_others(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $welcome = EmailTemplate::query()->where('key', EmailTemplate::WELCOME)->first();
        $forgot = EmailTemplate::query()->where('key', EmailTemplate::FORGOT_PASSWORD)->first();
        $reset = EmailTemplate::query()->where('key', EmailTemplate::PASSWORD_RESET)->first();
        $smtpPassword = SmtpSetting::current()->password;

        $this->actingAs($admin)
            ->get('/admin/email')
            ->assertOk()
            ->assertSee('Welcome mail')
            ->assertSee('Forget password')
            ->assertSee('Reset password')
            ->assertSee('Edit')
            ->assertSee('Delete')
            ->assertSee('Resend')
            ->assertDontSee('smtp-relay.brevo.com');

        $this->actingAs($admin)
            ->get('/admin/email/'.$forgot->id.'/edit')
            ->assertOk()
            ->assertSee('Forget password')
            ->assertSee('{reset_url}')
            ->assertDontSee('{password}');

        $this->actingAs($admin)
            ->get('/admin/email/'.$reset->id.'/edit')
            ->assertOk()
            ->assertSee('Reset password')
            ->assertSee('{login_url}')
            ->assertDontSee('{reset_url}');

        $this->actingAs($admin)
            ->put('/admin/email/'.$welcome->id, [
                'subject' => 'Hello {name}',
                'body' => 'Your username is {user_name}.',
                'is_active' => '0',
            ])
            ->assertRedirect('/admin/email/'.$welcome->id.'/edit')
            ->assertSessionHas('status', 'Email template saved.');

        $this->assertSame('Hello {name}', $welcome->fresh()->subject);
        $this->assertFalse($welcome->fresh()->is_active);
        $this->assertSame('Reset your TMS password', $forgot->fresh()->subject);
        $this->assertSame('Your TMS password was changed', $reset->fresh()->subject);
        $this->assertSame($smtpPassword, SmtpSetting::current()->password);
    }

    public function test_the_super_admin_can_delete_an_email_template(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $reset = EmailTemplate::query()->where('key', EmailTemplate::PASSWORD_RESET)->first();

        $this->actingAs($admin)
            ->delete('/admin/email/'.$reset->id)
            ->assertRedirect('/admin/email')
            ->assertSessionHas('status', 'Email template deleted.');

        $this->assertDatabaseMissing('email_templates', [
            'id' => $reset->id,
        ]);

        $this->actingAs($admin)
            ->get('/admin/email')
            ->assertOk()
            ->assertSee('Welcome mail')
            ->assertSee('Forget password')
            ->assertDontSee('Reset password');
    }

    public function test_the_super_admin_can_resend_a_template_to_a_member(): void
    {
        Mail::fake();

        $admin = User::factory()->superAdmin()->create();
        $member = User::factory()->create([
            'name' => 'Avery Stone',
            'email' => 'avery@example.com',
        ]);
        $welcome = EmailTemplate::query()->where('key', EmailTemplate::WELCOME)->first();
        $forgot = EmailTemplate::query()->where('key', EmailTemplate::FORGOT_PASSWORD)->first();

        $this->actingAs($admin)
            ->from('/admin/email')
            ->post('/admin/email/'.$welcome->id.'/resend', [
                'email' => 'missing@example.com',
            ])
            ->assertRedirect('/admin/email')
            ->assertSessionHasErrors('email');

        Mail::assertNothingSent();

        $this->actingAs($admin)
            ->post('/admin/email/'.$welcome->id.'/resend', [
                'email' => $member->email,
            ])
            ->assertRedirect('/admin/email')
            ->assertSessionHas('status', 'Email resent to avery@example.com.');

        Mail::assertSent(TemplateMessage::class, function (TemplateMessage $mail) use ($member): bool {
            return $mail->template->key === EmailTemplate::WELCOME
                && $mail->hasTo($member->email)
                && str_contains($mail->render(), 'Not included on resend')
                && str_contains($mail->render(), 'Avery Stone');
        });

        $this->actingAs($admin)
            ->post('/admin/email/'.$forgot->id.'/resend', [
                'email' => $member->email,
            ])
            ->assertRedirect('/admin/email');

        Mail::assertSent(TemplateMessage::class, function (TemplateMessage $mail) use ($member): bool {
            return $mail->template->key === EmailTemplate::FORGOT_PASSWORD
                && $mail->hasTo($member->email)
                && str_contains($mail->tokens['{reset_url}'], '/reset-password/');
        });
    }

    public function test_an_inactive_template_cannot_be_resent(): void
    {
        Mail::fake();

        $admin = User::factory()->superAdmin()->create();
        $member = User::factory()->create([
            'email' => 'avery@example.com',
        ]);
        $welcome = EmailTemplate::query()->where('key', EmailTemplate::WELCOME)->first();
        $welcome->update(['is_active' => false]);

        $this->actingAs($admin)
            ->from('/admin/email')
            ->post('/admin/email/'.$welcome->id.'/resend', [
                'email' => $member->email,
            ])
            ->assertRedirect('/admin/email')
            ->assertSessionHasErrors('email');

        Mail::assertNothingSent();
    }

    public function test_an_email_template_requires_a_subject_and_message(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $welcome = EmailTemplate::query()->where('key', EmailTemplate::WELCOME)->first();

        $this->actingAs($admin)
            ->from('/admin/email/'.$welcome->id.'/edit')
            ->put('/admin/email/'.$welcome->id, [
                'subject' => '',
                'body' => '',
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/email/'.$welcome->id.'/edit')
            ->assertSessionHasErrors(['subject', 'body']);
    }

    public function test_registration_sends_the_welcome_mail(): void
    {
        Mail::fake();

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

        $member = User::query()->where('email', 'avery@example.com')->first();

        $this->assertNotNull($member);

        Mail::assertSent(TemplateMessage::class, function (TemplateMessage $mail) use ($member): bool {
            $html = $mail->render();

            return $mail->template->key === EmailTemplate::WELCOME
                && $mail->hasTo('avery@example.com')
                && $mail->envelope()->from->address === 'muskanflutterdev@gmail.com'
                && $mail->envelope()->from->name === 'TMS'
                && $mail->envelope()->subject === 'Welcome to TMS, Test User'
                && str_contains($html, (string) $member->user_name)
                && str_contains($html, 'password1')
                && str_contains($html, 'Jordan Lee')
                && str_contains($html, 'Left');
        });

        $this->get('/registered')
            ->assertOk()
            ->assertSee('We also sent these details to your email.');
    }

    public function test_an_inactive_welcome_template_does_not_send_email(): void
    {
        Mail::fake();

        EmailTemplate::query()->where('key', EmailTemplate::WELCOME)->update(['is_active' => false]);

        User::factory()->create([
            'name' => 'Jordan Lee',
            'user_name' => 'GSI1000001',
        ]);

        $this->post('/register', [
            'name' => 'Test User',
            'sponsor_name' => 'Jordan Lee',
            'position' => 'right',
            'phone_number' => '9876543210',
            'email' => 'avery@example.com',
            'password' => 'password1',
        ])->assertRedirect('/registered');

        Mail::assertNothingSent();

        $this->get('/registered')
            ->assertOk()
            ->assertDontSee('We also sent these details to your email.');
    }

    public function test_a_forgotten_password_sends_the_forget_password_template(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'name' => 'Avery Stone',
            'email' => 'avery@example.com',
        ]);

        $this->post('/forgot-password', [
            'email' => $user->email,
        ])->assertSessionHas('status');

        Mail::assertSent(TemplateMessage::class, function (TemplateMessage $mail) use ($user): bool {
            $html = $mail->render();

            return $mail->template->key === EmailTemplate::FORGOT_PASSWORD
                && $mail->hasTo($user->email)
                && $mail->envelope()->subject === 'Reset your TMS password'
                && str_contains($html, 'Avery Stone')
                && str_contains($mail->tokens['{reset_url}'], '/reset-password/');
        });
    }

    public function test_a_new_password_sends_the_reset_password_template(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'name' => 'Avery Stone',
            'email' => 'avery@example.com',
        ]);

        $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $token = '';
        Mail::assertSent(TemplateMessage::class, function (TemplateMessage $mail) use (&$token): bool {
            if ($mail->template->key !== EmailTemplate::FORGOT_PASSWORD) {
                return false;
            }

            $token = basename((string) parse_url($mail->tokens['{reset_url}'], PHP_URL_PATH));

            return $token !== '';
        });

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect('/login');

        Mail::assertSent(TemplateMessage::class, function (TemplateMessage $mail) use ($user): bool {
            $html = $mail->render();

            return $mail->template->key === EmailTemplate::PASSWORD_RESET
                && $mail->hasTo($user->email)
                && $mail->envelope()->subject === 'Your TMS password was changed'
                && str_contains($html, 'Avery Stone')
                && str_contains($html, '/login');
        });
    }

    public function test_an_inactive_forget_password_template_does_not_send_email(): void
    {
        Mail::fake();

        EmailTemplate::query()->where('key', EmailTemplate::FORGOT_PASSWORD)->update(['is_active' => false]);

        $user = User::factory()->create([
            'email' => 'avery@example.com',
        ]);

        $this->post('/forgot-password', [
            'email' => $user->email,
        ])->assertSessionHas('status');

        Mail::assertNothingSent();
    }

    public function test_a_failed_welcome_email_still_creates_the_member(): void
    {
        $this->mock(TemplateMailer::class, function ($mock): void {
            $mock->shouldReceive('send')->once()->andReturn('failed');
        });

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

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'avery@example.com',
        ]);

        $this->get('/registered')
            ->assertOk()
            ->assertSee('The welcome email could not be sent.');
    }
}
