<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Template extends Model
{
    protected $fillable = ['key', 'name', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function activeKey(): string
    {
        if (! Schema::hasTable('templates')) {
            return 'ecommerce';
        }

        return static::query()->where('is_active', true)->value('key') ?? 'ecommerce';
    }

    public function activate(): void
    {
        static::query()->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }
}
