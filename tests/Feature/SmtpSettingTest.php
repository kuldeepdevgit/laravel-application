<?php

namespace Tests\Feature;

use App\Models\EmailTemplate;
use App\Models\SmtpSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmtpSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_members_cannot_open_smtp_settings(): void
    {
        $this->get('/admin/smtp')->assertRedirect('/login');

        $member = User::factory()->create();

        $this->actingAs($member)
            ->get('/admin/smtp')
            ->assertForbidden();

        $this->actingAs($member)
            ->put('/admin/smtp', [])
            ->assertForbidden();
    }

    public function test_smtp_settings_are_seeded_in_their_own_table(): void
    {
        $smtp = SmtpSetting::query()->first();

        $this->assertNotNull($smtp);
        $this->assertSame(1, SmtpSetting::query()->count());
        $this->assertSame('smtp-relay.brevo.com', $smtp->host);
        $this->assertSame(587, $smtp->port);
        $this->assertSame('tls', $smtp->encryption);
        $this->assertSame('b5018f001@smtp-brevo.com', $smtp->username);
        $this->assertSame('muskanflutterdev@gmail.com', $smtp->from_address);
        $this->assertSame('TMS', $smtp->from_name);
        $this->assertSame('', $smtp->password);
        $this->assertStringNotContainsString('xsmtpsib-', (string) $smtp->getRawOriginal('password'));

        $original = config('mail');

        try {
            $smtp->applyMailConfig();

            $this->assertSame('smtp', config('mail.default'));
            $this->assertSame('smtp', config('mail.mailers.smtp.scheme'));
            $this->assertSame('smtp-relay.brevo.com', config('mail.mailers.smtp.host'));
            $this->assertSame(587, config('mail.mailers.smtp.port'));
            $this->assertTrue(config('mail.mailers.smtp.require_tls'));
            $this->assertSame($smtp->password, config('mail.mailers.smtp.password'));
            $this->assertSame('muskanflutterdev@gmail.com', config('mail.from.address'));
            $this->assertSame('TMS', config('mail.from.name'));
        } finally {
            config(['mail' => $original]);
        }
    }

    public function test_the_super_admin_can_change_smtp_without_changing_the_email_template(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $template = EmailTemplate::query()->where('key', EmailTemplate::WELCOME)->first();
        $smtp = SmtpSetting::current();

        $this->actingAs($admin)
            ->get('/admin/smtp')
            ->assertOk()
            ->assertSee('SMTP account')
            ->assertSee('smtp-relay.brevo.com')
            ->assertSee('b5018f001@smtp-brevo.com')
            ->assertSee('muskanflutterdev@gmail.com')
            ->assertSee('Leave this blank to keep the current password.')
            ->assertDontSee('Welcome to TMS, {name}');

        $this->actingAs($admin)
            ->put('/admin/smtp', [
                'host' => 'smtp.example.com',
                'port' => 2525,
                'encryption' => 'none',
                'username' => 'mailer@example.com',
                'password' => 'changed-secret',
                'from_name' => 'Desk',
                'from_address' => 'desk@example.com',
            ])
            ->assertRedirect('/admin/smtp')
            ->assertSessionHas('status', 'SMTP settings saved.');

        $saved = $smtp->fresh();

        $this->assertSame('smtp.example.com', $saved->host);
        $this->assertSame(2525, $saved->port);
        $this->assertSame('none', $saved->encryption);
        $this->assertSame('mailer@example.com', $saved->username);
        $this->assertSame('changed-secret', $saved->password);
        $this->assertSame('Desk', $saved->from_name);
        $this->assertSame('desk@example.com', $saved->from_address);
        $this->assertSame('Welcome to TMS, {name}', $template->fresh()->subject);
        $this->assertSame('Reset your TMS password', EmailTemplate::query()->where('key', EmailTemplate::FORGOT_PASSWORD)->value('subject'));
    }

    public function test_a_blank_password_keeps_the_saved_smtp_password(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $smtp = SmtpSetting::current();
        $password = $smtp->password;

        $this->actingAs($admin)
            ->from('/admin/smtp')
            ->put('/admin/smtp', [
                'host' => 'smtp-relay.brevo.com',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'b5018f001@smtp-brevo.com',
                'password' => '',
                'from_name' => 'TMS',
                'from_address' => 'muskanflutterdev@gmail.com',
            ])
            ->assertRedirect('/admin/smtp');

        $this->assertSame($password, $smtp->fresh()->password);
    }

    public function test_smtp_settings_require_a_valid_from_address_and_port(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->from('/admin/smtp')
            ->put('/admin/smtp', [
                'host' => '',
                'port' => 70000,
                'encryption' => 'tls',
                'username' => 'mailer@example.com',
                'from_name' => 'TMS',
                'from_address' => 'not-an-email',
            ])
            ->assertRedirect('/admin/smtp')
            ->assertSessionHasErrors(['host', 'port', 'from_address']);
    }
}
