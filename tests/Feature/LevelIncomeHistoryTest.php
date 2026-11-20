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
