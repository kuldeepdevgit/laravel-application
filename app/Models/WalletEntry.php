<?php

namespace App\Models;

use Database\Factories\WalletEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WalletEntry extends Model
{
    /** @use HasFactory<WalletEntryFactory> */
    use HasFactory;

    public const CREDIT = 'credit';

    public const DEBIT = 'debit';

    public const SOURCE_FRANCHISE = 'franchise';

    protected $fillable = [
        'user_id',
        'income_id',
        'order_id',
        'direction',
        'source',
        'description',
        'amount',
        'balance',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    /**
     * Add a franchise income amount to the sponsor's wallet once.
     */
    public static function creditFromIncome(Income $income): ?self
    {
        if ($income->user_id === null || (float) $income->amount < 0.01) {
            return null;
        }

        $income->loadMissing('order');
        $number = $income->order?->number;
        $user = $income->user;

        if (! $user instanceof User) {
            return null;
        }

        return static::post(
            user: $user,
            direction: self::CREDIT,
            amount: (float) $income->amount,
            source: self::SOURCE_FRANCHISE,
            description: filled($number) ? 'Franchise income · '.$number : 'Franchise income',
            incomeId: (int) $income->getKey(),
            orderId: $income->order_id,
        );
    }

    public static function post(
        User $user,
        string $direction,
        float $amount,
        string $source,
        string $description,
        ?int $incomeId = null,
        ?int $orderId = null,
    ): self {
        if (! in_array($direction, [self::CREDIT, self::DEBIT], true)) {
            throw new InvalidArgumentException('A wallet entry must be a credit or a debit.');
        }

        $amount = round($amount, 2);

        if ($amount < 0.01) {
            throw new InvalidArgumentException('A wallet entry needs an amount greater than zero.');
        }

        return DB::transaction(function () use ($user, $direction, $amount, $source, $description, $incomeId, $orderId): self {
            User::query()->whereKey($user->getKey())->lockForUpdate()->first();

            if ($incomeId !== null) {
                $existing = static::query()->where('income_id', $incomeId)->first();

                if ($existing instanceof self) {
                    return $existing;
                }
            }

            $previous = round((float) static::query()
                ->where('user_id', $user->getKey())
                ->orderByDesc('id')
                ->value('balance'), 2);
            $balance = $direction === self::DEBIT
                ? round($previous - $amount, 2)
                : round($previous + $amount, 2);

            if ($balance < 0) {
                throw new InvalidArgumentException('The wallet balance is not enough for this debit.');
            }

            return static::query()->create([
                'user_id' => $user->getKey(),
                'income_id' => $incomeId,
                'order_id' => $orderId,
                'direction' => $direction,
                'source' => $source,
                'description' => $description,
                'amount' => $amount,
                'balance' => $balance,
            ]);
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Income, $this>
     */
    public function income(): BelongsTo
    {
        return $this->belongsTo(Income::class);
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isCredit(): bool
    {
        return $this->direction === self::CREDIT;
    }

    public function isDebit(): bool
    {
        return $this->direction === self::DEBIT;
    }

    public function formattedAmount(): string
    {
        return '₹'.number_format((float) $this->amount, 2);
    }

    public function formattedBalance(): string
    {
        return '₹'.number_format((float) $this->balance, 2);
    }

    public function creditAmount(): string
    {
        return $this->isCredit() ? $this->formattedAmount() : '—';
    }

    public function debitAmount(): string
    {
        return $this->isDebit() ? $this->formattedAmount() : '—';
    }
}
