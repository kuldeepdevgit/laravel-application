<?php

namespace App\Models;

use Database\Factories\UserPackageQualificationFactory;
use Illuminate\Database\Eloquent\Collection;
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
     * Every active package this sponsor holds whose saved direct BV their direct total also reaches.
     * Diamond includes Silver and Gold because those lower targets are already met.
     *
     * @return Collection<int, Package>
     */
    public static function packagesFor(float $selfBv, float $directTotal): Collection
    {
        return Package::query()
            ->where('package_status', 'active')
            ->orderBy('package_bv')
            ->orderBy('id')
            ->get()
            ->filter(function (Package $package) use ($selfBv, $directTotal): bool {
                return round($selfBv, 2) >= round((float) $package->package_bv, 2)
                    && round($directTotal, 2) >= $package->directBv();
            })
            ->values();
    }

    /**
     * Highest active package this sponsor holds whose saved direct BV their direct total also reaches.
     */
    public static function forSponsor(float $selfBv, float $directTotal): ?Package
    {
        return static::packagesFor($selfBv, $directTotal)->last();
    }

    /**
     * Store one row for every package this member qualifies for.
     * A Diamond qualification also keeps Silver and Gold. Packages they no longer meet are removed.
     *
     * @return Collection<int, self>
     */
    public static function refreshFor(User $user): Collection
    {
        $selfBv = $user->currentBv();
        $directBv = $user->directSellingBv();
        $packages = static::packagesFor($selfBv, $directBv);

        if ($packages->isEmpty()) {
            return new Collection([static::storeUnqualified($user, $selfBv, $directBv)]);
        }

        $packageIds = $packages->map(fn (Package $package): int => (int) $package->getKey())->all();

        static::query()
            ->where('user_id', $user->getKey())
            ->where(function ($query) use ($packageIds): void {
                $query->whereNull('package_id')
                    ->orWhereNotIn('package_id', $packageIds);
            })
            ->delete();

        $existing = static::query()
            ->where('user_id', $user->getKey())
            ->whereIn('package_id', $packageIds)
            ->get()
            ->keyBy('package_id');
        $rows = new Collection;

        foreach ($packages as $package) {
            $current = $existing->get($package->getKey());
            $qualifiedAt = $current instanceof self && $current->qualified_at !== null
                ? $current->qualified_at
                : now();

            $rows->push(static::query()->updateOrCreate(
                [
                    'user_id' => $user->getKey(),
                    'package_id' => $package->getKey(),
                ],
                [
                    'self_bv' => $selfBv,
                    'direct_bv' => $directBv,
                    'is_qualified' => true,
                    'qualified_at' => $qualifiedAt,
                ],
            ));
        }

        return $rows;
    }

    private static function storeUnqualified(User $user, float $selfBv, float $directBv): self
    {
        static::query()->where('user_id', $user->getKey())->whereNotNull('package_id')->delete();

        $existing = static::query()->where('user_id', $user->getKey())->whereNull('package_id')->first();

        if ($existing instanceof self) {
            $existing->update([
                'self_bv' => $selfBv,
                'direct_bv' => $directBv,
                'is_qualified' => false,
                'qualified_at' => null,
            ]);

            return $existing;
        }

        return static::query()->create([
            'user_id' => $user->getKey(),
            'package_id' => null,
            'self_bv' => $selfBv,
            'direct_bv' => $directBv,
            'is_qualified' => false,
            'qualified_at' => null,
        ]);
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
