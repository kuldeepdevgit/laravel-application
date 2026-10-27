<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'compare_price',
        'stock',
        'image',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Product $product): void {
            if (filled($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function imageUrl(): ?string
    {
        if (! filled($this->image)) {
            return null;
        }

        return Storage::disk('public')->url($this->image);
    }

    public function formattedPrice(): string
    {
        return '₹'.number_format((float) $this->price, 2);
    }

    public function formattedComparePrice(): ?string
    {
        if ($this->compare_price === null) {
            return null;
        }

        return '₹'.number_format((float) $this->compare_price, 2);
    }
}
