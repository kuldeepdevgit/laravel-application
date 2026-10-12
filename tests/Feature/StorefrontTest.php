<?php

namespace Tests\Feature;

use Tests\TestCase;

class StorefrontTest extends TestCase
{
    public function test_the_home_page_lists_products(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('northbay')
            ->assertSee('Aero Quiet Headphones');
    }

    public function test_search_filters_the_catalog(): void
    {
        $this->get('/shop?q=candle')
            ->assertOk()
            ->assertSee('Cedar Candle Trio')
            ->assertDontSee('Trailform Runners');
    }

    public function test_a_sold_out_product_stays_out_of_the_cart(): void
    {
        $this->post('/cart', ['product' => 'nb-candle'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->get('/cart')->assertSee('Your cart is empty');
    }

    public function test_a_shopper_can_add_an_item_and_place_a_demo_order(): void
    {
        $this->post('/cart', ['product' => 'nb-headphones', 'qty' => 1])
            ->assertRedirect()
            ->assertSessionHas('added', 'Aero Quiet Headphones');

        $this->get('/cart')
            ->assertOk()
            ->assertSee('Aero Quiet Headphones')
            ->assertSee('$129.00');

        $this->post('/cart/promo', ['code' => 'WELCOME10'])->assertRedirect();

        $this->post('/checkout', [
            'name' => 'Test Shopper',
            'email' => 'shopper@example.com',
            'address' => '18 Harbor Lane',
            'city' => 'Portland',
            'region' => 'OR',
            'postal' => '97201',
            'country' => 'United States',
            'pay' => 'delivery',
        ])->assertRedirect(route('checkout.create'));

        $this->get('/checkout')
            ->assertOk()
            ->assertSee('Order placed')
            ->assertSee('NB-');

        $this->get('/cart')->assertSee('Your cart is empty');
    }

    public function test_checkout_requires_a_shipping_address(): void
    {
        $this->post('/cart', ['product' => 'nb-coffee']);

        $this->post('/checkout', [])
            ->assertSessionHasErrors(['name', 'email', 'address', 'city', 'region', 'postal', 'country']);
    }
}
