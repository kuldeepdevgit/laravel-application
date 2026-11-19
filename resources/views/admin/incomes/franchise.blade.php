@extends('admin.layout')

@section('title', 'Franchise Income History — Admin')

@section('heading', 'Franchise Income History')

@section('content')
    <div class="cat-page">
        @if ($total === 0)
            <section class="panel cat-empty">
                <span class="cat-empty-mark" aria-hidden="true"><i class="bi bi-percent"></i></span>
                <h2>No franchise income yet</h2>
                <p class="mb-0">Franchise income appears here after an order is approved.</p>
            </section>
        @else
            <div class="cat-toolbar">
                <form class="cat-search" method="GET" action="{{ route('admin.incomes.franchise') }}">
                    <label class="visually-hidden" for="q">Search franchise income</label>
                    <input class="form-control" id="q" name="q" type="search" value="{{ $search }}" placeholder="Search order, customer, or franchise sponsor" autocomplete="off">
                    <button class="btn dash-btn" type="submit">Search</button>
                </form>
            </div>

            @if ($incomes->isEmpty())
                <section class="panel cat-empty">
                    <h2>No matches</h2>
                    <p>Nothing matches “{{ $search }}”.</p>
                    <a class="btn dash-btn-quiet" href="{{ route('admin.incomes.franchise') }}">Clear search</a>
                </section>
            @else
                <section class="panel">
                    <h2>Franchise Income History</h2>
                    <p class="text-secondary">Every franchise credit is listed here. Total credit <strong>₹{{ number_format($totalAmount, 2) }}</strong>.</p>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 team-table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Order</th>
                                    <th scope="col">Customer</th>
                                    <th scope="col">Franchise sponsor</th>
                                    <th scope="col">Order BV</th>
                                    <th scope="col">Credit</th>
                                    <th scope="col">Debit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($incomes as $income)
                                    <tr>
                                        <td>{{ $incomes->firstItem() + $loop->index }}</td>
                                        <td>{{ $income->createdAt ?: '—' }}</td>
                                        <td>
                                            @if ($income->orderId && filled($income->orderNumber))
                                                <a href="{{ route('admin.orders.show', $income->orderId) }}">{{ $income->orderNumber }}</a>
                                            @else
                                                {{ $income->orderNumber ?: '—' }}
                                            @endif
                                        </td>
                                        <td>
                                            {{ $income->customerName ?: '—' }}
                                            @if (filled($income->customerUserName))
                                                <div class="text-secondary">{{ $income->customerUserName }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $income->sponsorName ?: '—' }}
                                            @if (filled($income->sponsorUserName))
                                                <div class="text-secondary">{{ $income->sponsorUserName }}</div>
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
                </section>

                @if ($incomes->hasPages())
                    <div class="cat-pages">{{ $incomes->links() }}</div>
                @endif
            @endif
        @endif
    </div>
@endsection
