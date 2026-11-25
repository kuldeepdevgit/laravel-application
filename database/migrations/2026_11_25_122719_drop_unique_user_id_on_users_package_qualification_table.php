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
        $indexes = collect(Schema::getIndexes('users_package_qualification'));
        $userUniques = $indexes->filter(fn (array $index): bool => (bool) $index['unique']
            && ! (bool) $index['primary']
            && in_array('user_id', $index['columns'], true));
        $hasUserIndex = $indexes->contains(fn (array $index): bool => $index['columns'] === ['user_id'] && ! (bool) $index['unique']);

        if ($userUniques->isEmpty()) {
            if (! $hasUserIndex) {
                Schema::table('users_package_qualification', function (Blueprint $table): void {
                    $table->index('user_id');
                });
            }

            return;
        }

        Schema::table('users_package_qualification', function (Blueprint $table) use ($userUniques, $hasUserIndex): void {
            $table->dropForeign(['user_id']);

            foreach ($userUniques as $index) {
                $table->dropUnique($index['columns']);
            }

            if (! $hasUserIndex) {
                $table->index('user_id');
            }

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // user_id stays a normal index so one member can have a row for each package.
    }
};
