<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_bv', 10, 2)->default(0)->after('total');
        });

        if (! Schema::hasTable('order_items') || ! Schema::hasColumn('order_items', 'bv')) {
            return;
        }

        DB::table('orders')
            ->orderBy('id')
            ->lazyById()
            ->each(function (object $order): void {
                $totalBv = DB::table('order_items')
                    ->where('order_id', $order->id)
                    ->sum(DB::raw('bv * qty'));

                DB::table('orders')->where('id', $order->id)->update([
                    'total_bv' => round((float) $totalBv, 2),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('total_bv');
        });
    }
};
