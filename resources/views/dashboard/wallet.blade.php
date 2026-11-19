<div class="row g-3 mb-3">
    <div class="col-sm-4">
        <article class="stat-card">
            <span>Balance</span>
            <strong>₹{{ number_format((float) $walletBalance, 2) }}</strong>
        </article>
    </div>
    <div class="col-sm-4">
        <article class="stat-card">
            <span>Credit</span>
            <strong>₹{{ number_format((float) $walletCreditTotal, 2) }}</strong>
        </article>
    </div>
    <div class="col-sm-4">
        <article class="stat-card">
            <span>Debit</span>
            <strong>₹{{ number_format((float) $walletDebitTotal, 2) }}</strong>
        </article>
    </div>
</div>

@if ($walletEntries->isEmpty())
    <p class="mb-0 text-secondary">No wallet entries yet.</p>
@else
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0 team-table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Date</th>
                    <th scope="col">Details</th>
                    <th scope="col">Credit</th>
                    <th scope="col">Debit</th>
                    <th scope="col">Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($walletEntries as $entry)
                    <tr>
                        <td>{{ $walletEntries->firstItem() + $loop->index }}</td>
                        <td>{{ $entry->created_at?->format('M j, Y g:i A') ?: '—' }}</td>
                        <td>{{ $entry->description }}</td>
                        <td>{{ $entry->creditAmount() }}</td>
                        <td>{{ $entry->debitAmount() }}</td>
                        <td>{{ $entry->formattedBalance() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($walletEntries->hasPages())
        <div class="cat-pages">{{ $walletEntries->links() }}</div>
    @endif
@endif
