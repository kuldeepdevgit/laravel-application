<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Brightpath — Bootstrap template')</title>
    <link rel="icon" href="{{ asset('images/green-smart-india.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,560;9..144,650&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/brightpath.css') }}" rel="stylesheet">
</head>
<body class="bp-body">
    <nav class="navbar navbar-expand-lg bp-nav sticky-top">
        <div class="container">
            <a class="navbar-brand bp-brand" href="{{ route('bootstrap.home') }}" aria-label="Green Smart India E Services">
                @include('partials.logo', ['class' => 'brand-logo', 'size' => 56])
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#bpNav" aria-controls="bpNav" aria-expanded="false" aria-label="Open menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="bpNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link @if (request()->routeIs('bootstrap.home')) active @endif" href="{{ route('bootstrap.home') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link @if (request()->routeIs('bootstrap.services')) active @endif" href="{{ route('bootstrap.services') }}">Services</a></li>
                    <li class="nav-item"><a class="nav-link @if (request()->routeIs('bootstrap.contact')) active @endif" href="{{ route('bootstrap.contact') }}">Contact</a></li>
                    <li class="nav-item ms-lg-2">
                        @auth
                            <a class="btn bp-btn" href="{{ route('dashboard') }}">Dashboard</a>
                        @else
                            <a class="btn bp-btn" href="{{ route('login') }}">Member login</a>
                        @endauth
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main id="main">
        @yield('content')
    </main>

    <footer class="bp-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h2>Brightpath</h2>
                    <p class="mb-0">A Bootstrap template. Switch it on from the templates list and it becomes the front page.</p>
                </div>
                <div class="col-6 col-md-2">
                    <h2>Pages</h2>
                    <a href="{{ route('bootstrap.home') }}">Home</a>
                    <a href="{{ route('bootstrap.services') }}">Services</a>
                    <a href="{{ route('bootstrap.contact') }}">Contact</a>
                </div>
                <div class="col-6 col-md-3">
                    <h2>Also here</h2>
                    <a href="{{ route('shop') }}">Northbay shop</a>
                    <a href="{{ route('ai.home') }}">Keel</a>
                    <a href="{{ route('templates.index') }}">Templates</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
