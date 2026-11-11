@if ($orderTotal === 0)
    <section class="panel cat-empty">
        <span class="cat-empty-mark" aria-hidden="true"><i class="bi bi-bag-check"></i></span>
        <h2>No orders yet</h2>
        <p class="mb-0">Orders appear here after you check out.</p>
    </section>
@else
    <div class="cat-toolbar">
        <form class="cat-search" method="GET" action="{{ route('dashboard.section', 'order-history') }}">
            <label class="visually-hidden" for="q">Search your orders</label>
            <input class="form-control" id="q" name="q" type="search" value="{{ $orderSearch }}" placeholder="Search order number or product" autocomplete="off">
            <button class="btn dash-btn" type="submit">Search</button>
        </form>
    </div>

    @if ($orders->isEmpty())
        <section class="panel cat-empty">
            <h2>No matches</h2>
            <p>Nothing matches “{{ $orderSearch }}”.</p>
            <a class="btn dash-btn-quiet" href="{{ route('dashboard.section', 'order-history') }}">Clear search</a>
        </section>
    @else
        <section class="panel">
            <h2>Order History</h2>
            <p class="text-secondary">{{ $intro }}</p>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 team-table order-table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Order</th>
                            <th scope="col" class="order-wrap">Products</th>
                            <th scope="col">Total</th>
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
                                                        <small>{{ $item->sku }} · Qty {{ $item->qty }} · {{ $item->formattedPrice() }} each · {{ $item->formattedLineTotal() }}</small>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td>{{ $order->formattedTotal() }}</td>
                                <td>
                                    <a class="btn cat-action" href="{{ route('dashboard.orders.show', $order) }}">Details</a>
                                </td>
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
