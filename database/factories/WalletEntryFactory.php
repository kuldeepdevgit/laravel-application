<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WalletEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WalletEntry>
 */
class WalletEntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 1, 500);

        return [
            'user_id' => User::factory(),
            'income_id' => null,
            'order_id' => null,
            'direction' => WalletEntry::CREDIT,
            'source' => WalletEntry::SOURCE_FRANCHISE,
            'description' => 'Franchise income',
            'amount' => $amount,
            'balance' => $amount,
        ];
    }

    public function debit(): static
    {
        return $this->state(fn (array $attributes): array => [
            'direction' => WalletEntry::DEBIT,
            'source' => 'adjustment',
            'description' => 'Wallet debit',
        ]);
    }
}
