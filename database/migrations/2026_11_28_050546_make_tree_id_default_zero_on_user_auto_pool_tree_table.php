<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A seat with no parent stores tree_id 0. Zero is not a user, so the parent foreign key cannot stay.
     */
    public function up(): void
    {
        Schema::table('user_auto_pool_tree', function (Blueprint $table): void {
            $table->dropForeign(['tree_id']);
        });

        DB::table('user_auto_pool_tree')->whereNull('tree_id')->update(['tree_id' => 0]);

        Schema::table('user_auto_pool_tree', function (Blueprint $table): void {
            $table->unsignedBigInteger('tree_id')->default(0)->nullable(false)->change();
        });

        $hasTreeIndex = collect(Schema::getIndexes('user_auto_pool_tree'))
            ->contains(fn (array $index): bool => $index['columns'] === ['tree_id']);

        if (! $hasTreeIndex) {
            Schema::table('user_auto_pool_tree', function (Blueprint $table): void {
                $table->index('tree_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_auto_pool_tree', function (Blueprint $table): void {
            $table->unsignedBigInteger('tree_id')->nullable()->default(null)->change();
        });

        DB::table('user_auto_pool_tree')->where('tree_id', 0)->update(['tree_id' => null]);

        Schema::table('user_auto_pool_tree', function (Blueprint $table): void {
            $table->foreign('tree_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
