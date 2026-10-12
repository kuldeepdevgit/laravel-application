@extends('store.layout')

@section('title', $product['name'].' — Northbay')

@php
    $availability = \App\Support\Catalog::availability($product);
    $save = \App\Support\Catalog::savePercent($product);
    $cap = \App\Support\Catalog::maxQty($product);
@endphp

@section('content')
    <div class="container page">
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('shop', ['cat' => $product['category']]) }}">{{ \App\Support\Catalog::label($product['category']) }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $product['name'] }}</li>
            </ol>
        </nav>
        <div class="product-layout">
            <div>
                <div class="gallery-main">
                    <img id="gallery-main" src="{{ $product['images'][0] }}" alt="{{ $product['name'] }}">
                </div>
                <div class="thumbs">
                    @foreach ($product['images'] as $index => $image)
                        <button type="button" data-thumb="{{ $image }}" aria-label="Photo {{ $index + 1 }}" aria-pressed="{{ $index === 0 ? 'true' : 'false' }}">
                            <img src="{{ $image }}" alt="">
                        </button>
                    @endforeach
                </div>
            </div>
            <div>
                <p class="product-cat mb-1">{{ \App\Support\Catalog::label($product['category']) }}</p>
                <h1 class="product-title">{{ $product['name'] }}</h1>
                <div class="stars" aria-label="Rated {{ number_format($product['rating'], 1) }} out of 5">
                    {!! \App\Support\Catalog::stars($product['rating']) !!}
                    <span class="rating-count">{{ number_format($product['reviews']) }} ratings</span>
                </div>
                <div class="price-row mt-3">
                    <span class="price-lg">{{ \App\Support\Catalog::money($product['price']) }}</span>
                    @if ($product['compare'])
                        <s class="was">{{ \App\Support\Catalog::money($product['compare']) }}</s>
                        <span class="save">Save {{ $save }}%</span>
                    @endif
                </div>
                <p class="mt-3 mb-3">{{ $product['blurb'] }}</p>
                @if (count($product['colors']))
                    <div class="mb-3">
                        <div class="fw-semibold mb-2">Color</div>
                        <div class="choice-row">
                            @foreach ($product['colors'] as $index => $color)
                                <button type="button" class="choice" aria-pressed="{{ $index === 0 ? 'true' : 'false' }}">{{ $color }}</button>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if (count($product['sizes']))
                    <div class="mb-3">
                        <div class="fw-semibold mb-2">Size</div>
                        <div class="choice-row">
                            @foreach ($product['sizes'] as $index => $size)
                                <button type="button" class="choice" aria-pressed="{{ $index === 0 ? 'true' : 'false' }}">{{ $size }}</button>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="buy-box">
                    <p class="product-ship {{ $availability['class'] }} mb-2"><i class="bi bi-truck"></i> {{ $availability['text'] }}</p>
                    @if ($cap === 0)
                        <button class="btn btn-accent w-100" type="button" disabled>Sold out</button>
                    @else
                        <form method="POST" action="{{ route('cart.store') }}">
                            @csrf
                            <input type="hidden" name="product" value="{{ $product['id'] }}">
                            <label class="fw-semibold mb-2" for="qty">Quantity</label>
                            <input id="qty" class="form-control qty-input mb-3" type="number" name="qty" min="1" max="{{ $cap }}" value="1">
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-accent flex-grow-1" type="submit">Add to cart</button>
                                <button class="btn btn-ghost" type="submit" name="buy_now" value="1">Buy now</button>
                            </div>
                        </form>
                    @endif
                    <div class="ship-box">
                        <i class="bi bi-box-seam"></i>
                        <div>
                            <strong>Arrives in 2–4 business days</strong>
                            <p>Standard shipping is free on orders of $49 or more.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-tabs">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item"><button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#tab-about">About</button></li>
                <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#tab-specs">Details</button></li>
                <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#tab-reviews">Sample reviews</button></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-about">
                    <p>{{ $product['blurb'] }}</p>
                    <p class="mb-0">{{ $product['details'] }}</p>
                </div>
                <div class="tab-pane fade" id="tab-specs">
                    <table class="table mb-0">
                        <tbody>
                            @foreach ($product['specs'] as [$label, $value])
                                <tr><th scope="row">{{ $label }}</th><td>{{ $value }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="tab-reviews">
                    <p class="fine">Sample copy for this template, not live customer feedback.</p>
                    @foreach ($reviews as $review)
                        <article class="review">
                            <div class="stars" aria-label="Rated {{ $review['stars'] }} out of 5">{!! \App\Support\Catalog::stars($review['stars']) !!}</div>
                            <h3>{{ $review['title'] }}</h3>
                            <p>{{ $review['body'] }}</p>
                            <p class="fine mb-0">Verified buyer · {{ $review['date'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>

        @if (count($related))
            <section class="section px-0">
                <div class="section-head">
                    <h2>More in {{ \App\Support\Catalog::label($product['category']) }}</h2>
                    <a href="{{ route('shop', ['cat' => $product['category']]) }}">See the department</a>
                </div>
                <div class="product-grid">
                    @foreach ($related as $item)
                        @include('store.partials.card', ['product' => $item])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
