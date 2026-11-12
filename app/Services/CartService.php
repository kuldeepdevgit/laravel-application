<?php

namespace App\Services;

use App\Models\Product;
use App\Support\Catalog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CartService
{
    public const float MINIMUM_CHECKOUT_BV = 1500.0;

    private const CART = 'store.cart';

    /**
     * @return array<string, int>
     */
    public function items(): array
    {
        $items = session(self::CART, []);

        if (! is_array($items)) {
            return [];
        }

        $clean = [];

        foreach ($items as $id => $qty) {
            $quantity = (int) $qty;

            if (! is_string($id) || $id === '' || $quantity < 1) {
                continue;
            }

            $clean[$id] = $quantity;
        }

        return $clean;
    }

    /**
     * @return Collection<int, array{product: array<string, mixed>, qty: int}>
     */
    public function lines(): Collection
    {
        return collect($this->items())
            ->map(function (int $qty, string $id) {
                $product = $this->find($id);

                if (! $product) {
                    return null;
                }

                $cap = Catalog::maxQty($product);
                $quantity = min($qty, max(1, $cap));

                if ($cap < 1) {
                    return null;
                }

                return ['product' => $product, 'qty' => $quantity];
            })
            ->filter()
            ->values();
    }

    public function count(): int
    {
        return (int) $this->lines()->sum('qty');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array
    {
        $catalog = Catalog::find($id);

        if ($catalog) {
            $catalog['source'] = 'catalog';
            $catalog['product_id'] = null;
            $catalog['currency'] = 'USD';
            $catalog['bv'] = 0.0;

            return $catalog;
        }

        if (! Schema::hasTable('products')) {
            return null;
        }

        $product = Product::query()
            ->with('category')
            ->where('slug', $id)
            ->where('is_active', true)
            ->first();

        return $product?->toCartItem();
    }

    public function currency(): string
    {
        return $this->lines()->contains(
            fn (array $line) => ($line['product']['currency'] ?? 'USD') === 'INR'
        ) ? 'INR' : 'USD';
    }

    public function money(float $amount): string
    {
        $symbol = $this->currency() === 'INR' ? '₹' : '$';

        return $symbol.number_format($amount, 2);
    }

    public function formattedBv(float $amount): string
    {
        return number_format($amount, 2);
    }

    public function checkoutBvMet(): bool
    {
        return $this->totals()['bv'] >= self::MINIMUM_CHECKOUT_BV;
    }

    public function checkoutBvMessage(): string
    {
        return 'Checkout needs a total BV of '.$this->formattedBv(self::MINIMUM_CHECKOUT_BV).' or more. Your total BV is '.$this->formattedBv($this->totals()['bv']).'.';
    }

    public function add(string $id, int $qty): ?string
    {
        $product = $this->find($id);

        if (! $product) {
            return 'That product is not available.';
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
        $product = $this->find($id);

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
     * @return array{subtotal: float, off: float, ship: float, total: float, discounted: float, bv: float}
     */
    public function totals(): array
    {
        $subtotal = round($this->lines()->sum(
            fn (array $line) => $line['product']['price'] * $line['qty']
        ), 2);
        $bv = round($this->lines()->sum(
            fn (array $line): float => (float) ($line['product']['bv'] ?? 0) * $line['qty']
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
            'bv' => $bv,
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
