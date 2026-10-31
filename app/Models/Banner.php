<?php

namespace App\Models;

use Database\Factories\BannerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    /** @use HasFactory<BannerFactory> */
    use HasFactory;

    protected $fillable = [
        'eyebrow',
        'title',
        'description',
        'button_label',
        'button_url',
        'image',
        'is_active',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::updated(function (Banner $banner): void {
            if (! $banner->wasChanged('image')) {
                return;
            }

            $previous = $banner->getOriginal('image');

            if (! is_string($previous) || $previous === $banner->image) {
                return;
            }

            static::deleteImageFile($previous);
        });

        static::deleting(function (Banner $banner): void {
            static::deleteImageFile($banner->image);
        });
    }

    public static function imageDirectory(): string
    {
        return 'uploads/'.now()->format('Y/m').'/banner/banner-img';
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
            'sort_order' => 'integer',
        ];
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
