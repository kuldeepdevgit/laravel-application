<?php

namespace App\Models;

use Database\Factories\UserAutoPoolTreeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class UserAutoPoolTree extends Model
{
    /** @use HasFactory<UserAutoPoolTreeFactory> */
    use HasFactory;

    public const TREE_NONE = 0;

    public const POSITION_ONE = 1;

    public const POSITION_TWO = 2;

    public const POOL_SILVER = 'silver';

    public const POOL_GOLD = 'gold';

    public const POOL_DIAMOND = 'diamond';

    /**
     * @var list<string>
     */
    public const POOLS = [
        self::POOL_SILVER,
        self::POOL_GOLD,
        self::POOL_DIAMOND,
    ];

    protected $table = 'user_auto_pool_tree';

    protected $fillable = [
        'user_id',
        'tree_id',
        'position',
        'pool_type',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'tree_id' => self::TREE_NONE,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tree_id' => 'integer',
            'position' => 'integer',
        ];
    }

    /**
     * Member who holds this seat.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Parent member this seat sits under. tree_id 0 means there is no parent.
     *
     * @return BelongsTo<User, $this>
     */
    public function tree(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tree_id');
    }

    public function poolLabel(): string
    {
        return match ($this->pool_type) {
            self::POOL_SILVER => 'Silver',
            self::POOL_GOLD => 'Gold',
            self::POOL_DIAMOND => 'Diamond',
            default => ucfirst((string) $this->pool_type),
        };
    }

    /**
     * Three levels of this member's 2x matrix.
     * The member is the top seat when they hold one. Each seat has a left place and a right place.
     *
     * @return array{user: User|null, children: list<array{user: User|null, children: list<array{user: User|null, children: array<int, mixed>}>}>}
     */
    public static function matrixUnder(User $member, string $pool): array
    {
        $hasSeat = static::query()
            ->where('user_id', $member->getKey())
            ->where('pool_type', $pool)
            ->exists();
        $direct = static::seatsUnder((int) $member->getKey(), $pool);
        $childIds = collect($direct)
            ->filter(fn (?self $seat): bool => $seat?->user instanceof User && ! $seat->user->is($member))
            ->map(fn (self $seat): int => (int) $seat->user_id)
            ->unique()
            ->values();
        $grandchildren = $childIds->isEmpty()
            ? collect()
            : static::query()
                ->with('user')
                ->where('pool_type', $pool)
                ->whereIn('tree_id', $childIds->all())
                ->orderBy('id')
                ->get()
                ->groupBy(fn (self $seat): int => (int) $seat->tree_id);

        return [
            'user' => $hasSeat ? $member : null,
            'children' => [
                static::matrixBranch($direct[self::POSITION_ONE] ?? null, $member, $grandchildren),
                static::matrixBranch($direct[self::POSITION_TWO] ?? null, $member, $grandchildren),
            ],
        ];
    }

    /**
     * @param  Collection<int, Collection<int, self>>  $grandchildren
     * @return array{user: User|null, children: list<array{user: User|null, children: array<int, mixed>}>}
     */
    private static function matrixBranch(?self $seat, User $member, Collection $grandchildren): array
    {
        $child = $seat?->user;

        if (! $child instanceof User || $child->is($member)) {
            return static::emptyMatrix(1);
        }

        $kids = $grandchildren
            ->get((int) $child->getKey(), collect())
            ->filter(fn (self $row): bool => $row->user instanceof User && ! $row->user->is($child))
            ->unique('position')
            ->keyBy('position');

        return [
            'user' => $child,
            'children' => [
                static::matrixLeaf($kids->get(self::POSITION_ONE)),
                static::matrixLeaf($kids->get(self::POSITION_TWO)),
            ],
        ];
    }

    /**
     * @return array{user: User|null, children: array<int, mixed>}
     */
    private static function matrixLeaf(?self $seat): array
    {
        $user = $seat?->user;

        return [
            'user' => $user instanceof User ? $user : null,
            'children' => [],
        ];
    }

    /**
     * @return array{user: null, children: list<array<string, mixed>>|array<int, mixed>}
     */
    private static function emptyMatrix(int $depth): array
    {
        $children = [];

        if ($depth > 0) {
            $children = [
                static::emptyMatrix($depth - 1),
                static::emptyMatrix($depth - 1),
            ];
        }

        return [
            'user' => null,
            'children' => $children,
        ];
    }

    /**
     * First seat in each of the two places under a parent.
     *
     * @return array<int, self|null>
     */
    private static function seatsUnder(int $parentId, string $pool): array
    {
        if ($parentId === self::TREE_NONE) {
            return [
                self::POSITION_ONE => null,
                self::POSITION_TWO => null,
            ];
        }

        $rows = static::query()
            ->with('user')
            ->where('pool_type', $pool)
            ->where('tree_id', $parentId)
            ->orderBy('id')
            ->get()
            ->unique('position')
            ->keyBy('position');

        return [
            self::POSITION_ONE => $rows->get(self::POSITION_ONE),
            self::POSITION_TWO => $rows->get(self::POSITION_TWO),
        ];
    }
}
