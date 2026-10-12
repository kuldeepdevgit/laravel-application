<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Northbay — Everyday marketplace')</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect rx='8' width='32' height='32' fill='%23123936'/%3E%3Cpath d='M16 6l3.2 8.2L16 16l-3.2-1.8L16 6z' fill='%23e7b15a'/%3E%3Cpath d='M16 26l-3.2-8.2L16 16l3.2 1.8L16 26z' fill='%23f4f0e8'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,560;9..144,650&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('store/css/store.css') }}" rel="stylesheet">
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <div class="site-top sticky-top">
        <div class="promo-bar">
            <div class="container promo-inner">
                <span>Free shipping on orders over $49</span>
                <span class="d-none d-md-inline">30-day returns · Demo checkout stays in your browser</span>
            </div>
        </div>
        <header class="site-header">
            <div class="container header-row">
                <button class="icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#deptDrawer" aria-label="Open menu">
                    <i class="bi bi-list"></i>
                </button>
                <a class="brand" href="{{ route('home') }}">
                    <svg class="brand-mark" viewBox="0 0 32 32" aria-hidden="true">
                        <circle cx="16" cy="16" r="14" fill="none" stroke="currentColor" stroke-width="1.6"></circle>
                        <path d="M16 5.5l3.4 8.6L16 16.2l-3.4-2.1L16 5.5z" fill="#e7b15a"></path>
                        <path d="M16 26.5l-3.4-8.6L16 15.8l3.4 2.1L16 26.5z" fill="currentColor"></path>
                    </svg>
                    <span class="brand-copy">
                        <span class="brand-name">northbay</span>
                        <span class="brand-tag">marketplace</span>
                    </span>
                </a>
                <form class="search-bar" action="{{ route('shop') }}" method="GET" role="search">
                    <label class="visually-hidden" for="search-cat">Department</label>
                    <select class="form-select search-cat" id="search-cat" name="cat">
                        <option value="">All</option>
                        @foreach ($categories as $value => $label)
                            <option value="{{ $value }}" @selected($navCategory === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <label class="visually-hidden" for="search-q">Search</label>
                    <input class="form-control" id="search-q" name="q" type="search" placeholder="Search Northbay" value="{{ $navQuery }}">
                    <button class="btn search-btn" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
                </form>
                <div class="header-actions">
                    <div class="deliver d-none d-xl-flex">
                        <i class="bi bi-geo-alt"></i>
                        <span><small>Deliver to</small><strong>Portland, OR</strong></span>
                    </div>
                    @auth
                        <a class="text-btn d-none d-md-flex" href="{{ route('dashboard') }}">
                            <small>Hello, {{ auth()->user()->name }}</small>
                            <strong>Dashboard</strong>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="d-none d-lg-flex">
                            @csrf
                            <button class="text-btn" type="submit">
                                <small>Account</small>
                                <strong>Log out</strong>
                            </button>
                        </form>
                    @else
                        <a class="text-btn d-none d-md-flex" href="{{ route('login') }}">
                            <small>Account</small>
                            <strong>Log in</strong>
                        </a>
                    @endauth
                    <a class="cart-btn" href="{{ route('cart.index') }}" aria-label="Cart, {{ $cartCount }} {{ $cartCount === 1 ? 'item' : 'items' }}">
                        <span class="cart-icon">
                            <i class="bi bi-bag"></i>
                            <span class="cart-count">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                        </span>
                        <em class="d-none d-sm-inline">Cart</em>
                    </a>
                </div>
            </div>
        </header>
        <nav class="dept-nav d-none d-lg-block" aria-label="Departments">
            <div class="container dept-scroll">
                <button type="button" data-bs-toggle="offcanvas" data-bs-target="#deptDrawer"><i class="bi bi-list"></i> All departments</button>
                <a href="{{ route('shop') }}">All products</a>
                @foreach ($categories as $value => $label)
                    <a href="{{ route('shop', ['cat' => $value]) }}">{{ $label }}</a>
                @endforeach
            </div>
        </nav>
    </div>

    @if (session('status'))
        <div class="container pt-3">
            <div class="alert alert-warning mb-0" role="status">{{ session('status') }}</div>
        </div>
    @endif

    <main id="main">
        @yield('content')
    </main>

    <footer class="site-footer" id="help">
        <div class="container">
            <div class="row g-4">
                <div class="col-6 col-lg-3">
                    <h2>Shop</h2>
                    <a href="{{ route('shop') }}">All products</a>
                    <a href="{{ route('shop', ['cat' => 'Electronics']) }}">Electronics</a>
                    <a href="{{ route('shop', ['cat' => 'Home']) }}">Home goods</a>
                    <a href="{{ route('shop', ['cat' => 'Kitchen']) }}">Kitchen</a>
                    <a href="{{ route('shop', ['cat' => 'Fashion']) }}">Fashion</a>
                </div>
                <div class="col-6 col-lg-3">
                    <h2>Help</h2>
                    <a href="{{ route('cart.index') }}">Cart</a>
                    <a href="{{ route('checkout.create') }}">Checkout</a>
                    <a href="{{ route('home') }}">Home</a>
                    <a href="{{ route('templates.index') }}">Templates</a>
                </div>
                <div class="col-lg-6">
                    <h2>Notes from Northbay</h2>
                    <p>This storefront runs on Laravel. The catalog lives in <code>app/Support/Catalog.php</code>.</p>
                    <form class="newsletter" method="POST" action="{{ route('newsletter.store') }}">
                        @csrf
                        <label class="visually-hidden" for="news-email">Email address</label>
                        <input id="news-email" class="form-control @error('email') is-invalid @enderror" type="email" name="email" required placeholder="Email address" value="{{ old('email') }}">
                        <button class="btn btn-accent" type="submit">Join</button>
                    </form>
                    @error('email')
                        <p class="news-msg">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">© {{ date('Y') }} Northbay. A Laravel storefront.</div>
        </div>
    </footer>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="deptDrawer" aria-labelledby="deptLabel">
        <div class="offcanvas-header">
            <h2 class="offcanvas-title h5" id="deptLabel">Departments</h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body dept-list">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('shop') }}">All products</a>
            @foreach ($categories as $value => $label)
                <a href="{{ route('shop', ['cat' => $value]) }}">{{ $label }}</a>
            @endforeach
            <a href="{{ route('cart.index') }}">Cart</a>
            @auth
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}">Log in</a>
                <a href="{{ route('register') }}">Register</a>
            @endauth
        </div>
    </div>

    @if (session('added'))
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div class="toast" role="status" aria-live="polite" aria-atomic="true">
                <div class="toast-body d-flex justify-content-between align-items-center gap-3">
                    <span>{{ session('added') }} added to cart</span>
                    <a href="{{ route('cart.index') }}" class="btn btn-sm btn-accent">View cart</a>
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/storefront.js') }}"></script>
</body>
</html>
