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
        Schema::table('package', function (Blueprint $table) {
            $table->decimal('direct_bv', 12, 2)->default(0)->after('package_bv');
        });

        foreach ($this->directTargets() as $name => $directBv) {
            DB::table('package')->where('package_name', $name)->update([
                'direct_bv' => $directBv,
            ]);
        }

        DB::table('package')->where('direct_bv', 0)->update([
            'direct_bv' => DB::raw('package_bv * 3'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package', function (Blueprint $table) {
            $table->dropColumn('direct_bv');
        });
    }

    /**
     * Direct selling BV saved with each package so approval can read it.
     *
     * @return array<string, string>
     */
    private function directTargets(): array
    {
        return [
            'SILVER PACKAGE' => '4500.00',
            'GOLD PACKAGE' => '15000.00',
            'DAIMOND' => '21000.00',
        ];
    }
};
