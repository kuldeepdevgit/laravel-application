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
        Schema::table('users', function (Blueprint $table) {
            $table->string('bank_name', 120)->nullable()->after('usdt_bep20');
            $table->string('account_name', 120)->nullable()->after('bank_name');
            $table->string('account_number', 20)->nullable()->after('account_name');
            $table->string('ifsc_code', 11)->nullable()->after('account_number');
            $table->string('google_pay')->nullable()->after('ifsc_code');
            $table->string('phonepe')->nullable()->after('google_pay');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'account_name',
                'account_number',
                'ifsc_code',
                'google_pay',
                'phonepe',
            ]);
        });
    }
};
