<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'description' => fake()->sentence(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->numerify('98########'),
            'address' => fake()->streetAddress(),
            'website' => fake()->url(),
            'logo' => null,
            'is_active' => true,
        ];
    }
}
