<?php

namespace App\Models;

use Database\Factories\UserPackageQualificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPackageQualification extends Model
{
    /** @use HasFactory<UserPackageQualificationFactory> */
    use HasFactory;

    protected $table = 'users_package_qualification';

    protected $fillable = [
        'user_id',
        'package_id',
        'self_bv',
        'direct_bv',
        'is_qualified',
        'qualified_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'package_id' => 'integer',
            'self_bv' => 'decimal:2',
            'direct_bv' => 'decimal:2',
            'is_qualified' => 'boolean',
            'qualified_at' => 'datetime',
        ];
    }

    /**
     * Highest active package this sponsor holds whose saved direct BV their direct total also reaches.
     * Holding a package means their own confirmed BV is at least that package BV.
     */
    public static function forSponsor(float $selfBv, float $directTotal): ?Package
    {
        return Package::query()
            ->where('package_status', 'active')
            ->orderByDesc('package_bv')
            ->orderByDesc('id')
            ->get()
            ->first(function (Package $package) use ($selfBv, $directTotal): bool {
                return round($selfBv, 2) >= round((float) $package->package_bv, 2)
                    && round($directTotal, 2) >= $package->directBv();
            });
    }

    /**
     * Replace this member's qualification with the totals from confirmed orders.
     */
    public static function refreshFor(User $user): self
    {
        $selfBv = $user->currentBv();
        $directBv = $user->directSellingBv();
        $package = static::forSponsor($selfBv, $directBv);
        $existing = static::query()->where('user_id', $user->getKey())->first();

        $qualifiedAt = null;

        if ($package instanceof Package) {
            $samePackage = $existing instanceof self
                && (int) $existing->package_id === (int) $package->getKey()
                && $existing->qualified_at !== null;
            $qualifiedAt = $samePackage ? $existing->qualified_at : now();
        }

        return static::query()->updateOrCreate(
            ['user_id' => $user->getKey()],
            [
                'package_id' => $package?->getKey(),
                'self_bv' => $selfBv,
                'direct_bv' => $directBv,
                'is_qualified' => $package instanceof Package,
                'qualified_at' => $qualifiedAt,
            ],
        );
    }

    /**
     * When an order is approved, check the buyer's sponsor.
     * The sponsor must hold a package, and the total BV of members who joined directly under them must reach the direct BV saved on that package.
     * The buyer is checked with the same rule because their own confirmed BV is what lets them hold a package.
     */
    public static function refreshForOrder(Order $order): void
    {
        $order->loadMissing('user');
        $buyer = $order->user;

        if (! $buyer instanceof User) {
            return;
        }

        $sponsor = $buyer->sponsor();

        if ($sponsor instanceof User && ! $sponsor->is($buyer)) {
            static::refreshFor($sponsor);
        }

        static::refreshFor($buyer);
    }

    public function qualifiesFor(Package $package): bool
    {
        return round((float) $this->self_bv, 2) >= round((float) $package->package_bv, 2)
            && round((float) $this->direct_bv, 2) >= $package->directBv();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Package, $this>
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
