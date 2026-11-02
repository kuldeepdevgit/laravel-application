<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    public const WELCOME = 'welcome';

    public const FORGOT_PASSWORD = 'forgot_password';

    public const PASSWORD_RESET = 'password_reset';

    protected $fillable = [
        'key',
        'name',
        'subject',
        'body',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return Collection<int, self>
     */
    public static function ordered(): Collection
    {
        $rank = array_flip([
            self::WELCOME,
            self::FORGOT_PASSWORD,
            self::PASSWORD_RESET,
        ]);

        return static::query()->get()
            ->sortBy(fn (self $template): int => $rank[$template->key] ?? 99)
            ->values();
    }

    /**
     * @return array<int, array{key: string, name: string, subject: string, body: string, is_active: bool}>
     */
    public static function catalog(): array
    {
        return [
            [
                'key' => self::WELCOME,
                'name' => 'Welcome mail',
                'subject' => 'Welcome to TMS, {name}',
                'body' => <<<'BODY'
Hello {name},

Your Green Smart India E Services account is ready.

Username: {user_name}
Password: {password}
Email: {email}
Phone: {phone_number}
Sponsor: {sponsor_name}
Position: {position}

Log in: {login_url}
BODY,
                'is_active' => true,
            ],
            [
                'key' => self::FORGOT_PASSWORD,
                'name' => 'Forget password',
                'subject' => 'Reset your TMS password',
                'body' => <<<'BODY'
Hello {name},

We received a request to reset the password for {email}.

Choose a new password: {reset_url}

If you did not ask for this, you can ignore this email.
BODY,
                'is_active' => true,
            ],
            [
                'key' => self::PASSWORD_RESET,
                'name' => 'Reset password',
                'subject' => 'Your TMS password was changed',
                'body' => <<<'BODY'
Hello {name},

Your password has been changed.

Log in: {login_url}

If you did not make this change, contact support.
BODY,
                'is_active' => true,
            ],
        ];
    }

    public function summary(): string
    {
        return match ($this->key) {
            self::WELCOME => 'Sent when a member joins.',
            self::FORGOT_PASSWORD => 'Sent when a member asks to reset a forgotten password.',
            self::PASSWORD_RESET => 'Sent after a member chooses a new password.',
            default => 'Email template.',
        };
    }

    public function sendLabel(): string
    {
        return match ($this->key) {
            self::WELCOME => 'Send when a member joins',
            self::FORGOT_PASSWORD => 'Send the reset link',
            self::PASSWORD_RESET => 'Send after the password changes',
            default => 'Send this email',
        };
    }

    /**
     * Tokens an admin can place in this template.
     *
     * @return array<string, string>
     */
    public function placeholders(): array
    {
        $all = [
            '{name}' => 'Member name',
            '{user_name}' => 'Username',
            '{email}' => 'Email',
            '{phone_number}' => 'Phone number',
            '{sponsor_name}' => 'Sponsor',
            '{position}' => 'Left or right',
            '{password}' => 'Password',
            '{login_url}' => 'Login page',
            '{reset_url}' => 'Password reset link',
        ];

        $keys = match ($this->key) {
            self::FORGOT_PASSWORD => ['{name}', '{user_name}', '{email}', '{reset_url}'],
            self::PASSWORD_RESET => ['{name}', '{user_name}', '{email}', '{login_url}'],
            default => ['{name}', '{user_name}', '{email}', '{phone_number}', '{sponsor_name}', '{position}', '{password}', '{login_url}'],
        };

        return array_intersect_key($all, array_flip($keys));
    }

    /**
     * @param  array<string, string>  $extra
     * @return array<string, string>
     */
    public function tokens(User $member, array $extra = []): array
    {
        return array_merge([
            '{name}' => $member->name,
            '{user_name}' => (string) $member->user_name,
            '{email}' => $member->email,
            '{phone_number}' => (string) $member->phone_number,
            '{sponsor_name}' => (string) $member->sponsor_name,
            '{position}' => ucfirst((string) $member->position),
            '{password}' => '',
            '{login_url}' => route('login'),
            '{reset_url}' => '',
        ], $extra);
    }

    /**
     * @param  array<string, string>  $tokens
     */
    public function renderSubject(array $tokens): string
    {
        return trim(str_replace(["\r", "\n"], ' ', strtr($this->subject, $tokens)));
    }

    /**
     * @param  array<string, string>  $tokens
     */
    public function renderBody(array $tokens): string
    {
        return strtr($this->body, $tokens);
    }
}
