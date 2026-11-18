<?php

namespace Database\Factories;

use App\Models\Income;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Income>
 */
class IncomeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $orderBv = fake()->randomFloat(2, 100, 5000);

        return [
            'user_id' => User::factory(),
            'order_id' => Order::factory(),
            'from_user_id' => null,
            'type' => Income::TYPE_FRANCHISE,
            'order_bv' => $orderBv,
            'percent' => Income::FRANCHISE_PERCENT,
            'amount' => round($orderBv * Income::FRANCHISE_PERCENT / 100, 2),
        ];
    }
}
