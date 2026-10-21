@php
    $section = request()->route('section');
    $profileItems = [
        ['label' => 'Edit Profile', 'section' => 'edit-profile'],
        ['label' => 'Change Password', 'section' => 'change-password'],
        ['label' => 'Update Wallet Address', 'section' => 'update-wallet-address'],
    ];
    $profileOpen = in_array($section, array_column($profileItems, 'section'), true);
    $items = [
        ['label' => 'Dashboard', 'icon' => 'bi-house-fill', 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard'), 'chevron' => false],
        ['label' => 'Ref Link', 'icon' => 'bi-link-45deg', 'href' => route('dashboard.section', 'ref-link'), 'active' => $section === 'ref-link', 'chevron' => false],
        ['label' => 'Team Report', 'icon' => 'bi-gear', 'href' => route('dashboard.section', 'team-report'), 'active' => $section === 'team-report', 'chevron' => true],
        ['label' => 'Fund Management', 'icon' => 'bi-gear', 'href' => route('dashboard.section', 'fund-management'), 'active' => $section === 'fund-management', 'chevron' => true],
        ['label' => 'Income Report', 'icon' => 'bi-gear', 'href' => route('dashboard.section', 'income-report'), 'active' => $section === 'income-report', 'chevron' => true],
        ['label' => 'Withdrawal', 'icon' => 'bi-gear', 'href' => route('dashboard.section', 'withdrawal'), 'active' => $section === 'withdrawal', 'chevron' => true],
        ['label' => 'Support', 'icon' => 'bi-gear', 'href' => route('dashboard.section', 'support'), 'active' => $section === 'support', 'chevron' => true],
        ['label' => 'Document List', 'icon' => 'bi-house-fill', 'href' => route('dashboard.section', 'document-list'), 'active' => $section === 'document-list', 'chevron' => false],
    ];
@endphp

<nav class="dash-nav" aria-label="Member">
    <a class="dash-item @if ($items[0]['active']) is-active @endif" href="{{ $items[0]['href'] }}" @if ($items[0]['active']) aria-current="page" @endif>
        <i class="bi {{ $items[0]['icon'] }}" aria-hidden="true"></i>
        <span>{{ $items[0]['label'] }}</span>
    </a>
    <a class="dash-item @if ($items[1]['active']) is-active @endif" href="{{ $items[1]['href'] }}" @if ($items[1]['active']) aria-current="page" @endif>
        <i class="bi {{ $items[1]['icon'] }}" aria-hidden="true"></i>
        <span>{{ $items[1]['label'] }}</span>
    </a>

    <details class="dash-group" @if ($profileOpen) open @endif>
        <summary class="dash-item">
            <i class="bi bi-gear" aria-hidden="true"></i>
            <span>Manage Profile</span>
            <i class="bi bi-chevron-right dash-chevron" aria-hidden="true"></i>
        </summary>
        <div class="dash-sub">
            @foreach ($profileItems as $item)
                <a class="dash-subitem @if ($section === $item['section']) is-active @endif" href="{{ route('dashboard.section', $item['section']) }}" @if ($section === $item['section']) aria-current="page" @endif>
                    <span class="dash-mark" aria-hidden="true"></span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </details>

    @foreach (array_slice($items, 2) as $item)
        <a class="dash-item @if ($item['active']) is-active @endif" href="{{ $item['href'] }}" @if ($item['active']) aria-current="page" @endif>
            <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
            <span>{{ $item['label'] }}</span>
            @if ($item['chevron'])
                <i class="bi bi-chevron-right dash-chevron" aria-hidden="true"></i>
            @endif
        </a>
    @endforeach

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="dash-item" type="submit">
            <i class="bi bi-house-fill" aria-hidden="true"></i>
            <span>Logout</span>
        </button>
    </form>
</nav>
