<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_lists_products(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Green Smart India E Services')
            ->assertSee('images/green-smart-india.png', false)
            ->assertSee('All products')
            ->assertSee('No products yet')
            ->assertDontSee('Aero Quiet Headphones')
            ->assertDontSee('Fieldcloth Journal');
    }

    public function test_the_home_page_lists_every_active_saved_product(): void
    {
        $category = Category::factory()->create([
            'name' => 'Pantry',
            'slug' => 'pantry',
            'is_active' => true,
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Assam Honey',
            'slug' => 'assam-honey',
            'price' => 120.50,
            'product_bv' => 12,
            'is_active' => true,
        ]);
        Product::factory()->create([
            'name' => 'Hidden Soap',
            'slug' => 'hidden-soap',
            'is_active' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Assam Honey')
            ->assertSee('Pantry')
            ->assertSee('₹120.50')
            ->assertSee('BV 12.00')
            ->assertSee(route('products.show', 'assam-honey'), false)
            ->assertDontSee(route('shop', ['cat' => 'pantry']), false)
            ->assertDontSee('Hidden Soap')
            ->assertDontSee('Aero Quiet Headphones');
    }

    public function test_an_admin_product_page_opens_from_its_slug(): void
    {
        $category = Category::factory()->create([
            'name' => 'Pantry',
            'slug' => 'pantry',
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Assam Honey',
            'slug' => 'assam-honey',
            'description' => 'A small jar of honey.',
            'price' => 120.50,
            'is_active' => true,
        ]);
        Product::factory()->create([
            'name' => 'Hidden Soap',
            'slug' => 'hidden-soap',
            'is_active' => false,
        ]);

        $this->get(route('products.show', 'assam-honey'))
            ->assertOk()
            ->assertSee('Assam Honey')
            ->assertSee('A small jar of honey.')
            ->assertSee('₹120.50')
            ->assertSee('Pantry');

        $this->get(route('products.show', 'hidden-soap'))->assertNotFound();
        $this->get(route('products.show', 'nb-headphones'))
            ->assertOk()
            ->assertSee('Aero Quiet Headphones');
    }

    public function test_search_filters_the_shop(): void
    {
        Product::factory()->create([
            'name' => 'Cedar Candle Trio',
            'slug' => 'cedar-candle-trio',
            'description' => 'Three soy candles.',
            'is_active' => true,
        ]);
        Product::factory()->create([
            'name' => 'Trailform Runners',
            'slug' => 'trailform-runners',
            'description' => 'Everyday shoes.',
            'is_active' => true,
        ]);

        $this->get('/shop?q=candle')
            ->assertOk()
            ->assertSee('Results for “candle”')
            ->assertSee('Cedar Candle Trio')
            ->assertDontSee('Trailform Runners');
    }

    public function test_the_shop_paginates_active_products(): void
    {
        Product::factory()->create([
            'name' => 'Hidden Soap',
            'slug' => 'hidden-soap',
            'is_active' => false,
        ]);

        foreach (range(1, 10) as $number) {
            Product::factory()->create([
                'name' => sprintf('Shelf Item %02d', $number),
                'slug' => sprintf('shelf-item-%02d', $number),
                'is_active' => true,
            ]);
        }

        $this->get('/shop')
            ->assertOk()
            ->assertSee('All products')
            ->assertSee('Shelf Item 01')
            ->assertDontSee('Shelf Item 10')
            ->assertDontSee('Hidden Soap')
            ->assertSee('aria-label="Pages"', false);

        $this->get('/shop?page=2')
            ->assertOk()
            ->assertSee('Shelf Item 10')
            ->assertDontSee('Shelf Item 01');
    }

    public function test_the_shop_filters_by_department_price_and_stock(): void
    {
        $tea = Category::factory()->create([
            'name' => 'Organic Tea',
            'slug' => 'organic-tea',
            'is_active' => true,
        ]);
        $spice = Category::factory()->create([
            'name' => 'Spices',
            'slug' => 'spices',
            'is_active' => true,
        ]);
        Category::factory()->create([
            'name' => 'Hidden Honey',
            'slug' => 'hidden-honey',
            'is_active' => false,
        ]);

        Product::factory()->create([
            'category_id' => $tea->id,
            'name' => 'Assam Honey',
            'slug' => 'assam-honey',
            'price' => 80,
            'stock' => 4,
        ]);
        Product::factory()->create([
            'category_id' => $spice->id,
            'name' => 'Red Chilli',
            'slug' => 'red-chilli',
            'price' => 40,
            'stock' => 3,
        ]);
        Product::factory()->create([
            'category_id' => $tea->id,
            'name' => 'Costly Tea',
            'slug' => 'costly-tea',
            'price' => 500,
            'stock' => 2,
        ]);
        Product::factory()->create([
            'category_id' => $tea->id,
            'name' => 'Empty Tin',
            'slug' => 'empty-tin',
            'price' => 30,
            'stock' => 0,
        ]);

        $this->get('/shop?cat=organic-tea')
            ->assertOk()
            ->assertSee('Organic Tea')
            ->assertSee('Assam Honey')
            ->assertSee('Costly Tea')
            ->assertSee('Empty Tin')
            ->assertDontSee('Red Chilli')
            ->assertDontSee('Hidden Honey');

        $this->get('/shop?max=100')
            ->assertOk()
            ->assertSee('Assam Honey')
            ->assertSee('Red Chilli')
            ->assertSee('Empty Tin')
            ->assertDontSee('Costly Tea');

        $this->get('/shop?stock=1')
            ->assertOk()
            ->assertSee('Assam Honey')
            ->assertDontSee('Empty Tin');

        $this->get('/shop?sort=price-desc')
            ->assertOk()
            ->assertSeeInOrder(['Costly Tea', 'Assam Honey', 'Red Chilli', 'Empty Tin']);
    }

    public function test_a_sold_out_product_stays_out_of_the_cart(): void
    {
        $this->post('/cart', ['product' => 'nb-candle'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->get('/cart')->assertSee('Your cart is empty');
    }

    public function test_a_catalog_item_cannot_be_checked_out_below_the_bv_minimum(): void
    {
        $this->post('/cart', ['product' => 'nb-headphones', 'qty' => 1])
            ->assertRedirect()
            ->assertSessionHas('added', 'Aero Quiet Headphones');

        $this->get('/cart')
            ->assertOk()
            ->assertSee('Aero Quiet Headphones')
            ->assertSee('$129.00');

        $this->post('/cart/promo', ['code' => 'WELCOME10'])->assertRedirect();

        $this->get('/checkout')->assertRedirect(route('cart.index'));

        $this->post('/checkout', [
            'name' => 'Test Shopper',
            'email' => 'shopper@example.com',
            'address' => '18 Harbor Lane',
            'city' => 'Portland',
            'region' => 'OR',
            'postal' => '97201',
            'country' => 'United States',
            'pay' => 'cash',
        ])->assertRedirect(route('cart.index'));

        $this->get('/cart')
            ->assertOk()
            ->assertSee('Checkout needs a total BV of 1,500.00 or more. Your total BV is 0.00.')
            ->assertSee('Aero Quiet Headphones');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_requires_a_shipping_address(): void
    {
        $this->post('/cart', ['product' => 'nb-coffee']);

        $this->post('/checkout', [])
            ->assertSessionHasErrors(['name', 'email', 'address', 'city', 'region', 'postal', 'country']);
    }

    public function test_a_saved_product_can_be_added_to_the_cart_and_checked_out(): void
    {
        $product = Product::factory()->create([
            'name' => 'Assam Honey',
            'slug' => 'assam-honey',
            'price' => 120.50,
            'product_bv' => 750,
            'stock' => 5,
            'is_active' => true,
        ]);

        $this->get(route('products.show', 'assam-honey'))
            ->assertOk()
            ->assertSee('Add to cart')
            ->assertSee('Buy now');

        $this->from(route('products.show', 'assam-honey'))
            ->post('/cart', ['product' => 'assam-honey', 'qty' => 2])
            ->assertRedirect()
            ->assertSessionHas('added', 'Assam Honey');

        $this->get('/cart')
            ->assertOk()
            ->assertSee('Assam Honey')
            ->assertSee('₹120.50')
            ->assertSee('₹241.00')
            ->assertSee('Total BV')
            ->assertSee('1,500.00')
            ->assertDontSee('Checkout needs a total BV');

        $this->get('/checkout')
            ->assertOk()
            ->assertSee('Cash')
            ->assertDontSee('Card number')
            ->assertDontSee('Pay on delivery');

        $this->post('/checkout', [
            'name' => 'Test Shopper',
            'email' => 'shopper@example.com',
            'address' => '18 Harbor Lane',
            'city' => 'Guwahati',
            'region' => 'Assam',
            'postal' => '781001',
            'country' => 'India',
            'pay' => 'cash',
        ])->assertRedirect(route('checkout.create'));

        $this->get('/checkout')
            ->assertOk()
            ->assertSee('Order placed')
            ->assertSee('NB-');

        $this->assertDatabaseHas('orders', [
            'email' => 'shopper@example.com',
            'country' => 'India',
            'pay_method' => 'cash',
        ]);
        $this->assertDatabaseHas('order_items', [
            'sku' => 'assam-honey',
            'name' => 'Assam Honey',
            'qty' => 2,
        ]);
        $this->assertSame(3, $product->fresh()->stock);
        $this->get('/cart')->assertSee('Your cart is empty');
    }

    public function test_checkout_requires_a_total_bv_of_at_least_1500(): void
    {
        $product = Product::factory()->create([
            'name' => 'Neem Soap',
            'slug' => 'neem-soap',
            'price' => 40,
            'product_bv' => 1499,
            'stock' => 4,
            'is_active' => true,
        ]);

        $this->post('/cart', ['product' => 'neem-soap', 'qty' => 1])->assertRedirect();

        $this->get('/cart')
            ->assertOk()
            ->assertSee('Total BV')
            ->assertSee('1,499.00')
            ->assertSee('Checkout needs a total BV of 1,500.00 or more. Your total BV is 1,499.00.');

        $this->get('/checkout')->assertRedirect(route('cart.index'));

        $this->post('/checkout', [
            'name' => 'Test Shopper',
            'email' => 'shopper@example.com',
            'address' => '18 Harbor Lane',
            'city' => 'Guwahati',
            'region' => 'Assam',
            'postal' => '781001',
            'country' => 'India',
            'pay' => 'cash',
        ])->assertRedirect(route('cart.index'))
            ->assertSessionHas('status', 'Checkout needs a total BV of 1,500.00 or more. Your total BV is 1,499.00.');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_a_saved_sold_out_product_cannot_be_added_to_the_cart(): void
    {
        Product::factory()->create([
            'name' => 'Empty Tin',
            'slug' => 'empty-tin',
            'stock' => 0,
            'is_active' => true,
        ]);

        $this->from(route('shop'))
            ->post('/cart', ['product' => 'empty-tin'])
            ->assertRedirect()
            ->assertSessionHas('status', 'Empty Tin is sold out.');

        $this->get('/cart')->assertSee('Your cart is empty');
    }
}
