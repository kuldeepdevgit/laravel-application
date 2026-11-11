@php
    $soldOut = (int) $stock === 0;
    $cap = max(1, (int) ($cap ?? 12));
    $showQty = (bool) ($showQty ?? false);
    $buyNow = (bool) ($buyNow ?? false);
@endphp
@if ($soldOut)
    <button class="btn btn-accent w-100" type="button" disabled>Sold out</button>
@else
    <form method="POST" action="{{ route('cart.store') }}" class="add-to-cart">
        @csrf
        <input type="hidden" name="product" value="{{ $sku }}">
        @if ($showQty)
            <label class="fw-semibold mb-2" for="qty-{{ $sku }}">Quantity</label>
            <input id="qty-{{ $sku }}" class="form-control qty-input mb-3" type="number" name="qty" min="1" max="{{ $cap }}" value="1">
        @endif
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-accent {{ $buyNow ? 'flex-grow-1' : 'w-100' }}" type="submit">Add to cart</button>
            @if ($buyNow)
                <button class="btn btn-ghost" type="submit" name="buy_now" value="1">Buy now</button>
            @endif
        </div>
    </form>
@endif
