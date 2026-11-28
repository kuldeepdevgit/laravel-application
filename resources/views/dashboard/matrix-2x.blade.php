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
@else
    <p class="text-secondary">You do not have a seat in this pool yet.</p>
@endif

@if ($autoPoolChildren->isEmpty())
    <p class="mb-0 text-secondary">No members under you in this pool yet.</p>
@endif

<div class="pool-board">
    <div class="pool-mark" aria-hidden="true"><i class="bi bi-person-fill"></i></div>
    <ul class="pool-tree">
        @include('dashboard.pool-branch', ['node' => $matrix])
    </ul>
</div>
