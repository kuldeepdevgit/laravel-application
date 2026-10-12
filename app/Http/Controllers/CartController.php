<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Support\Catalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(): View
    {
        return view('store.cart', [
            'lines' => $this->cart->lines(),
            'totals' => $this->cart->totals(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product' => ['required', 'string'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $error = $this->cart->add($data['product'], (int) ($data['qty'] ?? 1));

        if ($error) {
            return back()->with('status', $error);
        }

        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout.create');
        }

        $product = Catalog::find($data['product']);

        return back()->with('added', $product['name'] ?? 'Item');
    }

    public function update(Request $request, string $product): RedirectResponse
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:0', 'max:12'],
        ]);

        $this->cart->set($product, $data['qty']);

        return back();
    }

    public function destroy(string $product): RedirectResponse
    {
        $this->cart->remove($product);

        return back();
    }

    public function promo(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:40'],
        ]);

        $this->cart->applyPromo($data['code'] ?? '');

        return back();
    }

    public function shipping(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'shipping' => ['required', 'in:standard,express'],
        ]);

        $this->cart->setShipping($data['shipping']);

        return back();
    }
}
