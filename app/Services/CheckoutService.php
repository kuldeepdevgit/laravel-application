<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(private CartService $cart) {}

    /**
     * @param  array<string, mixed>  $customer
     */
    public function place(array $customer): Order
    {
        return DB::transaction(function () use ($customer) {
            $lines = $this->cart->lines();

            if ($lines->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Your cart is empty.',
                ]);
            }

            if (! $this->cart->checkoutBvMet()) {
                throw ValidationException::withMessages([
                    'cart' => $this->cart->checkoutBvMessage(),
                ]);
            }

            $totals = $this->cart->totals();

            $order = Order::query()->create([
                'number' => $this->nextNumber(),
                'user_id' => auth()->id(),
                'name' => $customer['name'],
                'email' => $customer['email'],
                'phone' => $customer['phone'] ?? null,
                'address' => $customer['address'],
                'city' => $customer['city'],
                'region' => $customer['region'],
                'postal' => $customer['postal'],
                'country' => $customer['country'],
                'pay_method' => $customer['pay'],
                'promo' => $this->cart->promo() ?: null,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['off'],
                'shipping' => $totals['ship'],
                'total' => $totals['total'],
            ]);

            foreach ($lines as $line) {
                $this->consumeStock($line);

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']['product_id'] ?? null,
                    'sku' => $line['product']['id'],
                    'name' => $line['product']['name'],
                    'qty' => $line['qty'],
                    'price' => $line['product']['price'],
                    'bv' => $line['product']['bv'] ?? 0,
                    'image' => $line['product']['images'][0] ?? null,
                ]);
            }

            $this->cart->clear();

            return $order->load('items');
        });
    }

    private function nextNumber(): string
    {
        do {
            $number = 'NB-'.random_int(100000, 999999);
        } while (Order::query()->where('number', $number)->exists());

        return $number;
    }

    /**
     * @param  array{product: array<string, mixed>, qty: int}  $line
     */
    private function consumeStock(array $line): void
    {
        if (($line['product']['source'] ?? '') !== 'store') {
            return;
        }

        $updated = Product::query()
            ->whereKey($line['product']['product_id'])
            ->where('is_active', true)
            ->where('stock', '>=', $line['qty'])
            ->decrement('stock', $line['qty']);

        if ($updated === 0) {
            throw ValidationException::withMessages([
                'cart' => $line['product']['name'].' no longer has enough stock.',
            ]);
        }
    }
}
