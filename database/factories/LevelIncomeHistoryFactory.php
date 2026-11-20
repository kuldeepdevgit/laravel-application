<?php

namespace Database\Factories;

use App\Models\LevelIncomeHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LevelIncomeHistory>
 */
class LevelIncomeHistoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $orderBv = fake()->randomFloat(2, 100, 5000);
        $percentage = 10.0;

        return [
            'user_id' => User::factory(),
            'pay_user_id' => User::factory(),
            'level' => LevelIncomeHistory::LEVEL_ONE,
            'order_id' => null,
            'order_bv' => $orderBv,
            'percentage' => $percentage,
            'income' => round($orderBv * $percentage / 100, 2),
            'income_status' => LevelIncomeHistory::STATUS_PENDING,
        ];
    }

    public function levelTwo(): static
    {
        return $this->state(fn (array $attributes): array => [
            'level' => LevelIncomeHistory::LEVEL_TWO,
        ]);
    }

    public function credited(): static
    {
        return $this->state(fn (array $attributes): array => [
            'income_status' => LevelIncomeHistory::STATUS_CREDITED,
        ]);
    }
}
