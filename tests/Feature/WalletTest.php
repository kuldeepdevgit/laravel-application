<?php

namespace Tests\Feature;

use App\Models\Income;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\User;
use App\Models\WalletEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_approving_an_order_credits_the_franchise_sponsor_wallet_once(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $buyer = User::factory()->create([
            'name' => 'Asha Rao',
            'user_name' => 'GSI200001',
        ]);
        $sponsor = User::factory()->franchise()->create([
            'name' => 'Franchise Sponsor',
            'user_name' => 'GSI100001',
        ]);
        $order = Order::factory()->for($buyer)->create([
            'number' => 'NB-500005',
            'name' => 'Asha Rao',
            'status' => 'pending',
            'total_bv' => 1600,
        ]);
        OrderPayment::factory()->for($order)->for($buyer)->create([
            'franchise_sponsor_id' => 'gsi100001',
        ]);

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'confirmed'])
            ->assertRedirect();

        $entry = WalletEntry::query()->first();

        $this->assertNotNull($entry);
        $this->assertSame($sponsor->id, $entry->user_id);
        $this->assertSame(Income::query()->first()->id, $entry->income_id);
        $this->assertSame($order->id, $entry->order_id);
        $this->assertSame(WalletEntry::CREDIT, $entry->direction);
        $this->assertSame(WalletEntry::SOURCE_FRANCHISE, $entry->source);
        $this->assertSame('Franchise income · NB-500005', $entry->description);
        $this->assertSame('160.00', $entry->amount);
        $this->assertSame('160.00', $entry->balance);

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'pending'])
            ->assertRedirect();

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'confirmed'])
            ->assertRedirect();

        $this->assertSame(1, WalletEntry::query()->count());

        $this->actingAs($sponsor)
            ->get('/dashboard/wallet')
            ->assertOk()
            ->assertSee('Wallet')
            ->assertSee('Balance')
            ->assertSee('Credit')
            ->assertSee('Debit')
            ->assertSee('₹160.00')
            ->assertSee('Franchise income · NB-500005')
            ->assertSeeInOrder(['Franchise Income History', 'Wallet', 'Self ROI Income']);

        $this->actingAs($sponsor)
            ->get('/dashboard/franchise-income-history')
            ->assertOk()
            ->assertSee('Credit')
            ->assertSee('Debit')
            ->assertSee('₹160.00');
    }

    public function test_a_debit_reduces_the_wallet_balance_and_is_listed_separately(): void
    {
        $sponsor = User::factory()->create([
            'user_name' => 'GSI100001',
        ]);

        WalletEntry::post(
            user: $sponsor,
            direction: WalletEntry::CREDIT,
            amount: 160,
            source: WalletEntry::SOURCE_FRANCHISE,
            description: 'Franchise income · NB-700007',
        );

        WalletEntry::post(
            user: $sponsor,
            direction: WalletEntry::DEBIT,
            amount: 40,
            source: 'adjustment',
            description: 'Wallet debit',
        );

        $this->assertSame('120.00', WalletEntry::query()->latest('id')->first()->balance);

        $this->actingAs($sponsor)
            ->get('/dashboard/wallet')
            ->assertOk()
            ->assertSee('₹120.00')
            ->assertSee('₹160.00')
            ->assertSee('₹40.00')
            ->assertSee('Wallet debit')
            ->assertSee('Franchise income · NB-700007');

        try {
            WalletEntry::post(
                user: $sponsor,
                direction: WalletEntry::DEBIT,
                amount: 500,
                source: 'adjustment',
                description: 'Too large',
            );
            $this->fail('A debit larger than the balance was accepted.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('The wallet balance is not enough for this debit.', $exception->getMessage());
        }

        $this->assertSame(2, WalletEntry::query()->count());
    }

    public function test_another_member_sees_an_empty_wallet(): void
    {
        $sponsor = User::factory()->create();
        $other = User::factory()->create();

        WalletEntry::factory()->for($sponsor)->create([
            'amount' => 160,
            'balance' => 160,
            'description' => 'Franchise income · NB-600006',
        ]);

        $this->actingAs($other)
            ->get('/dashboard/wallet')
            ->assertOk()
            ->assertSee('₹0.00')
            ->assertSee('No wallet entries yet')
            ->assertDontSee('NB-600006');
    }
}
