@extends('admin.layout')

@section('title', $order->number.' — Admin')

@section('heading', $order->number)

@section('content')
    <div class="cat-page">
        <div class="cat-toolbar">
            <a class="btn dash-btn-quiet" href="{{ route('admin.orders.index') }}">Show all orders</a>
            <span class="order-status is-{{ $order->status }}">{{ $order->statusLabel() }}</span>
            @include('admin.orders.status-actions', ['order' => $order])
        </div>
        @if (session('status'))
            <p class="cat-note" role="status">{{ session('status') }}</p>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-lg-4">
                <section class="panel h-100">
                    <h2>Customer</h2>
                    <p class="mb-1"><strong>{{ $order->name }}</strong></p>
                    <p class="mb-1">{{ $order->email }}</p>
                    <p class="mb-1">{{ $order->phone ?: 'No phone' }}</p>
                    <p class="mb-0 text-secondary">
                        @if ($order->user)
                            Account {{ $order->user->user_name ?: $order->user->name }}
                        @else
                            Guest checkout
                        @endif
                    </p>
                </section>
            </div>
            <div class="col-lg-4">
                <section class="panel h-100">
                    <h2>Ship to</h2>
                    <p class="mb-1">{{ $order->address }}</p>
                    <p class="mb-1">{{ $order->city }}, {{ $order->region }} {{ $order->postal }}</p>
                    <p class="mb-0">{{ $order->country }}</p>
                </section>
            </div>
            <div class="col-lg-4">
                <section class="panel h-100">
                    <h2>Payment</h2>
                    <p class="mb-1">{{ $order->paymentLabel() }}</p>
                    <p class="mb-1">Promo {{ $order->promo ?: '—' }}</p>
                    <p class="mb-3 text-secondary">Placed {{ $order->created_at?->format('M j, Y g:i A') ?: '—' }}</p>
                    <dl class="order-totals mb-0">
                        <div>
                            <dt>Subtotal</dt>
                            <dd>{{ $order->formattedSubtotal() }}</dd>
                        </div>
                        <div>
                            <dt>Discount</dt>
                            <dd>{{ $order->formattedDiscount() }}</dd>
                        </div>
                        <div>
                            <dt>Shipping</dt>
                            <dd>{{ $order->formattedShipping() }}</dd>
                        </div>
                        <div>
                            <dt>Total</dt>
                            <dd>{{ $order->formattedTotal() }}</dd>
                        </div>
                        <div>
                            <dt>Total BV</dt>
                            <dd>{{ $order->formattedTotalBv() }}</dd>
                        </div>
                    </dl>
                </section>
            </div>
        </div>

        <section class="panel">
            <h2>Products</h2>
            <p class="text-secondary">These are the products saved on this order.</p>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 team-table order-table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Product</th>
                            <th scope="col">SKU</th>
                            <th scope="col">Category</th>
                            <th scope="col">Qty</th>
                            <th scope="col">Unit price</th>
                            <th scope="col">Line total</th>
                            <th scope="col">Unit BV</th>
                            <th scope="col">Line BV</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($order->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="order-wrap">
                                    <div class="order-line">
                                        @if ($item->imageUrl())
                                            <img src="{{ $item->imageUrl() }}" alt="" width="40" height="40">
                                        @else
                                            <span class="order-line-fallback" aria-hidden="true"><i class="bi bi-box-seam"></i></span>
                                        @endif
                                        <div>
                                            <p>{{ $item->name }}</p>
                                            @if (filled($item->product?->description))
                                                <small>{{ $item->product->description }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $item->sku }}</td>
                                <td>{{ $item->product?->category?->name ?? '—' }}</td>
                                <td>{{ $item->qty }}</td>
                                <td>{{ $item->formattedPrice() }}</td>
                                <td>{{ $item->formattedLineTotal() }}</td>
                                <td>{{ $item->formattedBv() }}</td>
                                <td>{{ $item->formattedLineBv() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-secondary py-4">No products on this order</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
