<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The same member and the same parent can appear on more than one seat. A seat can have no parent.
     */
    public function up(): void
    {
        Schema::table('user_auto_pool_tree', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id', 'pool_type']);
            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->dropUnique(['pool_type', 'tree_id', 'position']);
            $table->index(['pool_type', 'tree_id', 'position']);
        });

        $hasTreeIndex = collect(Schema::getIndexes('user_auto_pool_tree'))
            ->contains(fn (array $index): bool => $index['columns'] === ['tree_id']);

        if (! $hasTreeIndex) {
            Schema::table('user_auto_pool_tree', function (Blueprint $table): void {
                $table->index('tree_id');
            });
        }

        $treeId = collect(Schema::getColumns('user_auto_pool_tree'))->firstWhere('name', 'tree_id');

        if (is_array($treeId) && ($treeId['nullable'] ?? false) !== true) {
            Schema::table('user_auto_pool_tree', function (Blueprint $table): void {
                $table->dropForeign(['tree_id']);
                $table->unsignedBigInteger('tree_id')->nullable()->change();
                $table->foreign('tree_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_auto_pool_tree', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->unique(['user_id', 'pool_type']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->dropIndex(['pool_type', 'tree_id', 'position']);
            $table->unique(['pool_type', 'tree_id', 'position']);
        });
    }
};
