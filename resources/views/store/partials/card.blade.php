@php
    $availability = \App\Support\Catalog::availability($product);
    $save = \App\Support\Catalog::savePercent($product);
    $badgeClass = $product['badge'] === 'Deal' ? 'deal' : ($product['badge'] === 'New' ? 'new' : 'best');
@endphp
<article class="product-card">
    <a class="product-media" href="{{ route('products.show', $product['id']) }}">
        <img src="{{ $product['images'][0] }}" alt="{{ $product['name'] }}">
        @if ($product['stock'] === 0)
            <span class="product-badge sold">Sold out</span>
        @elseif ($product['badge'])
            <span class="product-badge {{ $badgeClass }}">{{ $product['badge'] }}</span>
        @endif
    </a>
    <div class="product-body">
        <p class="product-cat">{{ \App\Support\Catalog::label($product['category']) }}</p>
        <h3><a href="{{ route('products.show', $product['id']) }}">{{ $product['name'] }}</a></h3>
        <div class="stars" aria-label="Rated {{ number_format($product['rating'], 1) }} out of 5">
            {!! \App\Support\Catalog::stars($product['rating']) !!}
            <span>{{ number_format($product['reviews']) }} ratings</span>
        </div>
        <div class="price-row">
            <span class="price">{{ \App\Support\Catalog::money($product['price']) }}</span>
            @if ($product['compare'])
                <s>{{ \App\Support\Catalog::money($product['compare']) }}</s>
                <span class="save">Save {{ $save }}%</span>
            @endif
        </div>
        <p class="product-ship {{ $availability['class'] }}"><i class="bi bi-truck"></i> {{ $availability['text'] }}</p>
        @if ($product['stock'] === 0)
            <button class="btn btn-accent w-100" type="button" disabled>Sold out</button>
        @else
            <form method="POST" action="{{ route('cart.store') }}">
                @csrf
                <input type="hidden" name="product" value="{{ $product['id'] }}">
                <button class="btn btn-accent w-100" type="submit">Add to cart</button>
            </form>
        @endif
    </div>
</article>
