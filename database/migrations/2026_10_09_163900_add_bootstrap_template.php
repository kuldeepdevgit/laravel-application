<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('templates')) {
            return;
        }

        $exists = DB::table('templates')->where('key', 'bootstrap')->exists();

        if ($exists) {
            return;
        }

        $now = now();

        DB::table('templates')->insert([
            'key' => 'bootstrap',
            'name' => 'Bootstrap',
            'is_active' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('templates')) {
            return;
        }

        DB::table('templates')->where('key', 'bootstrap')->delete();
    }
};
