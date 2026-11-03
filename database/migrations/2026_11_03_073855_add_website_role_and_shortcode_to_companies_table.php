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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('website_role', 120)->nullable()->after('website');
            $table->string('shortcode', 10)->nullable()->unique()->after('website_role');
            $table->unsignedInteger('signup_sequence')->default(0)->after('shortcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropUnique(['shortcode']);
            $table->dropColumn(['website_role', 'shortcode', 'signup_sequence']);
        });
    }
};
