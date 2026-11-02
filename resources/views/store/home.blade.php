@extends('store.layout')

@section('content')
    @if ($banners->isNotEmpty())
        <section id="hero" class="carousel slide hero" @if ($banners->count() > 1) data-bs-ride="carousel" data-bs-interval="6500" @endif>
            @if ($banners->count() > 1)
                <div class="carousel-indicators">
                    @foreach ($banners as $banner)
                        <button type="button" data-bs-target="#hero" data-bs-slide-to="{{ $loop->index }}" @class(['active' => $loop->first]) @if ($loop->first) aria-current="true" @endif aria-label="Slide {{ $loop->iteration }}"></button>
                    @endforeach
                </div>
            @endif
            <div class="carousel-inner">
                @foreach ($banners as $banner)
                    <div @class(['carousel-item', 'active' => $loop->first])>
                        <div class="hero-slide" @if ($banner->imageUrl()) style="background-image: url('{{ $banner->imageUrl() }}')" @endif>
                            <div class="container">
                                <div class="hero-copy">
                                    @if ($banner->eyebrow)
                                        <p class="eyebrow">{{ $banner->eyebrow }}</p>
                                    @endif
                                    <h1>{{ $banner->title }}</h1>
                                    @if ($banner->description)
                                        <p>{{ $banner->description }}</p>
                                    @endif
                                    @if ($banner->button_label && $banner->button_url)
                                        <a class="btn btn-accent btn-lg" href="{{ $banner->button_url }}">{{ $banner->button_label }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($banners->count() > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#hero" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#hero" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            @endif
        </section>
    @elseif ($showDefaultBanners)
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
    @endif

    @if ($departments->isNotEmpty())
        <section class="section" aria-labelledby="shop-departments">
            <div class="container">
                <div class="section-head">
                    <h2 id="shop-departments">Shop by department</h2>
                </div>
                <div class="dept-slider" data-dept-slider data-per-view="8" data-loop="true" data-clone="true">
                    <button class="dept-slider-btn dept-slider-prev" type="button" aria-label="Previous departments">
                        <i class="bi bi-chevron-left" aria-hidden="true"></i>
                    </button>
                    <div class="dept-slider-window">
                        <div class="dept-slider-track">
                            @foreach ($departments as $department)
                                <a class="cat-tile" href="{{ route('shop', ['cat' => $department->slug]) }}">
                                    @if ($department->imageUrl())
                                        <img src="{{ $department->imageUrl() }}" alt="">
                                    @else
                                        <span class="cat-fallback" aria-hidden="true">{{ mb_strtoupper(mb_substr($department->name, 0, 1)) }}</span>
                                    @endif
                                    <span>{{ $department->name }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <button class="dept-slider-btn dept-slider-next" type="button" aria-label="Next departments">
                        <i class="bi bi-chevron-right" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </section>
    @endif

    <section class="section" aria-labelledby="all-products">
        <div class="container">
            <div class="section-head">
                <h2 id="all-products">All products</h2>
                <a href="{{ route('shop') }}">See the shop</a>
            </div>
            @if ($products->isEmpty())
                <p class="product-blurb">No products yet.</p>
            @else
                <div class="product-grid">
                    @foreach ($products as $product)
                        @include('store.partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            @endif
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
