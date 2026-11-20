<?php

namespace App\Models;

use Database\Factories\LevelIncomeHistoryFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\UniqueConstraintViolationException;

class LevelIncomeHistory extends Model
{
    /** @use HasFactory<LevelIncomeHistoryFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CREDITED = 'credited';

    public const STATUS_REJECTED = 'rejected';

    public const LEVEL_ONE = 1;

    public const LEVEL_TWO = 2;

    public const INCOME_PERCENTAGE = 40.0;

    public const LEVEL_SHARE = 50.0;

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
     * Credit the buyer's two uplines from 40% of this order's BV.
     * That income is split in half: level 1 is the direct sponsor, level 2 is that sponsor's sponsor.
     * A 1,500 BV order pays 300 to each level. Confirming the same order again does not credit it a second time.
     *
     * @return Collection<int, self>
     */
    public static function creditFor(Order $order): Collection
    {
        if ($order->status !== 'confirmed' || $order->user_id === null) {
            return new Collection;
        }

        $existing = static::query()
            ->where('order_id', $order->getKey())
            ->orderBy('level')
            ->get();

        if ($existing->isNotEmpty()) {
            return $existing;
        }

        $order->loadMissing('user');
        $buyer = $order->user;

        if (! $buyer instanceof User) {
            return new Collection;
        }

        $orderBv = round((float) $order->total_bv, 2);
        $levelPercentage = round(self::INCOME_PERCENTAGE * self::LEVEL_SHARE / 100, 2);
        $income = round($orderBv * $levelPercentage / 100, 2);

        if ($income < 0.01) {
            return new Collection;
        }

        $rows = new Collection;
        $upline = $buyer->sponsor();
        /** @var array<int, true> $seen */
        $seen = [(int) $buyer->getKey() => true];

        for ($level = self::LEVEL_ONE; $level <= self::LEVEL_TWO; $level++) {
            if (! $upline instanceof User || isset($seen[(int) $upline->getKey()])) {
                break;
            }

            $seen[(int) $upline->getKey()] = true;

            try {
                $rows->push(static::query()->create([
                    'user_id' => $upline->getKey(),
                    'pay_user_id' => $buyer->getKey(),
                    'level' => $level,
                    'order_id' => $order->getKey(),
                    'order_bv' => $orderBv,
                    'income' => $income,
                    'percentage' => $levelPercentage,
                    'income_status' => self::STATUS_CREDITED,
                ]));
            } catch (UniqueConstraintViolationException) {
                $row = static::query()
                    ->where('order_id', $order->getKey())
                    ->where('level', $level)
                    ->first();

                if ($row instanceof self) {
                    $rows->push($row);
                }
            }

            $upline = $upline->sponsor();
        }

        return $rows;
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

    public function formattedIncome(): string
    {
        return '₹'.number_format((float) $this->income, 2);
    }

    public function formattedPercentage(): string
    {
        return rtrim(rtrim(number_format((float) $this->percentage, 2, '.', ''), '0'), '.').'%';
    }
}
