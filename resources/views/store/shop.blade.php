@extends('store.layout')

@section('title', $heading.' — Northbay')

@section('content')
    <div class="container page">
        <form id="filters" method="GET" action="{{ route('shop') }}">
            @if ($filters['q'] !== '')
                <input type="hidden" name="q" value="{{ $filters['q'] }}">
            @endif
        </form>
        <div class="row g-4">
            <aside class="col-lg-3">
                <div class="filter-card">
                    <h2>Department</h2>
                    @foreach (['' => 'All departments'] + $categories as $value => $label)
                        <label class="cat-option">
                            <input type="radio" form="filters" name="cat" value="{{ $value }}" @checked($filters['cat'] === $value || $filters['match'] === $value) onchange="document.getElementById('filters').submit()">
                            {{ $label }}
                        </label>
                    @endforeach
                    <h2>Highest price <output id="price-label">{{ \App\Support\Catalog::money($filters['max']) }}</output></h2>
                    <input class="form-range" form="filters" type="range" name="max" min="10" max="200" step="1" value="{{ $filters['max'] }}" oninput="document.getElementById('price-label').textContent = '$' + Number(this.value).toFixed(2)" onchange="document.getElementById('filters').submit()">
                    <div class="form-check mt-3">
                        <input class="form-check-input" form="filters" type="checkbox" id="rated" name="rated" value="1" @checked($filters['rated']) onchange="document.getElementById('filters').submit()">
                        <label class="form-check-label" for="rated">4 stars and up</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" form="filters" type="checkbox" id="instock" name="stock" value="1" @checked($filters['stock']) onchange="document.getElementById('filters').submit()">
                        <label class="form-check-label" for="instock">In stock only</label>
                    </div>
                    <a class="btn btn-link px-0" href="{{ route('shop') }}">Clear filters</a>
                </div>
            </aside>
            <div class="col-lg-9">
                <div class="shop-toolbar">
                    <div>
                        <h1>{{ $heading }}</h1>
                        <p>{{ $products->total() }} {{ $products->total() === 1 ? 'product' : 'products' }}</p>
                    </div>
                    <label class="visually-hidden" for="sort">Sort</label>
                    <select id="sort" class="form-select" name="sort" form="filters" onchange="document.getElementById('filters').submit()">
                        @foreach (['featured' => 'Featured', 'price-asc' => 'Price: low to high', 'price-desc' => 'Price: high to low', 'rating' => 'Top rated', 'reviews' => 'Most rated'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($products->isEmpty())
                    <div class="empty-state">
                        <i class="bi bi-search"></i>
                        <h2>No matches</h2>
                        <p>Try another department or a higher price.</p>
                        <a class="btn btn-accent" href="{{ route('shop') }}">Clear filters</a>
                    </div>
                @else
                    <div class="product-grid compact">
                        @foreach ($products as $product)
                            @include('store.partials.card', ['product' => $product])
                        @endforeach
                    </div>
                    @if ($products->lastPage() > 1)
                        <nav class="pager" aria-label="Pages">
                            @for ($i = 1; $i <= $products->lastPage(); $i++)
                                <a href="{{ $products->url($i) }}" @if ($i === $products->currentPage()) aria-current="page" @endif>{{ $i }}</a>
                            @endfor
                        </nav>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection
