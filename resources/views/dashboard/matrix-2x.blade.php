@if ($autoPoolSeat instanceof \App\Models\UserAutoPoolTree)
    <div class="row g-2 mb-4">
        <div class="col-6 col-lg-4">
            <article class="stat-card">
                <span>Your position</span>
                <strong>{{ $autoPoolSeat->position }}</strong>
            </article>
        </div>
        <div class="col-6 col-lg-4">
            <article class="stat-card">
                <span>Placed under</span>
                <strong>{{ $autoPoolSeat->tree?->name ?: '—' }}</strong>
                @if (filled($autoPoolSeat->tree?->user_name))
                    <small>{{ $autoPoolSeat->tree->user_name }}</small>
                @endif
            </article>
        </div>
    </div>
@endif

<div class="pool-board">
    <ul class="pool-tree">
        @include('dashboard.pool-branch', ['node' => $matrix])
    </ul>
</div>
