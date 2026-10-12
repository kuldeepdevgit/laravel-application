@php
    $summaryCart = app(\App\Services\CartService::class);
    $totals = $summaryCart->totals();
    $lines = $summaryCart->lines();
    $standardLabel = $totals['subtotal'] == 0 || $totals['discounted'] >= 49 ? 'Free' : '$5.99';
    $remaining = round(49 - $totals['discounted'], 2);
@endphp
<aside class="summary-card">
    <h2>Order summary</h2>
    <ul class="summary-lines">
        @foreach ($lines as $line)
            <li>
                <span>{{ $line['product']['name'] }} × {{ $line['qty'] }}</span>
                <span>{{ \App\Support\Catalog::money($line['product']['price'] * $line['qty']) }}</span>
            </li>
        @endforeach
    </ul>
    <div class="sum-row"><span>Subtotal</span><span>{{ \App\Support\Catalog::money($totals['subtotal']) }}</span></div>
    <div class="sum-row"><span>Discount</span><span>−{{ \App\Support\Catalog::money($totals['off']) }}</span></div>
    <div class="sum-row"><span>Shipping</span><span>{{ $totals['ship'] == 0 ? 'Free' : \App\Support\Catalog::money($totals['ship']) }}</span></div>
    @if ($totals['subtotal'] > 0 && $summaryCart->shipping() !== 'express' && $remaining > 0)
        <p class="fine">Add {{ \App\Support\Catalog::money($remaining) }} for free standard shipping.</p>
    @endif
    <div class="sum-row total"><span>Total</span><span>{{ \App\Support\Catalog::money($totals['total']) }}</span></div>

    <form method="POST" action="{{ route('cart.shipping') }}" class="ship-options">
        @csrf
        <label>
            <span><input type="radio" name="shipping" value="standard" @checked($summaryCart->shipping() !== 'express') onchange="this.form.submit()"> Standard</span>
            <small>{{ $standardLabel }}</small>
        </label>
        <label>
            <span><input type="radio" name="shipping" value="express" @checked($summaryCart->shipping() === 'express') onchange="this.form.submit()"> Express</span>
            <small>$12.99</small>
        </label>
    </form>

    <form method="POST" action="{{ route('cart.promo') }}" class="promo">
        @csrf
        <label class="visually-hidden" for="promo-code">Promo code</label>
        <input id="promo-code" class="form-control" name="code" placeholder="Promo code" value="{{ $summaryCart->promo() }}" autocomplete="off">
        <button class="btn btn-ghost" type="submit">Apply</button>
    </form>
    @if ($summaryCart->promoMessage())
        <p class="promo-note">{{ $summaryCart->promoMessage() }}</p>
    @endif
    <p class="fine">
        Try WELCOME10 for 10% off.
        @if ($summaryCart->promo())
            <button class="btn btn-link btn-sm p-0 align-baseline" type="submit" form="remove-promo">Remove code</button>
        @endif
    </p>
    @if ($summaryCart->promo())
        <form id="remove-promo" method="POST" action="{{ route('cart.promo') }}" hidden>
            @csrf
            <input type="hidden" name="code" value="">
        </form>
    @endif

    @if (($mode ?? 'cart') === 'checkout')
        <button class="btn btn-accent w-100 mt-3" type="submit" form="checkout-form">Place order</button>
    @else
        <a class="btn btn-accent w-100 mt-3" href="{{ route('checkout.create') }}" @if ($lines->isEmpty()) aria-disabled="true" tabindex="-1" @endif>Checkout</a>
    @endif
    <p class="fine">Demo checkout. No payment is processed.</p>
</aside>
