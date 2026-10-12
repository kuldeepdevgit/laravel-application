<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard')</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect rx='8' width='32' height='32' fill='%2314213d'/%3E%3Ccircle cx='16' cy='16' r='6' fill='%23e07a3d'/%3E%3C/svg%3E">
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
            <a class="dash-brand" href="{{ route('dashboard') }}">Member desk</a>
            <p class="dash-user">{{ auth()->user()->name }}</p>
            @include('dashboard.nav')
        </aside>

        <div class="dash-main flex-grow-1">
            <header class="dash-top">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-dark d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#dashMenu" aria-label="Open menu">
                        <i class="bi bi-list"></i>
                    </button>
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

    <div class="offcanvas offcanvas-start" tabindex="-1" id="dashMenu" aria-labelledby="dashMenuLabel">
        <div class="offcanvas-header">
            <h2 class="offcanvas-title h5" id="dashMenuLabel">Member desk</h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            @include('dashboard.nav')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
