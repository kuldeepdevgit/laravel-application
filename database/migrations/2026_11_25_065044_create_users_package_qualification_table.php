<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users_package_qualification', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('package')->nullOnDelete();
            $table->decimal('self_bv', 12, 2)->default(0);
            $table->decimal('direct_bv', 12, 2)->default(0);
            $table->boolean('is_qualified')->default(false);
            $table->timestamp('qualified_at')->nullable();
            $table->timestamps();

            $table->index('is_qualified');
            $table->index(['package_id', 'is_qualified']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_package_qualification');
    }
};
