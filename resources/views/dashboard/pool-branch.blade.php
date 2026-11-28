<li>
    <div @class(['pool-node', 'is-filled' => $node['user'] instanceof \App\Models\User, 'is-empty' => ! ($node['user'] instanceof \App\Models\User)])>
        @if ($node['user'] instanceof \App\Models\User)
            <strong>{{ filled($node['user']->user_name) ? $node['user']->user_name : $node['user']->name }}</strong>
            @if (filled($node['user']->user_name))
                <small>{{ $node['user']->name }}</small>
            @endif
        @else
            <span class="visually-hidden">Empty seat</span>
        @endif
    </div>
    @if ($node['children'] !== [])
        <ul>
            @foreach ($node['children'] as $child)
                @include('dashboard.pool-branch', ['node' => $child])
            @endforeach
        </ul>
    @endif
</li>
