<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Auto pool seats. tree_id is the parent member. Position is 1 or 2. Pool is silver, gold, or diamond.
     */
    public function up(): void
    {
        Schema::create('user_auto_pool_tree', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tree_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('position');
            $table->string('pool_type', 16);
            $table->timestamps();

            $table->unique(['user_id', 'pool_type']);
            $table->unique(['pool_type', 'tree_id', 'position']);
            $table->index('pool_type');
            $table->index(['pool_type', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_auto_pool_tree');
    }
};
