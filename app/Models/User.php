<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'sponsor_name', 'user_name', 'user_role', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public static function isValidSponsor(string $sponsorName): bool
    {
        $sponsorName = mb_strtolower(trim($sponsorName));

        if ($sponsorName === '') {
            return false;
        }

        return static::query()
            ->whereRaw('LOWER(name) = ?', [$sponsorName])
            ->orWhereRaw('LOWER(user_name) = ?', [$sponsorName])
            ->exists();
    }

    public static function generateUserName(): string
    {
        do {
            $userName = 'GSI'.random_int(1000000, 9999999);
        } while (static::query()->where('user_name', $userName)->exists());

        return $userName;
    }

    public function sendPasswordResetNotification($token): void
    {
        if (config('mail.default') === 'log') {
            session()->flash('reset_url', url(route('password.reset', [
                'token' => $token,
                'email' => $this->getEmailForPasswordReset(),
            ], false)));
        }

        $this->notify(new ResetPassword($token));
    }
}
