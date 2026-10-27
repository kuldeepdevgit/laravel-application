<nav class="dash-nav" aria-label="Admin">
    <a class="dash-brand" href="{{ route('admin.dashboard') }}">
        <img src="{{ asset('images/green-smart-india.png') }}" alt="Green Smart India E Services" width="220" height="220">
    </a>
    <p class="admin-role">Super Admin</p>
    <a class="dash-item @if (request()->routeIs('admin.dashboard')) is-active @endif" href="{{ route('admin.dashboard') }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>
        <i class="bi bi-speedometer2" aria-hidden="true"></i>
        <span>Dashboard</span>
    </a>
    <a class="dash-item @if (request()->routeIs('admin.categories.*')) is-active @endif" href="{{ route('admin.categories.index') }}" @if (request()->routeIs('admin.categories.*')) aria-current="page" @endif>
        <i class="bi bi-grid" aria-hidden="true"></i>
        <span>Category</span>
    </a>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="dash-item" type="submit">
            <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
            <span>Logout</span>
        </button>
    </form>
</nav>
