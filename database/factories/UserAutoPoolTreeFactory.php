<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserAutoPoolTree;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserAutoPoolTree>
 */
class UserAutoPoolTreeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tree_id' => null,
            'position' => UserAutoPoolTree::POSITION_ONE,
            'pool_type' => UserAutoPoolTree::POOL_SILVER,
        ];
    }

    public function positionTwo(): static
    {
        return $this->state(fn (array $attributes): array => [
            'position' => UserAutoPoolTree::POSITION_TWO,
        ]);
    }

    public function gold(): static
    {
        return $this->state(fn (array $attributes): array => [
            'pool_type' => UserAutoPoolTree::POOL_GOLD,
        ]);
    }

    public function diamond(): static
    {
        return $this->state(fn (array $attributes): array => [
            'pool_type' => UserAutoPoolTree::POOL_DIAMOND,
        ]);
    }
}
