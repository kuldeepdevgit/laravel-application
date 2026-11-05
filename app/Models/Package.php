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
        ];
    }

    public function isActive(): bool
    {
        return $this->package_status === 'active';
    }
}
