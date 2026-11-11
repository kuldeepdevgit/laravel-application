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
            ->assertDontSee('Hidden Oil');
    }
}
