@php
    $section = request()->route('section');
    $account = $dashboardAccount ?? auth()->user();
    $groups = [
        [
            'label' => 'Manage Profile',
            'icon' => 'bi-person-gear',
            'items' => [
                ['label' => 'Edit Profile', 'section' => 'edit-profile', 'icon' => 'bi-pencil-square'],
                ['label' => 'Change Password', 'section' => 'change-password', 'icon' => 'bi-key'],
                ['label' => 'Update Wallet Address', 'section' => 'update-wallet-address', 'icon' => 'bi-wallet'],
            ],
        ],
        [
            'label' => 'Team Report',
            'icon' => 'bi-people',
            'items' => [
                ['label' => 'Qualification', 'section' => 'qualification', 'icon' => 'bi-patch-check'],
                ['label' => 'My Team', 'section' => 'my-team', 'icon' => 'bi-person-lines-fill'],
                ['label' => 'My Downline Team', 'section' => 'my-downline-team', 'icon' => 'bi-diagram-3'],
            ],
        ],
        [
            'label' => 'Fund Management',
            'icon' => 'bi-wallet2',
            'items' => [
                ['label' => 'Fund Request (USDT)', 'section' => 'fund-request-usdt', 'icon' => 'bi-plus-circle'],
                ['label' => 'Fund Request List', 'section' => 'fund-request-list', 'icon' => 'bi-card-list'],
                ['label' => 'Fund From Admin', 'section' => 'fund-from-admin', 'icon' => 'bi-person-down'],
                ['label' => 'Upgrade List', 'section' => 'upgrade-list', 'icon' => 'bi-arrow-up-circle'],
                ['label' => 'Activation/ Upgrade', 'section' => 'activation-upgrade', 'icon' => 'bi-lightning-charge'],
                ['label' => 'Fund Wallet', 'section' => 'fund-wallet', 'icon' => 'bi-wallet-fill'],
            ],
        ],
        [
            'label' => 'Income Report',
            'icon' => 'bi-graph-up-arrow',
            'items' => [
                ['label' => 'Franchise Income History', 'section' => 'franchise-income-history', 'icon' => 'bi-percent'],
                ['label' => '3x Matrix Level Income', 'section' => 'matrix-level-income', 'icon' => 'bi-grid-3x3-gap'],
                ['label' => 'Wallet', 'section' => 'wallet', 'icon' => 'bi-wallet2'],
            ],
        ],
        [
            'label' => 'Withdrawal',
            'icon' => 'bi-cash-coin',
            'items' => [
                ['label' => 'Withdrawal List', 'section' => 'withdrawal-list', 'icon' => 'bi-list-check'],
                ['label' => 'Withdrawal Now', 'section' => 'withdrawal-now', 'icon' => 'bi-arrow-down-circle'],
            ],
        ],
    ];
@endphp

<nav class="dash-nav" aria-label="Member">
    <a class="dash-brand" href="{{ route('dashboard') }}">
        <img src="{{ asset('images/green-smart-india.png') }}" alt="Green Smart India E Services" width="220" height="220">
    </a>
    <div class="dash-status {{ $account?->isPaid() ? 'is-active' : 'is-inactive' }}" role="status">
        <i class="bi {{ $account?->isPaid() ? 'bi-person-check' : 'bi-person-x' }}" aria-hidden="true"></i>
        <span>Account</span>
        <strong>{{ $account?->isPaid() ? 'Active' : 'Inactive' }}</strong>
    </div>
    <a class="dash-item @if (request()->routeIs('dashboard')) is-active @endif" href="{{ route('dashboard') }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif>
        <i class="bi bi-speedometer2" aria-hidden="true"></i>
        <span>Dashboard</span>
    </a>
    <a class="dash-item @if ($section === 'ref-link') is-active @endif" href="{{ route('dashboard.section', 'ref-link') }}" @if ($section === 'ref-link') aria-current="page" @endif>
        <i class="bi bi-link-45deg" aria-hidden="true"></i>
        <span>Ref Link</span>
    </a>
    <a class="dash-item @if ($section === 'order-history' || request()->routeIs('dashboard.orders.show')) is-active @endif" href="{{ route('dashboard.section', 'order-history') }}" @if ($section === 'order-history' || request()->routeIs('dashboard.orders.show')) aria-current="page" @endif>
        <i class="bi bi-bag-check" aria-hidden="true"></i>
        <span>Order History</span>
    </a>

    @foreach ($groups as $group)
        @php
            $groupOpen = in_array($section, array_column($group['items'], 'section'), true);
        @endphp
        <details class="dash-group" @if ($groupOpen) open @endif>
            <summary class="dash-item">
                <i class="bi {{ $group['icon'] }}" aria-hidden="true"></i>
                <span>{{ $group['label'] }}</span>
                <i class="bi bi-chevron-right dash-chevron" aria-hidden="true"></i>
            </summary>
            <div class="dash-sub">
                @foreach ($group['items'] as $item)
                    <a class="dash-subitem @if ($section === $item['section']) is-active @endif" href="{{ route('dashboard.section', $item['section']) }}" @if ($section === $item['section']) aria-current="page" @endif>
                        <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </details>
    @endforeach

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="dash-item" type="submit">
            <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
            <span>Logout</span>
        </button>
    </form>
</nav>
