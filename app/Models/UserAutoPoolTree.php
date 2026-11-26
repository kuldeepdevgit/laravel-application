<?php

namespace App\Models;

use Database\Factories\UserAutoPoolTreeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAutoPoolTree extends Model
{
    /** @use HasFactory<UserAutoPoolTreeFactory> */
    use HasFactory;

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
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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
     * Parent member this seat sits under.
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
}
