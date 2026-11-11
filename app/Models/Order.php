<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'number',
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'region',
        'postal',
        'country',
        'pay_method',
        'promo',
        'subtotal',
        'discount',
        'shipping',
        'total',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function formattedSubtotal(): string
    {
        return $this->rupees($this->subtotal);
    }

    public function formattedDiscount(): string
    {
        return $this->rupees($this->discount);
    }

    public function formattedShipping(): string
    {
        return $this->rupees($this->shipping);
    }

    public function formattedTotal(): string
    {
        return $this->rupees($this->total);
    }

    public function paymentLabel(): string
    {
        return match ($this->pay_method) {
            'cash' => 'Cash',
            default => ucfirst(str_replace('_', ' ', $this->pay_method)),
        };
    }

    public function shippingAddress(): string
    {
        return collect([$this->address, $this->city, $this->region, $this->postal, $this->country])
            ->filter(fn (mixed $part): bool => filled($part))
            ->implode(', ');
    }

    private function rupees(mixed $amount): string
    {
        return '₹'.number_format((float) $amount, 2);
    }
}
