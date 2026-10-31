<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
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
        'product_bv',
        'compare_price',
        'stock',
        'image',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::updated(function (Product $product): void {
            if (! $product->wasChanged('image')) {
                return;
            }

            $previous = $product->getOriginal('image');

            if (! is_string($previous) || $previous === $product->image) {
                return;
            }

            static::deleteImageFile($previous);
        });

        static::deleting(function (Product $product): void {
            static::deleteImageFile($product->image);
        });
    }

    public static function imageDirectory(): string
    {
        return 'uploads/'.now()->format('Y/m').'/product/product-img';
    }

    public static function storeImage(UploadedFile $file): string
    {
        $directory = static::imageDirectory();
        $absolute = public_path($directory);

        if (! is_dir($absolute)) {
            mkdir($absolute, 0755, true);
        }

        $name = $file->hashName();
        $file->move($absolute, $name);

        return $directory.'/'.$name;
    }

    public static function deleteImageFile(?string $path): void
    {
        if (! filled($path)) {
            return;
        }

        $path = str_replace('\\', '/', $path);

        if (str_contains($path, '..') || str_starts_with($path, '/')) {
            return;
        }

        $publicFile = public_path($path);

        if (is_file($publicFile)) {
            $real = realpath($publicFile);
            $root = realpath(public_path());

            if ($real !== false && $root !== false && str_starts_with($real, $root.DIRECTORY_SEPARATOR)) {
                unlink($real);
            }
        }

        Storage::disk('public')->delete($path);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'product_bv' => 'decimal:2',
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

        $path = str_replace('\\', '/', $this->image);

        if (! str_contains($path, '..') && is_file(public_path($path))) {
            $encoded = implode('/', array_map(rawurlencode(...), explode('/', $path)));

            return asset($encoded);
        }

        return Storage::disk('public')->url($path);
    }

    public function formattedPrice(): string
    {
        return '₹'.number_format((float) $this->price, 2);
    }

    public function formattedBv(): string
    {
        return number_format((float) $this->product_bv, 2);
    }

    public function formattedComparePrice(): ?string
    {
        if ($this->compare_price === null) {
            return null;
        }

        return '₹'.number_format((float) $this->compare_price, 2);
    }
}
