<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 20, 400);

        return [
            'number' => 'NB-'.fake()->unique()->numberBetween(100000, 999999),
            'user_id' => null,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->optional()->numerify('##########'),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'region' => fake()->state(),
            'postal' => fake()->postcode(),
            'country' => 'India',
            'pay_method' => 'cash',
            'promo' => null,
            'subtotal' => $subtotal,
            'discount' => 0,
            'shipping' => 0,
            'total' => $subtotal,
        ];
    }
}
