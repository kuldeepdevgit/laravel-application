<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Category $category): void {
            static::deleteImageFile($category->image);
        });
    }

    public static function imageDirectory(): string
    {
        return 'uploads/'.now()->format('Y/m').'/category/category-img';
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
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
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
}
