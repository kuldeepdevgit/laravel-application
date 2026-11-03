@extends('store.layout')

@section('title', $product->name.' — Northbay')

@section('content')
    <div class="container page">
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                @if ($product->category)
                    <li class="breadcrumb-item"><a href="{{ route('shop', ['cat' => $product->category->slug]) }}">{{ $product->category->name }}</a></li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
            </ol>
        </nav>
        <div class="product-layout">
            <div class="gallery-main">
                @if ($product->imageUrl())
                    <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}">
                @else
                    <span class="product-fallback" aria-hidden="true">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>
                @endif
            </div>
            <div>
                @if ($product->category)
                    <p class="product-cat mb-1">{{ $product->category->name }}</p>
                @endif
                <h1 class="product-title">{{ $product->name }}</h1>
                <div class="price-row mt-3">
                    <span class="price-lg">{{ $product->formattedPrice() }}</span>
                    @if ($product->formattedComparePrice())
                        <s class="was">{{ $product->formattedComparePrice() }}</s>
                    @endif
                    <span class="product-bv">BV {{ $product->formattedBv() }}</span>
                </div>
                @if ($product->description)
                    <p class="mt-3 mb-3">{{ $product->description }}</p>
                @endif
                <p class="product-ship {{ $product->stock === 0 ? 'out' : '' }}">
                    <i class="bi bi-box-seam"></i>
                    {{ $product->stock === 0 ? 'Sold out' : $product->stock.' in stock' }}
                </p>
            </div>
        </div>

        @if ($related->isNotEmpty())
            <section class="section px-0">
                <div class="section-head">
                    <h2>More in {{ $product->category?->name ?? 'the shop' }}</h2>
                </div>
                <div class="product-grid">
                    @foreach ($related as $item)
                        @include('store.partials.product-card', ['product' => $item])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
