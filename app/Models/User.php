<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\TemplateMailer;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'sponsor_name', 'position', 'user_name', 'user_role', 'email', 'phone_number', 'address', 'city', 'state', 'zip_code', 'usdt_bep20', 'password'])]
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

    public function isSuperAdmin(): bool
    {
        return (int) $this->getKey() === 1
            && $this->email === 'superadmin@software.com'
            && $this->user_role === 'super_admin';
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

    public static function sponsorFullName(string $sponsorName): ?string
    {
        $sponsorName = mb_strtolower(trim($sponsorName));

        if ($sponsorName === '') {
            return null;
        }

        $byUsername = static::query()
            ->whereRaw('LOWER(user_name) = ?', [$sponsorName])
            ->value('name');

        if (is_string($byUsername) && $byUsername !== '') {
            return $byUsername;
        }

        $byName = static::query()
            ->whereRaw('LOWER(name) = ?', [$sponsorName])
            ->value('name');

        return is_string($byName) && $byName !== '' ? $byName : null;
    }

    public static function generateUserName(): string
    {
        $shortcode = Company::query()
            ->where('is_active', true)
            ->whereNotNull('shortcode')
            ->where('shortcode', '!=', '')
            ->orderBy('id')
            ->value('shortcode');

        $prefix = is_string($shortcode) && $shortcode !== '' ? strtoupper($shortcode) : 'GS';

        do {
            $userName = $prefix.str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (static::query()->where('user_name', $userName)->exists());

        return $userName;
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Members who joined directly under this account.
     *
     * @return Collection<int, static>
     */
    public function directMembers(): Collection
    {
        $names = collect([$this->name, $this->user_name])
            ->filter(fn (mixed $name): bool => filled($name))
            ->map(fn (mixed $name): string => mb_strtolower(trim((string) $name)))
            ->unique()
            ->values();

        if ($names->isEmpty()) {
            return new Collection;
        }

        return static::query()
            ->where(function ($query) use ($names): void {
                foreach ($names as $name) {
                    $query->orWhereRaw('LOWER(sponsor_name) = ?', [$name]);
                }
            })
            ->whereKeyNot($this->getKey())
            ->orderBy('id')
            ->get();
    }

    public function sendPasswordResetNotification($token): void
    {
        $url = url(route('password.reset', [
            'token' => $token,
            'email' => $this->getEmailForPasswordReset(),
        ], false));

        if (config('mail.default') === 'log') {
            session()->flash('reset_url', $url);
        }

        app(TemplateMailer::class)->send($this, EmailTemplate::FORGOT_PASSWORD, [
            '{reset_url}' => $url,
        ]);
    }
}
