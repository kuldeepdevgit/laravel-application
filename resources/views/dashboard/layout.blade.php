<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard')</title>
    <link rel="icon" href="{{ asset('images/green-smart-india.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,560;9..144,650&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
</head>
<body class="dash-body">
    <div class="d-flex">
        <aside class="dash-side flex-shrink-0">
            @include('dashboard.nav')
        </aside>

        <div class="dash-main flex-grow-1">
            <header class="dash-top">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-dark d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#dashMenu" aria-label="Open menu">
                        <i class="bi bi-list"></i>
                    </button>
                    <a class="dash-mark d-lg-none" href="{{ route('dashboard') }}">
                        <img src="{{ asset('images/green-smart-india.png') }}" alt="Green Smart India">
                    </a>
                    <h1>@yield('heading', 'Dashboard')</h1>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-dark btn-sm" type="submit">Log out</button>
                </form>
            </header>

            <div class="p-3 p-lg-4">
                @yield('content')
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-start dash-drawer" tabindex="-1" id="dashMenu" aria-label="Menu">
        <div class="offcanvas-header">
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            @include('dashboard.nav')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
