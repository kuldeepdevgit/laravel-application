<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => null,
            'sku' => fake()->slug(2),
            'name' => fake()->words(3, true),
            'qty' => fake()->numberBetween(1, 4),
            'price' => fake()->randomFloat(2, 10, 200),
            'image' => null,
        ];
    }
}
