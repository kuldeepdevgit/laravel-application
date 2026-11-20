<?php

namespace Tests\Feature;

use App\Models\LevelIncomeHistory;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LevelIncomeHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_level_income_row_stores_the_receiver_payer_and_two_level_amount(): void
    {
        $receiver = User::factory()->create();
        $payer = User::factory()->create();
        $order = Order::factory()->for($payer)->create([
            'status' => 'confirmed',
            'total_bv' => 1600,
        ]);

        $row = LevelIncomeHistory::factory()->credited()->create([
            'user_id' => $receiver->id,
            'pay_user_id' => $payer->id,
            'level' => LevelIncomeHistory::LEVEL_ONE,
            'order_id' => $order->id,
            'order_bv' => 1600,
            'percentage' => 10,
            'income' => 160,
        ]);

        $this->assertDatabaseHas('level_income_history', [
            'id' => $row->id,
            'user_id' => $receiver->id,
            'pay_user_id' => $payer->id,
            'level' => 1,
            'order_id' => $order->id,
            'order_bv' => '1600.00',
            'income' => '160.00',
            'percentage' => '10.00',
            'income_status' => LevelIncomeHistory::STATUS_CREDITED,
        ]);
        $this->assertTrue($row->user->is($receiver));
        $this->assertTrue($row->payUser->is($payer));
        $this->assertTrue($row->order->is($order));
        $this->assertTrue($receiver->levelIncomeHistories->contains($row));
    }

    public function test_income_status_defaults_to_pending_and_level_two_can_be_stored(): void
    {
        $receiver = User::factory()->create();
        $payer = User::factory()->create();

        $row = LevelIncomeHistory::query()->create([
            'user_id' => $receiver->id,
            'pay_user_id' => $payer->id,
            'level' => LevelIncomeHistory::LEVEL_TWO,
            'order_bv' => 900,
            'percentage' => 5,
            'income' => 45,
        ]);

        $row->refresh();

        $this->assertSame(LevelIncomeHistory::LEVEL_TWO, $row->level);
        $this->assertSame(LevelIncomeHistory::STATUS_PENDING, $row->income_status);
        $this->assertSame('45.00', $row->income);
        $this->assertSame('5.00', $row->percentage);
    }

    public function test_confirming_an_order_pays_fifty_percent_of_its_bv_to_each_of_two_uplines(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $firstUpline = User::factory()->create([
            'name' => 'Member A',
            'user_name' => 'GSI5000001',
        ]);
        $directUpline = User::factory()->create([
            'name' => 'Member B',
            'user_name' => 'GSI5000002',
            'sponsor_name' => 'GSI5000001',
        ]);
        $buyer = User::factory()->create([
            'name' => 'Member C',
            'user_name' => 'GSI5000003',
            'sponsor_name' => 'GSI5000002',
        ]);
        $order = Order::factory()->for($buyer)->create([
            'number' => 'NB-800008',
            'status' => 'pending',
            'total_bv' => 1500,
        ]);

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'confirmed'])
            ->assertRedirect();

        $this->assertDatabaseHas('level_income_history', [
            'user_id' => $directUpline->id,
            'pay_user_id' => $buyer->id,
            'level' => LevelIncomeHistory::LEVEL_ONE,
            'order_id' => $order->id,
            'order_bv' => '1500.00',
            'income' => '300.00',
            'percentage' => '20.00',
            'income_status' => LevelIncomeHistory::STATUS_CREDITED,
        ]);
        $this->assertDatabaseHas('level_income_history', [
            'user_id' => $firstUpline->id,
            'pay_user_id' => $buyer->id,
            'level' => LevelIncomeHistory::LEVEL_TWO,
            'order_id' => $order->id,
            'order_bv' => '1500.00',
            'income' => '300.00',
            'percentage' => '20.00',
            'income_status' => LevelIncomeHistory::STATUS_CREDITED,
        ]);
        $this->assertSame(2, LevelIncomeHistory::query()->count());

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'confirmed'])
            ->assertRedirect();

        $this->assertSame(2, LevelIncomeHistory::query()->count());

        $this->actingAs($directUpline)
            ->get('/dashboard/level-income-history')
            ->assertOk()
            ->assertSee('Member C')
            ->assertSee('GSI5000003')
            ->assertSee('NB-800008')
            ->assertSee('1,500.00')
            ->assertSee('₹300.00')
            ->assertSee('20%')
            ->assertSee('40% of its total BV')
            ->assertSee('Credited')
            ->assertDontSee('Member A');

        $this->actingAs($directUpline)
            ->get('/dashboard/matrix-level-income')
            ->assertOk()
            ->assertSee('Level 1')
            ->assertSee('Level 2')
            ->assertSee('₹300.00')
            ->assertSee('₹0.00')
            ->assertSee('Member C')
            ->assertSee('1,500.00')
            ->assertDontSee('Seat 1')
            ->assertDontSee('9 seats');

        $this->actingAs($firstUpline)
            ->get('/dashboard/level-income-history')
            ->assertOk()
            ->assertSee('₹300.00')
            ->assertSee('Member C');

        $this->actingAs($buyer)
            ->get('/dashboard/level-income-history')
            ->assertOk()
            ->assertSee('No level income history yet')
            ->assertSee('₹0.00')
            ->assertDontSee('NB-800008');
    }

    public function test_confirming_an_order_pays_only_the_uplines_that_exist(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $directUpline = User::factory()->create([
            'user_name' => 'GSI5000011',
            'sponsor_name' => null,
        ]);
        $buyer = User::factory()->create([
            'user_name' => 'GSI5000012',
            'sponsor_name' => 'GSI5000011',
        ]);
        $order = Order::factory()->for($buyer)->create([
            'status' => 'pending',
            'total_bv' => 1500,
        ]);

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'confirmed'])
            ->assertRedirect();

        $this->assertSame(1, LevelIncomeHistory::query()->count());
        $this->assertDatabaseHas('level_income_history', [
            'user_id' => $directUpline->id,
            'level' => LevelIncomeHistory::LEVEL_ONE,
            'income' => '300.00',
        ]);
        $this->assertDatabaseMissing('level_income_history', [
            'order_id' => $order->id,
            'income' => '600.00',
        ]);
    }

    public function test_rejecting_an_order_or_a_zero_bv_order_does_not_pay_uplines(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $upline = User::factory()->create([
            'user_name' => 'GSI5000021',
        ]);
        $buyer = User::factory()->create([
            'user_name' => 'GSI5000022',
            'sponsor_name' => 'GSI5000021',
        ]);
        $rejected = Order::factory()->for($buyer)->create([
            'status' => 'pending',
            'total_bv' => 100,
        ]);
        $empty = Order::factory()->for($buyer)->create([
            'status' => 'pending',
            'total_bv' => 0,
        ]);

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$rejected->id.'/status', ['status' => 'rejected'])
            ->assertRedirect();

        $this->actingAs($admin)
            ->patch('/admin/orders/'.$empty->id.'/status', ['status' => 'confirmed'])
            ->assertRedirect();

        $this->assertSame(0, LevelIncomeHistory::query()->count());
        $this->assertSame($upline->id, $buyer->fresh()->sponsor()?->id);
    }

    public function test_deleting_the_receiver_removes_the_history_and_the_payer_is_kept(): void
    {
        $receiver = User::factory()->create();
        $payer = User::factory()->create();
        $row = LevelIncomeHistory::factory()->create([
            'user_id' => $receiver->id,
            'pay_user_id' => $payer->id,
        ]);

        $receiver->delete();

        $this->assertDatabaseMissing('level_income_history', ['id' => $row->id]);
        $this->assertModelExists($payer);

        LevelIncomeHistory::factory()->create([
            'user_id' => User::factory(),
            'pay_user_id' => $payer->id,
        ]);

        $this->expectException(QueryException::class);

        $payer->delete();
    }
}
