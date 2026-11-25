<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserPackageQualification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserPackageQualification>
 */
class UserPackageQualificationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'package_id' => null,
            'self_bv' => 0,
            'direct_bv' => 0,
            'is_qualified' => false,
            'qualified_at' => null,
        ];
    }
}
