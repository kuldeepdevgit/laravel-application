<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function create(): View
    {
        return view('store.checkout', [
            'lines' => $this->cart->lines(),
            'totals' => $this->cart->totals(),
            'order' => session('order'),
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        if ($this->cart->lines()->isEmpty()) {
            return redirect()->route('checkout.create');
        }

        $number = 'NB-'.random_int(100000, 999999);
        $this->cart->clear();

        return redirect()
            ->route('checkout.create')
            ->with('order', $number);
    }
}
