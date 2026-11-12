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
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('bv', 10, 2)->default(0)->after('price');
        });

        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'product_bv')) {
            return;
        }

        DB::table('order_items')
            ->whereNotNull('product_id')
            ->orderBy('id')
            ->lazyById()
            ->each(function (object $item): void {
                $bv = DB::table('products')->where('id', $item->product_id)->value('product_bv');

                if ($bv === null) {
                    return;
                }

                DB::table('order_items')->where('id', $item->id)->update([
                    'bv' => $bv,
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('bv');
        });
    }
};
