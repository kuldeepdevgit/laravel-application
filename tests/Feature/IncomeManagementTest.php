<?php

namespace Tests\Feature;

use App\Models\Income;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_members_cannot_open_franchise_income_history(): void
    {
        $this->get('/admin/incomes/franchise')->assertRedirect('/login');

        $this->actingAs(User::factory()->create())
            ->get('/admin/incomes/franchise')
            ->assertForbidden();
    }

    public function test_the_super_admin_sees_an_empty_franchise_income_history(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Income Management')
            ->assertSee('Franchise Income History')
            ->assertSee('Open franchise income');

        $this->actingAs($admin)
            ->get('/admin/incomes/franchise')
            ->assertOk()
            ->assertSee('Income Management')
            ->assertSee('Franchise Income History')
            ->assertSee('No franchise income yet')
            ->assertDontSee('Manage Profile');
    }

    public function test_the_super_admin_sees_every_franchise_income_and_can_search_it(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $sponsor = User::factory()->create([
            'name' => 'Franchise Sponsor',
            'user_name' => 'GSI100001',
        ]);
        $buyer = User::factory()->create([
            'name' => 'Asha Rao',
            'user_name' => 'GSI200001',
        ]);
        $order = Order::factory()->for($buyer)->create([
            'number' => 'NB-500005',
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

        $otherSponsor = User::factory()->create([
            'name' => 'Other Sponsor',
            'user_name' => 'GSI300003',
        ]);
        $otherOrder = Order::factory()->create([
            'number' => 'NB-700007',
            'name' => 'Hidden Buyer',
            'status' => 'confirmed',
            'total_bv' => 500,
        ]);
        Income::factory()->for($otherSponsor)->for($otherOrder)->create([
            'type' => Income::TYPE_FRANCHISE,
            'order_bv' => 500,
            'percent' => 10,
            'amount' => 50,
        ]);

        $this->actingAs($admin)
            ->get('/admin/incomes/franchise')
            ->assertOk()
            ->assertSee('NB-500005')
            ->assertSee('Asha Rao')
            ->assertSee('Franchise Sponsor')
            ->assertSee('GSI100001')
            ->assertSee('1,600.00')
            ->assertSee('₹160.00')
            ->assertSee('10%')
            ->assertSee('NB-700007')
            ->assertSee('Hidden Buyer')
            ->assertSee('₹50.00')
            ->assertSee('₹210.00')
            ->assertSee('Credit')
            ->assertSee('Debit');

        $this->actingAs($admin)
            ->get('/admin/incomes/franchise?q=GSI100001')
            ->assertOk()
            ->assertSee('NB-500005')
            ->assertSee('Franchise Sponsor')
            ->assertDontSee('NB-700007')
            ->assertDontSee('Hidden Buyer');

        $this->actingAs($admin)
            ->get('/admin/incomes/franchise?q=Missing')
            ->assertOk()
            ->assertSee('No matches')
            ->assertSee('Nothing matches “Missing”')
            ->assertDontSee('NB-500005');
    }
}
