<?php

namespace App\Models;

use Database\Factories\LevelIncomeHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LevelIncomeHistory extends Model
{
    /** @use HasFactory<LevelIncomeHistoryFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CREDITED = 'credited';

    public const STATUS_REJECTED = 'rejected';

    public const LEVEL_ONE = 1;

    public const LEVEL_TWO = 2;

    protected $table = 'level_income_history';

    protected $fillable = [
        'user_id',
        'pay_user_id',
        'level',
        'order_id',
        'order_bv',
        'income',
        'percentage',
        'income_status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'order_bv' => 'decimal:2',
            'income' => 'decimal:2',
            'percentage' => 'decimal:2',
        ];
    }

    /**
     * Member who receives this level income.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Member whose order generated this level income.
     *
     * @return BelongsTo<User, $this>
     */
    public function payUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pay_user_id');
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
