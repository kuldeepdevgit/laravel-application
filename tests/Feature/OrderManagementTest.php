<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin/orders')->assertRedirect('/login');
        $this->patch('/admin/orders/1/status')->assertRedirect('/login');
    }

    public function test_a_member_cannot_open_order_management(): void
    {
        $member = User::factory()->create();
        $order = Order::factory()->create();

        $this->actingAs($member)
            ->get('/admin/orders')
            ->assertForbidden();

        $this->actingAs($member)
            ->get('/admin/orders/'.$order->id)
            ->assertForbidden();

        $this->actingAs($member)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'confirmed'])
            ->assertForbidden();
    }

    public function test_the_super_admin_sees_an_empty_order_list(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get('/admin/orders')
            ->assertOk()
            ->assertSee('Order Management')
            ->assertSee('Show all orders')
            ->assertSee('No orders yet')
            ->assertDontSee('Manage Profile');
    }

    public function test_the_super_admin_sees_every_order_with_its_products(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $buyer = User::factory()->create([
            'name' => 'Asha Rao',
            'user_name' => 'asha',
        ]);
        $category = Category::factory()->create(['name' => 'Personal care']);
        $product = Product::factory()->for($category)->create([
            'name' => 'Neem Soap',
            'description' => 'A gentle neem bar.',
        ]);

        $order = Order::factory()->for($buyer)->create([
            'number' => 'NB-100001',
            'name' => 'Asha Rao',
            'email' => 'asha@example.com',
            'phone' => '9876543210',
            'address' => '12 Market Road',
            'city' => 'Pune',
            'region' => 'Maharashtra',
            'postal' => '411001',
            'country' => 'India',
            'pay_method' => 'cash',
            'promo' => 'SAVE10',
            'subtotal' => 200,
            'discount' => 20,
            'shipping' => 40,
            'total' => 220,
        ]);

        OrderItem::factory()->for($order)->for($product)->create([
            'sku' => 'neem-soap',
            'name' => 'Neem Soap',
            'qty' => 2,
            'price' => 100,
            'bv' => 80,
        ]);

        $other = Order::factory()->create([
            'number' => 'NB-200002',
            'name' => 'Other Buyer',
            'email' => 'other@example.com',
        ]);

        OrderItem::factory()->for($other)->create([
            'sku' => 'hidden-oil',
            'name' => 'Hidden Oil',
            'qty' => 1,
            'price' => 50,
        ]);

        $this->actingAs($admin)
            ->get('/admin/orders')
            ->assertOk()
            ->assertSee('NB-100001')
            ->assertSee('Asha Rao')
            ->assertSee('asha@example.com')
            ->assertSee('9876543210')
            ->assertSee('Neem Soap')
            ->assertSee('neem-soap')
            ->assertSee('Qty 2')
            ->assertSee('₹100.00')
            ->assertSee('₹200.00')
            ->assertSee('₹220.00')
            ->assertSee('BV 80.00')
            ->assertSee('Line BV 160.00')
            ->assertSee('Total BV')
            ->assertSee('Pending')
            ->assertSee('Confirm')
            ->assertSee('Rejected')
            ->assertSee('NB-200002')
            ->assertSee('Hidden Oil')
            ->assertSee('Details');

        $this->actingAs($admin)
            ->get('/admin/orders?q=Neem+Soap')
            ->assertOk()
            ->assertSee('NB-100001')
            ->assertSee('Neem Soap')
            ->assertDontSee('Hidden Oil')
            ->assertDontSee('NB-200002');

        $this->actingAs($admin)
            ->get('/admin/orders/'.$order->id)
            ->assertOk()
            ->assertSee('NB-100001')
            ->assertSee('Asha Rao')
            ->assertSee('asha@example.com')
            ->assertSee('9876543210')
            ->assertSee('asha')
            ->assertSee('12 Market Road')
            ->assertSee('Pune')
            ->assertSee('Maharashtra')
            ->assertSee('411001')
            ->assertSee('India')
            ->assertSee('Cash')
            ->assertSee('SAVE10')
            ->assertSee('₹200.00')
            ->assertSee('₹20.00')
            ->assertSee('₹40.00')
            ->assertSee('₹220.00')
            ->assertSee('Neem Soap')
            ->assertSee('A gentle neem bar.')
            ->assertSee('neem-soap')
            ->assertSee('Personal care')
            ->assertSee('₹100.00')
            ->assertSee('Unit BV')
            ->assertSee('80.00')
            ->assertSee('Line BV')
            ->assertSee('160.00')
            ->assertSee('Total BV')
            ->assertDontSee('Hidden Oil');
    }

    public function test_the_super_admin_can_confirm_pend_or_reject_an_order(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $order = Order::factory()->create([
            'number' => 'NB-300003',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->from('/admin/orders')
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'confirmed'])
            ->assertRedirect('/admin/orders')
            ->assertSessionHas('status', 'NB-300003 is now Confirmed.');

        $this->assertSame('confirmed', $order->fresh()->status);

        $this->actingAs($admin)
            ->get('/admin/orders/'.$order->id)
            ->assertOk()
            ->assertSee('Confirmed');

        $this->actingAs($admin)
            ->from('/admin/orders/'.$order->id)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'rejected'])
            ->assertRedirect('/admin/orders/'.$order->id)
            ->assertSessionHas('status', 'NB-300003 is now Rejected.');

        $this->assertSame('rejected', $order->fresh()->status);

        $this->actingAs($admin)
            ->from('/admin/orders/'.$order->id)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'pending'])
            ->assertRedirect('/admin/orders/'.$order->id)
            ->assertSessionHas('status', 'NB-300003 is now Pending.');

        $this->assertSame('pending', $order->fresh()->status);

        $this->actingAs($admin)
            ->from('/admin/orders/'.$order->id)
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'shipped'])
            ->assertRedirect('/admin/orders/'.$order->id)
            ->assertSessionHasErrors('status');

        $this->assertSame('pending', $order->fresh()->status);
    }
}
