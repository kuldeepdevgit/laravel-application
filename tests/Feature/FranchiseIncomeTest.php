<?php

namespace Tests\Feature;

use App\Models\Income;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FranchiseIncomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_approving_an_order_credits_the_franchise_sponsor_ten_percent_of_order_bv(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $buyer = User::factory()->create([
            'name' => 'Asha Rao',
            'user_name' => 'GSI200001',
        ]);
        $sponsor = User::factory()->create([
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
            ->assertRedirect()
            ->assertSessionHas('status', 'NB-500005 is now Confirmed.');

        $income = Income::query()->first();

        $this->assertNotNull($income);
        $this->assertSame($sponsor->id, $income->user_id);
        $this->assertSame($buyer->id, $income->from_user_id);
        $this->assertSame($order->id, $income->order_id);
        $this->assertSame(Income::TYPE_FRANCHISE, $income->type);
        $this->assertSame('1600.00', $income->order_bv);
        $this->assertSame('10.00', $income->percent);
        $this->assertSame('160.00', $income->amount);
        $this->assertSame(1, Income::query()->count());

        $this->actingAs($admin)
            ->get('/admin/orders/'.$order->id)
            ->assertOk()
            ->assertSee('Franchise income')
            ->assertSee('₹160.00');

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'pending'])
            ->assertRedirect();

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'confirmed'])
            ->assertRedirect();

        $this->assertSame(1, Income::query()->count());
        $this->assertSame('160.00', Income::query()->first()->amount);
    }

    public function test_rejecting_an_order_or_a_missing_sponsor_does_not_credit_franchise_income(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $buyer = User::factory()->create();
        $rejected = Order::factory()->for($buyer)->create([
            'status' => 'pending',
            'total_bv' => 2000,
        ]);
        OrderPayment::factory()->for($rejected)->for($buyer)->create([
            'franchise_sponsor_id' => 'GSI100001',
        ]);
        User::factory()->create(['user_name' => 'GSI100001']);

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$rejected->id.'/status', ['status' => 'rejected'])
            ->assertRedirect();

        $unknown = Order::factory()->for($buyer)->create([
            'status' => 'pending',
            'total_bv' => 2000,
        ]);
        OrderPayment::factory()->for($unknown)->for($buyer)->create([
            'franchise_sponsor_id' => 'GSI999999',
        ]);

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$unknown->id.'/status', ['status' => 'confirmed'])
            ->assertRedirect();

        $zeroBv = Order::factory()->for($buyer)->create([
            'status' => 'pending',
            'total_bv' => 0,
        ]);
        OrderPayment::factory()->for($zeroBv)->for($buyer)->create([
            'franchise_sponsor_id' => 'GSI100001',
        ]);

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$zeroBv->id.'/status', ['status' => 'confirmed'])
            ->assertRedirect();

        $unpaid = Order::factory()->for($buyer)->create([
            'status' => 'pending',
            'total_bv' => 1800,
        ]);

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$unpaid->id.'/status', ['status' => 'confirmed'])
            ->assertRedirect();

        $this->assertSame(0, Income::query()->count());
        $this->assertSame('confirmed', $unknown->fresh()->status);
        $this->assertSame('confirmed', $zeroBv->fresh()->status);
        $this->assertSame('confirmed', $unpaid->fresh()->status);
    }

    public function test_the_franchise_sponsor_sees_franchise_income_first_in_the_income_report(): void
    {
        $sponsor = User::factory()->create([
            'name' => 'Franchise Sponsor',
            'user_name' => 'GSI100001',
        ]);
        $other = User::factory()->create([
            'user_name' => 'GSI300003',
        ]);
        $buyer = User::factory()->create();
        $order = Order::factory()->for($buyer)->create([
            'number' => 'NB-600006',
            'name' => 'Asha Rao',
            'status' => 'confirmed',
            'total_bv' => 1600,
        ]);
        Income::factory()->for($sponsor)->for($order)->create([
            'from_user_id' => $buyer->id,
            'type' => Income::TYPE_FRANCHISE,
            'order_bv' => 1600,
            'percent' => 10,
            'amount' => 160,
        ]);

        $this->actingAs($sponsor)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Franchise Income History')
            ->assertSeeInOrder(['Franchise Income History', 'Self ROI Income']);

        $this->actingAs($sponsor)
            ->get('/dashboard/franchise-income-history')
            ->assertOk()
            ->assertSee('Franchise Income History')
            ->assertSee('10% of its total BV')
            ->assertSee('Credit')
            ->assertSee('Debit')
            ->assertSee('₹160.00')
            ->assertSee('NB-600006')
            ->assertSee('Asha Rao')
            ->assertSee('1,600.00')
            ->assertSee('10%');

        $this->actingAs($other)
            ->get('/dashboard/franchise-income-history')
            ->assertOk()
            ->assertSee('No franchise income history yet')
            ->assertSee('₹0.00')
            ->assertDontSee('NB-600006');
    }
}
