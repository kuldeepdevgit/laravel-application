<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Keel — AI design tool')</title>
    <link rel="icon" href="{{ asset('images/green-smart-india.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/keel.css') }}?v={{ @filemtime(public_path('css/keel.css')) }}" rel="stylesheet">
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <header class="site-nav">
        <div class="container nav-row">
            <a class="brand" href="{{ route('home') }}" aria-label="Green Smart India E Services">
                @include('partials.logo', ['class' => 'brand-logo', 'size' => 58])
            </a>
            <nav class="nav-links" aria-label="Primary">
                <a href="{{ route('ai.home') }}#features">Feature</a>
                <a href="{{ route('ai.pricing') }}" @if (request()->routeIs('ai.pricing')) aria-current="page" @endif>Pricing</a>
                <a href="{{ route('ai.pricing') }}#faq">FAQ</a>
                <a href="{{ route('ai.product') }}" @if (request()->routeIs('ai.product')) aria-current="page" @endif>Docs</a>
                <a href="{{ route('ai.pricing') }}">Buy</a>
            </nav>
            <div class="nav-actions">
                @auth
                    <a class="btn login-btn" href="{{ route('dashboard') }}">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn login-btn" type="submit">Log out</button>
                    </form>
                @else
                    <a class="btn login-btn" href="{{ route('login') }}">Log in</a>
                @endauth
                <button class="menu-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#aiMenu" aria-label="Open menu">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>
    </header>

    <main id="main">
        @yield('content')
    </main>

    @unless ($hideFooter ?? false)
        <footer class="site-footer">
            <div class="container">
                <div class="row g-4">
                    <div class="col-md-4">
                        <h2>Keel</h2>
                        <p>An AI desk template. The workspace on this site answers from a script, so you can design the front end before you connect a model.</p>
                    </div>
                    <div class="col-6 col-md-2">
                        <h2>Look</h2>
                        <a href="{{ route('ai.product') }}">Product</a>
                        <a href="{{ route('ai.pricing') }}">Pricing</a>
                        <a href="{{ route('ai.workspace') }}">Desk</a>
                    </div>
                    <div class="col-6 col-md-2">
                        <h2>Also here</h2>
                        <a href="{{ route('shop') }}">Northbay shop</a>
                        <a href="{{ route('templates.index') }}">Templates</a>
                    </div>
                </div>
                <div class="footer-bottom">© {{ date('Y') }} Keel. A Laravel template.</div>
            </div>
        </footer>
    @endunless

    <div class="offcanvas offcanvas-start" tabindex="-1" id="aiMenu" aria-labelledby="aiMenuLabel">
        <div class="offcanvas-header">
            <a href="{{ route('home') }}" id="aiMenuLabel">
                @include('partials.logo', ['class' => 'brand-logo', 'size' => 48])
            </a>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-grid gap-2">
            <a href="{{ route('ai.home') }}#features">Feature</a>
            <a href="{{ route('ai.pricing') }}">Pricing</a>
            <a href="{{ route('ai.pricing') }}#faq">FAQ</a>
            <a href="{{ route('ai.product') }}">Docs</a>
            <a href="{{ route('ai.pricing') }}">Buy</a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/keel.js') }}"></script>
</body>
</html>
