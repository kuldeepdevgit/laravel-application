<?php

use App\Models\SmtpSetting;
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
        Schema::create('smtp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('host');
            $table->unsignedSmallInteger('port');
            $table->string('encryption', 10)->default('tls');
            $table->string('username');
            $table->text('password');
            $table->string('from_address');
            $table->string('from_name');
            $table->timestamps();
        });

        if (Schema::hasTable('email_templates') && Schema::hasColumn('email_templates', 'smtp_host')) {
            $row = DB::table('email_templates')->orderBy('id')->first();

            if ($row !== null) {
                DB::table('smtp_settings')->insert([
                    'host' => $row->smtp_host,
                    'port' => $row->smtp_port,
                    'encryption' => $row->smtp_encryption,
                    'username' => $row->smtp_username,
                    'password' => $row->smtp_password,
                    'from_address' => $row->from_address,
                    'from_name' => $row->from_name,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }

            Schema::table('email_templates', function (Blueprint $table) {
                $table->dropColumn([
                    'smtp_host',
                    'smtp_port',
                    'smtp_encryption',
                    'smtp_username',
                    'smtp_password',
                    'from_address',
                    'from_name',
                ]);
            });
        }

        if (DB::table('smtp_settings')->count() === 0) {
            SmtpSetting::query()->create(SmtpSetting::defaults());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smtp_settings');
    }
};
