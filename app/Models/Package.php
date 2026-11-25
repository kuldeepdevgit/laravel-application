<?php

namespace App\Models;

use Database\Factories\PackageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    /** @use HasFactory<PackageFactory> */
    use HasFactory;

    protected $table = 'package';

    protected $fillable = [
        'package_name',
        'package_amount',
        'package_bv',
        'direct_bv',
        'package_status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'package_amount' => 'decimal:2',
            'package_bv' => 'decimal:2',
            'direct_bv' => 'decimal:2',
        ];
    }

    public function isActive(): bool
    {
        return $this->package_status === 'active';
    }

    /**
     * Confirmed BV required from members who joined directly under the sponsor.
     */
    public function directBv(): float
    {
        return round((float) $this->direct_bv, 2);
    }

    /**
     * Highest active package this BV qualifies for.
     */
    public static function forBv(float $bv): ?self
    {
        return static::query()
            ->where('package_status', 'active')
            ->where('package_bv', '<=', number_format($bv, 2, '.', ''))
            ->orderByDesc('package_bv')
            ->orderByDesc('id')
            ->first();
    }
}
