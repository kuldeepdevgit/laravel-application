<p class="mb-3">Total <strong>₹{{ number_format((float) $franchiseIncomeTotal, 2) }}</strong></p>

@if ($franchiseIncomes->isEmpty())
    <p class="mb-0 text-secondary">No franchise income history yet.</p>
@else
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0 team-table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Date</th>
                    <th scope="col">Order</th>
                    <th scope="col">Customer</th>
                    <th scope="col">Order BV</th>
                    <th scope="col">Credit</th>
                    <th scope="col">Debit</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($franchiseIncomes as $income)
                    <tr>
                        <td>{{ $franchiseIncomes->firstItem() + $loop->index }}</td>
                        <td>{{ $income->createdAt ?: '—' }}</td>
                        <td>{{ $income->orderNumber ?: '—' }}</td>
                        <td>
                            {{ $income->customerName ?: '—' }}
                            @if (filled($income->customerUserName))
                                <div class="text-secondary">{{ $income->customerUserName }}</div>
                            @endif
                        </td>
                        <td>{{ $income->orderBv }}</td>
                        <td>{{ $income->amount }} <span class="text-secondary">({{ $income->percent }})</span></td>
                        <td>—</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($franchiseIncomes->hasPages())
        <div class="cat-pages">{{ $franchiseIncomes->links() }}</div>
    @endif
@endif
