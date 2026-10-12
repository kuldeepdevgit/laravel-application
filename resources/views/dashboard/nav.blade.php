<nav class="dash-nav d-grid gap-1" aria-label="Member">
    <a class="@if (request()->routeIs('dashboard')) active @endif" href="{{ route('dashboard') }}">
        <i class="bi bi-grid"></i> Overview
    </a>
    <a href="{{ route('bootstrap.home') }}">
        <i class="bi bi-layout-text-window"></i> Brightpath
    </a>
    <a href="{{ route('templates.index') }}">
        <i class="bi bi-collection"></i> Templates
    </a>
    <a href="{{ route('home') }}">
        <i class="bi bi-house"></i> Front page
    </a>
</nav>
