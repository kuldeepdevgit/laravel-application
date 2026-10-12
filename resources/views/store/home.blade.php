@extends('store.layout')

@section('content')
    <section id="hero" class="carousel slide hero" data-bs-ride="carousel" data-bs-interval="6500">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#hero" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#hero" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#hero" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=1800&q=80')">
                    <div class="container">
                        <div class="hero-copy">
                            <p class="eyebrow">New this week</p>
                            <h1>The weekend edit</h1>
                            <p>Throws, kettles, and lamps for a slower morning at home.</p>
                            <a class="btn btn-accent btn-lg" href="{{ route('shop', ['cat' => 'Home']) }}">Shop home goods</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1800&q=80')">
                    <div class="container">
                        <div class="hero-copy">
                            <p class="eyebrow">Out the door</p>
                            <h1>Gear that travels</h1>
                            <p>Shoes, bottles, and a bag that fits under the seat.</p>
                            <a class="btn btn-accent btn-lg" href="{{ route('shop', ['cat' => 'Outdoors']) }}">Shop outdoors</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=1800&q=80')">
                    <div class="container">
                        <div class="hero-copy">
                            <p class="eyebrow">Sound and keys</p>
                            <h1>Quieter desks</h1>
                            <p>Headphones, a compact keyboard, and a speaker for the counter.</p>
                            <a class="btn btn-accent btn-lg" href="{{ route('shop', ['cat' => 'Electronics']) }}">Shop electronics</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#hero" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#hero" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-head">
                <h2>Shop by department</h2>
            </div>
            <div class="cat-grid">
                @foreach ([
                    'Electronics' => '1505740420928-5e560c06d30e',
                    'Home' => '1507473885765-e6ed057f782c',
                    'Fashion' => '1521572163474-6864f9cf17ab',
                    'Kitchen' => '1517668808822-9ebb02f2a0e6',
                    'Outdoors' => '1542291026-7eec264c27ff',
                    'Grocery' => '1447933601403-0c6688de566e',
                    'Beauty' => '1556228720-195a672e8a03',
                    'Stationery' => '1512820790803-83ca734da794',
                ] as $category => $photo)
                    <a class="cat-tile" href="{{ route('shop', ['cat' => $category]) }}">
                        <img src="https://images.unsplash.com/photo-{{ $photo }}?auto=format&fit=crop&w=400&q=70" alt="">
                        <span>{{ \App\Support\Catalog::label($category) }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="band">
        <div class="container">
            <div class="section-head">
                <h2>Today's picks</h2>
                <p class="deal-clock">Ends in <span id="deal-clock" data-end="{{ $dealEndsAt * 1000 }}">08:00:00</span></p>
                <a href="{{ route('shop') }}">See all</a>
            </div>
            <div class="product-grid">
                @foreach ($deals as $product)
                    @include('store.partials.card', ['product' => $product])
                @endforeach
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-head">
                <h2>Popular right now</h2>
                <a href="{{ route('shop', ['sort' => 'reviews']) }}">Most rated</a>
            </div>
            <div class="product-grid">
                @foreach ($popular as $product)
                    @include('store.partials.card', ['product' => $product])
                @endforeach
            </div>
        </div>
    </section>

    <section class="container">
        <div class="row g-3">
            <div class="col-md-6">
                <a class="promo-card" href="{{ route('shop', ['cat' => 'Fashion']) }}" style="background-image: url('https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1200&q=80')">
                    <span>Fashion</span>
                    <strong>Layers for mild weather</strong>
                </a>
            </div>
            <div class="col-md-6">
                <a class="promo-card" href="{{ route('shop', ['cat' => 'Kitchen']) }}" style="background-image: url('https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=1200&q=80')">
                    <span>Kitchen</span>
                    <strong>Cookware that earns its shelf</strong>
                </a>
            </div>
        </div>
    </section>

    @if (count($more))
        <section class="section">
            <div class="container">
                <div class="section-head">
                    <h2>More to explore</h2>
                    <a href="{{ route('shop') }}">Browse the catalog</a>
                </div>
                <div class="product-grid">
                    @foreach ($more as $product)
                        @include('store.partials.card', ['product' => $product])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="note-band">
        <div class="container">
            <p>Useful goods, plain prices, and shipping you can plan around.</p>
        </div>
    </section>

    <section class="section trust">
        <div class="container">
            <ul>
                <li><i class="bi bi-shield-check"></i><strong>Secure checkout</strong><span>Card fields are validated, then discarded. Nothing is charged.</span></li>
                <li><i class="bi bi-arrow-counterclockwise"></i><strong>30-day returns</strong><span>A sample policy line you can replace with your own.</span></li>
                <li><i class="bi bi-truck"></i><strong>Tracked delivery</strong><span>Standard shipping is free from $49.</span></li>
                <li><i class="bi bi-chat-dots"></i><strong>A real help link</strong><span>Point the footer at your own support page.</span></li>
            </ul>
        </div>
    </section>
@endsection
