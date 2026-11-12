<?php

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'sku',
        'name',
        'qty',
        'price',
        'bv',
        'image',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'price' => 'decimal:2',
            'bv' => 'decimal:2',
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
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function lineTotal(): float
    {
        return round((float) $this->price * $this->qty, 2);
    }

    public function formattedPrice(): string
    {
        return '₹'.number_format((float) $this->price, 2);
    }

    public function formattedLineTotal(): string
    {
        return '₹'.number_format($this->lineTotal(), 2);
    }

    public function lineBv(): float
    {
        return round((float) $this->bv * $this->qty, 2);
    }

    public function formattedBv(): string
    {
        return number_format((float) $this->bv, 2);
    }

    public function formattedLineBv(): string
    {
        return number_format($this->lineBv(), 2);
    }

    public function imageUrl(): ?string
    {
        if (! filled($this->image)) {
            return null;
        }

        $image = str_replace('\\', '/', $this->image);

        if (str_starts_with($image, 'https://') || str_starts_with($image, 'http://')) {
            return $image;
        }

        if (str_contains($image, '..') || str_starts_with($image, '/')) {
            return null;
        }

        return asset($image);
    }
}
