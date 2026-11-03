<?php

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'email',
        'phone',
        'address',
        'website',
        'website_role',
        'shortcode',
        'logo',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Company $company): void {
            if (filled($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'signup_sequence' => 'integer',
        ];
    }

    /**
     * The next signup username for this company, such as GSI0001.
     */
    public function nextUserName(): string
    {
        return DB::transaction(function (): string {
            $company = static::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();
            $prefix = strtoupper((string) $company->shortcode);
            $number = (int) $company->signup_sequence;

            do {
                $number++;
                $userName = $prefix.str_pad((string) $number, 4, '0', STR_PAD_LEFT);
            } while (User::query()->where('user_name', $userName)->exists());

            $company->signup_sequence = $number;
            $company->save();

            return $userName;
        });
    }

    public function logoUrl(): ?string
    {
        if (! filled($this->logo)) {
            return null;
        }

        return Storage::disk('public')->url($this->logo);
    }
}
