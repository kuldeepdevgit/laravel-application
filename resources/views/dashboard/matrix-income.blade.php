<div class="row g-2 mb-4">
    <div class="col-6 col-lg-4">
        <article class="stat-card">
            <span>Level 1</span>
            <strong>₹{{ number_format((float) $levelOneTotal, 2) }}</strong>
            <small>20% from members you sponsor</small>
        </article>
    </div>
    <div class="col-6 col-lg-4">
        <article class="stat-card">
            <span>Level 2</span>
            <strong>₹{{ number_format((float) $levelTwoTotal, 2) }}</strong>
            <small>20% from their members</small>
        </article>
    </div>
    <div class="col-12 col-lg-4">
        <article class="stat-card">
            <span>Total income</span>
            <strong>₹{{ number_format((float) $levelIncomeTotal, 2) }}</strong>
            <small>Level 1 + level 2</small>
        </article>
    </div>
</div>

@if ($levelIncomes->isEmpty())
    <p class="mb-0 text-secondary">No level income yet.</p>
@else
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0 team-table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Date</th>
                    <th scope="col">Level</th>
                    <th scope="col">From</th>
                    <th scope="col">Order</th>
                    <th scope="col">Order BV</th>
                    <th scope="col">Income</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($levelIncomes as $income)
                    <tr>
                        <td>{{ $levelIncomes->firstItem() + $loop->index }}</td>
                        <td>{{ $income->created_at?->format('M j, Y') ?: '—' }}</td>
                        <td>Level {{ $income->level }}</td>
                        <td>
                            {{ $income->payUser?->name ?: '—' }}
                            @if (filled($income->payUser?->user_name))
                                <div class="text-secondary">{{ $income->payUser->user_name }}</div>
                            @endif
                        </td>
                        <td>{{ $income->order?->number ?: '—' }}</td>
                        <td>{{ number_format((float) $income->order_bv, 2) }}</td>
                        <td>{{ $income->formattedIncome() }} <span class="text-secondary">({{ $income->formattedPercentage() }})</span></td>
                        <td>{{ ucfirst($income->income_status) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($levelIncomes->hasPages())
        <div class="cat-pages">{{ $levelIncomes->links() }}</div>
    @endif
@endif
