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
            $table->decimal('package_bv', 10, 2)->default(0)->after('package_amount');
        });

        DB::table('package')->update([
            'package_bv' => DB::raw('package_amount'),
        ]);

        $now = now();

        foreach ($this->packages() as $name => $amount) {
            $existing = DB::table('package')->where('package_name', $name)->first();

            if ($existing === null) {
                DB::table('package')->insert([
                    'package_name' => $name,
                    'package_amount' => $amount,
                    'package_bv' => $amount,
                    'package_status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                continue;
            }

            DB::table('package')->where('id', $existing->id)->update([
                'package_amount' => $amount,
                'package_bv' => $amount,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('package')->whereIn('package_name', array_keys($this->packages()))->delete();

        Schema::table('package', function (Blueprint $table) {
            $table->dropColumn('package_bv');
        });
    }

    /**
     * @return array<string, string>
     */
    private function packages(): array
    {
        return [
            'SILVER PACKAGE' => '1500.00',
            'GOLD PACKAGE' => '5000.00',
            'DAIMOND' => '7000.00',
        ];
    }
};
