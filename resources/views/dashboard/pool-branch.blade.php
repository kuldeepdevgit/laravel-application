<li>
    <div class="pool-seat">
        <div class="pool-mark" aria-hidden="true"><i class="bi bi-person-fill"></i></div>
        <div @class(['pool-node', 'is-filled' => $node['user'] instanceof \App\Models\User, 'is-empty' => ! ($node['user'] instanceof \App\Models\User)])>
            <span>Name</span>
            <strong>{{ $node['user'] instanceof \App\Models\User ? $node['user']->name : '—' }}</strong>
            <span>ID</span>
            <strong>{{ $node['user'] instanceof \App\Models\User && filled($node['user']->user_name) ? $node['user']->user_name : '—' }}</strong>
        </div>
    </div>
    @if ($node['children'] !== [])
        <ul>
            @foreach ($node['children'] as $child)
                @include('dashboard.pool-branch', ['node' => $child])
            @endforeach
        </ul>
    @endif
</li>
