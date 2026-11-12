@extends('admin.layout')

@section('title', 'Show all orders — Admin')

@section('heading', 'Show all orders')

@section('content')
    <div class="cat-page">
        @if (session('status'))
            <p class="cat-note" role="status">{{ session('status') }}</p>
        @endif

        @if ($total === 0)
            <section class="panel cat-empty">
                <span class="cat-empty-mark" aria-hidden="true"><i class="bi bi-bag-check"></i></span>
                <h2>No orders yet</h2>
                <p class="mb-0">Orders appear here after a customer checks out.</p>
            </section>
        @else
            <div class="cat-toolbar">
                <form class="cat-search" method="GET" action="{{ route('admin.orders.index') }}">
                    <label class="visually-hidden" for="q">Search orders</label>
                    <input class="form-control" id="q" name="q" type="search" value="{{ $search }}" placeholder="Search order number, customer, phone, or product" autocomplete="off">
                    <button class="btn dash-btn" type="submit">Search</button>
                </form>
            </div>

            @if ($orders->isEmpty())
                <section class="panel cat-empty">
                    <h2>No matches</h2>
                    <p>Nothing matches “{{ $search }}”.</p>
                    <a class="btn dash-btn-quiet" href="{{ route('admin.orders.index') }}">Clear search</a>
                </section>
            @else
                <section class="panel">
                    <h2>Orders</h2>
                    <p class="text-secondary">Every shop order is listed here, with the products on that order.</p>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 team-table order-table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Order</th>
                                    <th scope="col">Customer</th>
                                    <th scope="col" class="order-wrap">Products</th>
                                    <th scope="col">Total</th>
                                    <th scope="col">Total BV</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        <td>{{ $orders->firstItem() + $loop->index }}</td>
                                        <td>
                                            <strong>{{ $order->number }}</strong>
                                            <div class="text-secondary">{{ $order->created_at?->format('M j, Y g:i A') ?: '—' }}</div>
                                        </td>
                                        <td class="order-wrap">
                                            <div>{{ $order->name }}</div>
                                            <div class="text-secondary">{{ $order->email }}</div>
                                            <div class="text-secondary">{{ $order->phone ?: '—' }}</div>
                                        </td>
                                        <td class="order-wrap">
                                            @if ($order->items->isEmpty())
                                                <span class="text-secondary">No products</span>
                                            @else
                                                <ul class="order-lines">
                                                    @foreach ($order->items as $item)
                                                        <li class="order-line">
                                                            @if ($item->imageUrl())
                                                                <img src="{{ $item->imageUrl() }}" alt="" width="40" height="40">
                                                            @else
                                                                <span class="order-line-fallback" aria-hidden="true"><i class="bi bi-box-seam"></i></span>
                                                            @endif
                                                            <div>
                                                                <p>{{ $item->name }}</p>
                                                                <small>{{ $item->sku }} · Qty {{ $item->qty }} · {{ $item->formattedPrice() }} each · {{ $item->formattedLineTotal() }} · BV {{ $item->formattedBv() }} each · Line BV {{ $item->formattedLineBv() }}</small>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>
                                        <td>{{ $order->formattedTotal() }}</td>
                                        <td>{{ $order->formattedTotalBv() }}</td>
                                        <td><span class="order-status is-{{ $order->status }}">{{ $order->statusLabel() }}</span></td>
                                        <td>@include('admin.orders.status-actions', ['order' => $order, 'details' => true])</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                @if ($orders->hasPages())
                    <div class="cat-pages">{{ $orders->links() }}</div>
                @endif
            @endif
        @endif
    </div>
@endsection
