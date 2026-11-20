<p class="mb-3">Total <strong>₹{{ number_format((float) $levelIncomeTotal, 2) }}</strong></p>

@if ($levelIncomes->isEmpty())
    <p class="mb-0 text-secondary">No level income history yet.</p>
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
                        <td>{{ $income->level }}</td>
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
