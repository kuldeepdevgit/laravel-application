<?php

namespace App\Models;

use Database\Factories\FundAddressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FundAddress extends Model
{
    /** @use HasFactory<FundAddressFactory> */
    use HasFactory;

    protected $fillable = [
        'address_type',
        'wallet_address',
        'wallet_screenshot',
        'bank_name',
        'account_name',
        'account_number',
        'ifsc_code',
    ];

    protected static function booted(): void
    {
        static::deleting(function (FundAddress $address): void {
            if (filled($address->wallet_screenshot)) {
                Storage::disk('public')->delete($address->wallet_screenshot);
            }
        });
    }

    public function isUsdt(): bool
    {
        return $this->address_type === 'usdt';
    }

    public function label(): string
    {
        if ($this->isUsdt()) {
            return $this->wallet_address ?: 'USDT address';
        }

        $bank = $this->bank_name ?: 'INR address';

        return filled($this->account_number) ? $bank.' · '.$this->account_number : $bank;
    }

    public function screenshotUrl(): ?string
    {
        if (! filled($this->wallet_screenshot)) {
            return null;
        }

        return Storage::disk('public')->url($this->wallet_screenshot);
    }
}
