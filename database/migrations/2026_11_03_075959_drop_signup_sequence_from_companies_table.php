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
        if (Schema::hasColumn('companies', 'signup_sequence')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('signup_sequence');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('companies', 'signup_sequence')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->unsignedInteger('signup_sequence')->default(0)->after('shortcode');
            });
        }
    }
};
