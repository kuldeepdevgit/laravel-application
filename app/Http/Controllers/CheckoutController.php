<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\User;
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

    public function create(): View|RedirectResponse
    {
        if ($this->cart->lines()->isNotEmpty() && ! $this->cart->checkoutBvMet()) {
            return redirect()
                ->route('cart.index')
                ->with('status', $this->cart->checkoutBvMessage());
        }

        $number = session('order');

        return view('store.checkout', [
            'lines' => $this->cart->lines(),
            'totals' => $this->cart->totals(),
            'order' => $number,
            'placedOrder' => is_string($number) && $number !== ''
                ? Order::query()->with('items')->where('number', $number)->first()
                : null,
            'details' => $this->savedDetails(),
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

    /**
     * @return array{name: string, email: string, phone: string, address: string, city: string, region: string, postal: string, country: string}
     */
    private function savedDetails(): array
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return [
                'name' => '',
                'email' => '',
                'phone' => '',
                'address' => '',
                'city' => '',
                'region' => '',
                'postal' => '',
                'country' => '',
            ];
        }

        return [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => (string) $user->phone_number,
            'address' => (string) $user->address,
            'city' => (string) $user->city,
            'region' => (string) $user->state,
            'postal' => (string) $user->zip_code,
            'country' => 'India',
        ];
    }
}
