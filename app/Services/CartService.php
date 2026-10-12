<?php

namespace App\Services;

use App\Support\Catalog;
use Illuminate\Support\Collection;

class CartService
{
    private const CART = 'store.cart';

    /**
     * @return array<string, int>
     */
    public function items(): array
    {
        $items = session(self::CART, []);

        return is_array($items) ? $items : [];
    }

    /**
     * @return Collection<int, array{product: array<string, mixed>, qty: int}>
     */
    public function lines(): Collection
    {
        return collect($this->items())
            ->map(function ($qty, $id) {
                $product = Catalog::find((string) $id);

                if (! $product || (int) $qty < 1) {
                    return null;
                }

                return ['product' => $product, 'qty' => (int) $qty];
            })
            ->filter()
            ->values();
    }

    public function count(): int
    {
        return (int) collect($this->items())->sum();
    }

    public function add(string $id, int $qty): ?string
    {
        $product = Catalog::find($id);

        if (! $product) {
            return 'That product is not in the catalog.';
        }

        $cap = Catalog::maxQty($product);

        if ($cap === 0) {
            return $product['name'].' is sold out.';
        }

        $items = $this->items();
        $items[$id] = min($cap, ($items[$id] ?? 0) + max(1, $qty));
        session([self::CART => $items]);

        return null;
    }

    public function set(string $id, int $qty): void
    {
        $items = $this->items();
        $product = Catalog::find($id);

        if (! $product || $qty < 1 || Catalog::maxQty($product) === 0) {
            unset($items[$id]);
            session([self::CART => $items]);

            return;
        }

        $items[$id] = min(Catalog::maxQty($product), $qty);
        session([self::CART => $items]);
    }

    public function remove(string $id): void
    {
        $items = $this->items();
        unset($items[$id]);
        session([self::CART => $items]);
    }

    public function clear(): void
    {
        session()->forget([self::CART, 'store.promo', 'store.promo_message']);
    }

    public function promo(): string
    {
        return session('store.promo') === 'WELCOME10' ? 'WELCOME10' : '';
    }

    public function promoMessage(): string
    {
        return (string) session('store.promo_message', '');
    }

    public function applyPromo(string $code): void
    {
        $code = strtoupper(trim($code));

        if ($code === 'WELCOME10') {
            session([
                'store.promo' => 'WELCOME10',
                'store.promo_message' => 'Code applied: 10% off.',
            ]);

            return;
        }

        if ($code === '') {
            session(['store.promo' => '', 'store.promo_message' => '']);

            return;
        }

        session([
            'store.promo' => '',
            'store.promo_message' => 'That code is not active. Try WELCOME10.',
        ]);
    }

    public function shipping(): string
    {
        return session('store.shipping') === 'express' ? 'express' : 'standard';
    }

    public function setShipping(string $method): void
    {
        session(['store.shipping' => $method === 'express' ? 'express' : 'standard']);
    }

    /**
     * @return array{subtotal: float, off: float, ship: float, total: float, discounted: float}
     */
    public function totals(): array
    {
        $subtotal = round($this->lines()->sum(
            fn (array $line) => $line['product']['price'] * $line['qty']
        ), 2);
        $off = $this->promo() === 'WELCOME10' ? round($subtotal * 0.1, 2) : 0.0;
        $discounted = round($subtotal - $off, 2);
        $ship = $subtotal === 0.0
            ? 0.0
            : ($this->shipping() === 'express' ? 12.99 : ($discounted >= 49 ? 0.0 : 5.99));

        return [
            'subtotal' => $subtotal,
            'off' => $off,
            'ship' => $ship,
            'discounted' => $discounted,
            'total' => round($discounted + $ship, 2),
        ];
    }

    public function dealEndsAt(): int
    {
        $end = (int) session('store.deal_end', 0);

        if ($end < time()) {
            $end = time() + (8 * 60 * 60);
            session(['store.deal_end' => $end]);
        }

        return $end;
    }
}
