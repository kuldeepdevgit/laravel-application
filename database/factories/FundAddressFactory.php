<?php

namespace Database\Factories;

use App\Models\FundAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FundAddress>
 */
class FundAddressFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'address_type' => 'usdt',
            'wallet_address' => '0x'.fake()->regexify('[a-f0-9]{40}'),
            'wallet_screenshot' => null,
            'bank_name' => null,
            'account_name' => null,
            'account_number' => null,
            'ifsc_code' => null,
        ];
    }

    public function inr(): static
    {
        return $this->state(fn (): array => [
            'address_type' => 'inr',
            'wallet_address' => null,
            'wallet_screenshot' => null,
            'bank_name' => 'State Bank',
            'account_name' => 'Green Smart India',
            'account_number' => '123456789012',
            'ifsc_code' => 'SBIN0001234',
        ]);
    }
}
