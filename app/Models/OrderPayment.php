<?php

namespace App\Models;

use Database\Factories\OrderPaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class OrderPayment extends Model
{
    /** @use HasFactory<OrderPaymentFactory> */
    use HasFactory;

    /**
     * @var array<string, string>
     */
    public const MODES = [
        'cash' => 'Cash',
        'google_pay' => 'Google Pay',
        'phone_pe' => 'PhonePe',
        'bank' => 'Bank',
    ];

    protected $fillable = [
        'order_id',
        'user_id',
        'franchise_sponsor_id',
        'amount',
        'screenshot',
        'pay_mode',
        'remark',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payModeLabel(): string
    {
        return self::MODES[$this->pay_mode] ?? ucfirst(str_replace('_', ' ', (string) $this->pay_mode));
    }

    public function formattedAmount(): string
    {
        return '₹'.number_format((float) $this->amount, 2);
    }

    public function screenshotUrl(): ?string
    {
        if (! filled($this->screenshot)) {
            return null;
        }

        return Storage::disk('public')->url($this->screenshot);
    }
}
