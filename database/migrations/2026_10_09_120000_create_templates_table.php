<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        $now = now();

        DB::table('templates')->insert([
            ['key' => 'ecommerce', 'name' => 'Ecommerce', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'ai', 'name' => 'AI', 'is_active' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
