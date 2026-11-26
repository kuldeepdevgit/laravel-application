<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\TemplateMailer;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'sponsor_name', 'position', 'user_name', 'user_role', 'is_franchise', 'email', 'phone_number', 'address', 'city', 'state', 'zip_code', 'usdt_bep20', 'bank_name', 'account_name', 'account_number', 'ifsc_code', 'google_pay', 'phonepe', 'user_paid', 'activation_date', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const DOWNLINE_DEPTH = 10;

    public const PAID = 'paid';

    public const UNPAID = 'unpaid';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'activation_date' => 'date',
            'is_franchise' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function isPaid(): bool
    {
        return $this->user_paid === self::PAID;
    }

    public function isFranchise(): bool
    {
        return (bool) $this->is_franchise;
    }

    /**
     * Mark the account paid the first time an order is approved.
     * A later approval leaves user_paid and activation_date as they are.
     */
    public function markPaidOnFirstApproval(): void
    {
        if ($this->isPaid()) {
            return;
        }

        $attributes = [
            'user_paid' => self::PAID,
        ];

        if ($this->activation_date === null) {
            $attributes['activation_date'] = now()->toDateString();
        }

        $this->update($attributes);
    }

    public function isSuperAdmin(): bool
    {
        return (int) $this->getKey() === 1
            && $this->email === 'superadmin@software.com'
            && $this->user_role === 'super_admin';
    }

    public static function isValidSponsor(string $sponsorName): bool
    {
        return static::findSponsor($sponsorName) instanceof self;
    }

    public static function isValidFranchise(string $sponsorName): bool
    {
        return static::findFranchise($sponsorName) instanceof self;
    }

    public static function findFranchise(string $sponsorName): ?self
    {
        $sponsor = static::findSponsor($sponsorName);

        if (! $sponsor instanceof self || ! $sponsor->isFranchise()) {
            return null;
        }

        return $sponsor;
    }

    public static function findSponsor(string $sponsorName): ?self
    {
        $sponsorName = mb_strtolower(trim($sponsorName));

        if ($sponsorName === '') {
            return null;
        }

        $byUsername = static::query()
            ->whereRaw('LOWER(user_name) = ?', [$sponsorName])
            ->first();

        if ($byUsername instanceof self) {
            return $byUsername;
        }

        return static::query()
            ->whereRaw('LOWER(name) = ?', [$sponsorName])
            ->first();
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

    /**
     * The account stored as this member's sponsor, matched by user ID or name.
     */
    public function sponsor(): ?self
    {
        $sponsorName = mb_strtolower(trim((string) $this->sponsor_name));

        if ($sponsorName === '') {
            return null;
        }

        $byUsername = static::query()
            ->whereKeyNot($this->getKey())
            ->whereRaw('LOWER(user_name) = ?', [$sponsorName])
            ->first();

        if ($byUsername instanceof self) {
            return $byUsername;
        }

        return static::query()
            ->whereKeyNot($this->getKey())
            ->whereRaw('LOWER(name) = ?', [$sponsorName])
            ->orderBy('id')
            ->first();
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
     * One row for each package this member qualifies for.
     *
     * @return HasMany<UserPackageQualification, $this>
     */
    public function packageQualifications(): HasMany
    {
        return $this->hasMany(UserPackageQualification::class);
    }

    /**
     * BV from this member's paid or confirmed orders.
     */
    public function currentBv(): float
    {
        return round((float) $this->orders()->whereIn('status', Order::BV_STATUSES)->sum('total_bv'), 2);
    }

    /**
     * Paid or confirmed product BV from members who joined directly under this account.
     */
    public function directSellingBv(): float
    {
        $keys = $this->sponsorMatchKeys();

        if ($keys === []) {
            return 0.0;
        }

        $placeholders = implode(', ', array_fill(0, count($keys), '?'));

        return round((float) Order::query()
            ->whereIn('status', Order::BV_STATUSES)
            ->whereHas('user', function (Builder $query) use ($keys, $placeholders): void {
                $query->whereKeyNot($this->getKey())
                    ->whereRaw('LOWER(sponsor_name) IN ('.$placeholders.')', $keys);
            })
            ->sum('total_bv'), 2);
    }

    /**
     * Paid or confirmed order BV for a team-list row.
     */
    public function formattedSelfInvestment(): string
    {
        return number_format((float) $this->getAttribute('self_investment'), 2);
    }

    /**
     * @return HasMany<Income, $this>
     */
    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    /**
     * Level income credited to this member.
     *
     * @return HasMany<LevelIncomeHistory, $this>
     */
    public function levelIncomeHistories(): HasMany
    {
        return $this->hasMany(LevelIncomeHistory::class);
    }

    /**
     * Auto pool seats held by this member.
     *
     * @return HasMany<UserAutoPoolTree, $this>
     */
    public function autoPoolTrees(): HasMany
    {
        return $this->hasMany(UserAutoPoolTree::class);
    }

    /**
     * @return HasMany<WalletEntry, $this>
     */
    public function walletEntries(): HasMany
    {
        return $this->hasMany(WalletEntry::class);
    }

    /**
     * Members who joined directly under this account.
     *
     * @return Collection<int, static>
     */
    public function directMembers(): Collection
    {
        return $this->membersSponsoredBy($this->sponsorMatchKeys())
            ->reject(fn (self $member): bool => $member->is($this))
            ->values();
    }

    /**
     * Members in this account's downline, from direct referrals through the given depth.
     * Each member has downline_level set, where 1 is a direct member.
     *
     * @return Collection<int, static>
     */
    public function downlineMembers(int $depth = self::DOWNLINE_DEPTH): Collection
    {
        $members = new Collection;
        /** @var array<int, true> $seen */
        $seen = [(int) $this->getKey() => true];
        $keys = $this->sponsorMatchKeys();

        for ($level = 1; $level <= $depth && $keys !== []; $level++) {
            $levelMembers = $this->membersSponsoredBy($keys)
                ->reject(fn (self $member): bool => isset($seen[(int) $member->getKey()]))
                ->values();

            if ($levelMembers->isEmpty()) {
                break;
            }

            $nextKeys = [];

            foreach ($levelMembers as $member) {
                $seen[(int) $member->getKey()] = true;
                $member->setAttribute('downline_level', $level);
                $members->push($member);

                foreach ($member->sponsorMatchKeys() as $key) {
                    $nextKeys[$key] = $key;
                }
            }

            $keys = array_values($nextKeys);
        }

        return $members;
    }

    /**
     * Names and usernames that a new member can store as sponsor_name.
     *
     * @return list<string>
     */
    private function sponsorMatchKeys(): array
    {
        return collect([$this->name, $this->user_name])
            ->filter(fn (mixed $name): bool => filled($name))
            ->map(fn (mixed $name): string => mb_strtolower(trim((string) $name)))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $keys
     * @return Collection<int, static>
     */
    private function membersSponsoredBy(array $keys): Collection
    {
        if ($keys === []) {
            return new Collection;
        }

        $placeholders = implode(', ', array_fill(0, count($keys), '?'));

        return static::query()
            ->withSum(['orders as self_investment' => function (Builder $query): void {
                $query->whereIn('status', Order::BV_STATUSES);
            }], 'total_bv')
            ->whereRaw('LOWER(sponsor_name) IN ('.$placeholders.')', $keys)
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
