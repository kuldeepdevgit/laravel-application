<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderPayment>
 */
class OrderPaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'franchise_sponsor_id' => 'GSI100001',
            'amount' => fake()->randomFloat(2, 10, 5000),
            'screenshot' => 'order-payments/proof.jpg',
            'pay_mode' => 'cash',
            'remark' => null,
        ];
    }
}
