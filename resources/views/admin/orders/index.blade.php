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

                <div class="modal fade" id="view-payment" tabindex="-1" aria-labelledby="view-payment-title" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content cat-modal">
                            <div class="modal-header">
                                <div>
                                    <h2 class="modal-title" id="view-payment-title">Uploaded payment</h2>
                                    <p class="text-secondary mb-0" id="view-payment-order"></p>
                                </div>
                                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0 text-secondary" id="view-payment-empty" hidden>No payment uploaded for this order.</p>
                                <div class="payment-view" id="view-payment-body" hidden>
                                    <dl>
                                        <div>
                                            <dt>Franchise Sponsor ID</dt>
                                            <dd id="view-payment-sponsor">—</dd>
                                        </div>
                                        <div>
                                            <dt>Amount</dt>
                                            <dd id="view-payment-amount">—</dd>
                                        </div>
                                        <div>
                                            <dt>Pay mode</dt>
                                            <dd id="view-payment-mode">—</dd>
                                        </div>
                                        <div>
                                            <dt>Remark</dt>
                                            <dd id="view-payment-remark">—</dd>
                                        </div>
                                        <div>
                                            <dt>Uploaded</dt>
                                            <dd id="view-payment-when">—</dd>
                                        </div>
                                    </dl>
                                    <figure id="view-payment-figure" hidden>
                                        <a id="view-payment-open" href="#" target="_blank" rel="noopener">
                                            <img class="payment-shot" id="view-payment-shot" alt="">
                                        </a>
                                        <figcaption><a id="view-payment-open-text" href="#" target="_blank" rel="noopener">Open full size</a></figcaption>
                                    </figure>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @push('scripts')
                    <script>
                        document.querySelectorAll('[data-bs-target="#view-payment"]').forEach(function (button) {
                            button.addEventListener('click', function () {
                                var shot = button.getAttribute('data-screenshot') || '';
                                var sponsor = button.getAttribute('data-sponsor') || '';
                                var hasPayment = sponsor !== '' || shot !== '';

                                document.getElementById('view-payment-order').textContent = button.getAttribute('data-number') || '';
                                document.getElementById('view-payment-empty').hidden = hasPayment;
                                document.getElementById('view-payment-body').hidden = !hasPayment;
                                document.getElementById('view-payment-sponsor').textContent = sponsor || '—';
                                document.getElementById('view-payment-amount').textContent = button.getAttribute('data-amount') || '—';
                                document.getElementById('view-payment-mode').textContent = button.getAttribute('data-mode') || '—';
                                document.getElementById('view-payment-remark').textContent = button.getAttribute('data-remark') || '—';
                                document.getElementById('view-payment-when').textContent = button.getAttribute('data-when') || '—';

                                var figure = document.getElementById('view-payment-figure');
                                var image = document.getElementById('view-payment-shot');
                                var open = document.getElementById('view-payment-open');
                                var openText = document.getElementById('view-payment-open-text');
                                figure.hidden = shot === '';
                                if (shot === '') {
                                    image.removeAttribute('src');
                                    open.removeAttribute('href');
                                    openText.removeAttribute('href');
                                } else {
                                    image.src = shot;
                                    image.alt = 'Payment screenshot for ' + (button.getAttribute('data-number') || 'this order');
                                    open.href = shot;
                                    openText.href = shot;
                                }
                            });
                        });
                    </script>
                @endpush
            @endif
        @endif
    </div>
@endsection
