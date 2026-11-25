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
        Schema::table('users_package_qualification', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
        });

        $packages = DB::table('package')->where('package_status', 'active')->get();
        $rows = DB::table('users_package_qualification')
            ->where('is_qualified', true)
            ->whereNotNull('package_id')
            ->get();
        $now = now();

        foreach ($rows as $row) {
            foreach ($packages as $package) {
                if ((int) $package->id === (int) $row->package_id) {
                    continue;
                }

                if ((float) $row->self_bv < (float) $package->package_bv || (float) $row->direct_bv < (float) $package->direct_bv) {
                    continue;
                }

                $exists = DB::table('users_package_qualification')
                    ->where('user_id', $row->user_id)
                    ->where('package_id', $package->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('users_package_qualification')->insert([
                    'user_id' => $row->user_id,
                    'package_id' => $package->id,
                    'self_bv' => $row->self_bv,
                    'direct_bv' => $row->direct_bv,
                    'is_qualified' => true,
                    'qualified_at' => $row->qualified_at ?? $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        Schema::table('users_package_qualification', function (Blueprint $table) {
            $table->unique(['user_id', 'package_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_package_qualification', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id', 'package_id']);
        });

        $rows = DB::table('users_package_qualification as qualification')
            ->leftJoin('package', 'package.id', '=', 'qualification.package_id')
            ->orderByDesc('package.package_bv')
            ->orderByDesc('qualification.id')
            ->get(['qualification.id', 'qualification.user_id']);
        $keep = [];
        $delete = [];

        foreach ($rows as $row) {
            if (isset($keep[$row->user_id])) {
                $delete[] = $row->id;

                continue;
            }

            $keep[$row->user_id] = $row->id;
        }

        if ($delete !== []) {
            DB::table('users_package_qualification')->whereIn('id', $delete)->delete();
        }

        Schema::table('users_package_qualification', function (Blueprint $table) {
            $table->unique('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
