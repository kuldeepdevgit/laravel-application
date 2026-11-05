<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = number_format(fake()->randomFloat(2, 100, 10000), 2, '.', '');

        return [
            'package_name' => fake()->unique()->words(2, true),
            'package_amount' => $amount,
            'package_bv' => $amount,
            'package_status' => 'active',
        ];
    }
}
