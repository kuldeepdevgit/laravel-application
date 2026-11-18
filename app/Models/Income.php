<?php

namespace App\Models;

use Database\Factories\IncomeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model
{
    /** @use HasFactory<IncomeFactory> */
    use HasFactory;

    public const TYPE_FRANCHISE = 'franchise';

    public const FRANCHISE_PERCENT = 10.0;

    protected $fillable = [
        'user_id',
        'order_id',
        'from_user_id',
        'type',
        'order_bv',
        'percent',
        'amount',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_bv' => 'decimal:2',
            'percent' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Credit the franchise sponsor 10% of the order BV the first time an order is approved.
     * A later approval of the same order does not credit it again.
     */
    public static function creditFranchiseFor(Order $order): ?self
    {
        if ($order->status !== 'confirmed') {
            return null;
        }

        $existing = static::query()
            ->where('order_id', $order->getKey())
            ->where('type', self::TYPE_FRANCHISE)
            ->first();

        if ($existing instanceof self) {
            return $existing;
        }

        $order->loadMissing('payment');

        $sponsorKey = $order->payment?->franchise_sponsor_id;

        if (! is_string($sponsorKey) || trim($sponsorKey) === '') {
            return null;
        }

        $sponsor = User::findSponsor($sponsorKey);

        if (! $sponsor instanceof User) {
            return null;
        }

        $orderBv = round((float) $order->total_bv, 2);
        $amount = round($orderBv * self::FRANCHISE_PERCENT / 100, 2);

        if ($amount < 0.01) {
            return null;
        }

        return static::query()->create([
            'user_id' => $sponsor->getKey(),
            'order_id' => $order->getKey(),
            'from_user_id' => $order->user_id,
            'type' => self::TYPE_FRANCHISE,
            'order_bv' => $orderBv,
            'percent' => self::FRANCHISE_PERCENT,
            'amount' => $amount,
        ]);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function formattedOrderBv(): string
    {
        return number_format((float) $this->order_bv, 2);
    }

    public function formattedPercent(): string
    {
        return rtrim(rtrim(number_format((float) $this->percent, 2, '.', ''), '0'), '.').'%';
    }

    public function formattedAmount(): string
    {
        return '₹'.number_format((float) $this->amount, 2);
    }
}
