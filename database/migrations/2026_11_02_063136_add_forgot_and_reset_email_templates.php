<?php

use App\Models\EmailTemplate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('email_templates')) {
            return;
        }

        $hasWelcome = DB::table('email_templates')->where('key', EmailTemplate::WELCOME)->exists();

        if (! $hasWelcome) {
            DB::table('email_templates')->where('key', 'member_joined')->update([
                'key' => EmailTemplate::WELCOME,
                'name' => 'Welcome mail',
                'updated_at' => now(),
            ]);
        }

        foreach (EmailTemplate::catalog() as $attributes) {
            $exists = DB::table('email_templates')->where('key', $attributes['key'])->exists();

            if (! $exists) {
                EmailTemplate::query()->create($attributes);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('email_templates')->whereIn('key', [
            EmailTemplate::FORGOT_PASSWORD,
            EmailTemplate::PASSWORD_RESET,
        ])->delete();

        DB::table('email_templates')->where('key', EmailTemplate::WELCOME)->update([
            'key' => 'member_joined',
            'name' => 'Member joined',
        ]);
    }
};
