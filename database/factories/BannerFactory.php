<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'eyebrow' => 'New this week',
            'title' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
            'button_label' => 'Shop now',
            'button_url' => '/shop',
            'image' => null,
            'is_active' => true,
            'sort_order' => 1,
        ];
    }
}
