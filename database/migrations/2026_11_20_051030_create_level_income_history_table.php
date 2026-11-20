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
        Schema::create('level_income_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pay_user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedTinyInteger('level');
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('order_bv', 12, 2);
            $table->decimal('income', 12, 2);
            $table->decimal('percentage', 5, 2);
            $table->string('income_status', 16)->default('pending');
            $table->timestamps();

            $table->index(['user_id', 'level']);
            $table->index(['user_id', 'income_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level_income_history');
    }
};
