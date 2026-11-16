<?php

namespace App\Models;

use Database\Factories\OrderPaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
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

    protected static function booted(): void
    {
        static::updated(function (OrderPayment $payment): void {
            if (! $payment->wasChanged('screenshot')) {
                return;
            }

            $previous = $payment->getOriginal('screenshot');

            if (! is_string($previous) || $previous === $payment->screenshot) {
                return;
            }

            static::deleteImageFile($previous);
        });

        static::deleting(function (OrderPayment $payment): void {
            static::deleteImageFile($payment->screenshot);
        });
    }

    public static function imageDirectory(): string
    {
        return 'uploads/'.now()->format('Y/m').'/payment/payment-img';
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

        $path = str_replace('\\', '/', $this->screenshot);

        if (! str_contains($path, '..') && is_file(public_path($path))) {
            $encoded = implode('/', array_map(rawurlencode(...), explode('/', $path)));

            return asset($encoded);
        }

        return Storage::disk('public')->url($path);
    }
}
