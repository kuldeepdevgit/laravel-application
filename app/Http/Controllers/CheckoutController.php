<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CheckoutService $checkout,
    ) {}

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

        try {
            $order = $this->checkout->place($request->validated());
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first() ?: 'Checkout could not be completed.';

            return redirect()
                ->route('cart.index')
                ->with('status', $message)
                ->withErrors($exception->errors());
        }

        return redirect()
            ->route('checkout.create')
            ->with('order', $order->number);
    }
}
