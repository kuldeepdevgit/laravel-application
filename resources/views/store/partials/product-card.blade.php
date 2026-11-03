@php
    $productUrl = route('products.show', $product->slug);
@endphp
<article class="product-card">
    <a class="product-media" href="{{ $productUrl }}">
        @if ($product->imageUrl())
            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}">
        @else
            <span class="product-fallback" aria-hidden="true">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>
        @endif
        @if ($product->stock === 0)
            <span class="product-badge sold">Sold out</span>
        @endif
    </a>
    <div class="product-body">
        @if ($product->category)
            <p class="product-cat">{{ $product->category->name }}</p>
        @endif
        <h3><a href="{{ $productUrl }}">{{ $product->name }}</a></h3>
        @if ($product->description)
            <p class="product-blurb">{{ $product->description }}</p>
        @endif
        <div class="price-row">
            <span class="price">{{ $product->formattedPrice() }}</span>
            @if ($product->formattedComparePrice())
                <s>{{ $product->formattedComparePrice() }}</s>
            @endif
            <span class="product-bv">BV {{ $product->formattedBv() }}</span>
        </div>
        <p class="product-ship {{ $product->stock === 0 ? 'out' : '' }}">
            <i class="bi bi-box-seam"></i>
            {{ $product->stock === 0 ? 'Sold out' : $product->stock.' in stock' }}
        </p>
    </div>
</article>
