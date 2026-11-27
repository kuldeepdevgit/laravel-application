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
@else
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0 team-table">
            <thead>
                <tr>
                    <th scope="col">Position</th>
                    <th scope="col">Date</th>
                    <th scope="col">Member</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($autoPoolChildren as $child)
                    <tr>
                        <td>{{ $child->position }}</td>
                        <td>{{ $child->created_at?->format('M j, Y') ?: '—' }}</td>
                        <td>
                            {{ $child->user?->name ?: '—' }}
                            @if (filled($child->user?->user_name))
                                <div class="text-secondary">{{ $child->user->user_name }}</div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
