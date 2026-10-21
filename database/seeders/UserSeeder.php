<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the super admin account.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'sponsor_name' => null,
                'user_name' => 'GSI1000001',
                'user_role' => 'super_admin',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );
    }
}
