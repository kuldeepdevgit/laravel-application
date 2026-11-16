@extends('store.layout')

@section('title', ($order ? 'Order placed' : 'Checkout').' — Northbay')

@section('content')
    <div class="container page">
        @unless ($order)
            <h1 class="page-title">Checkout</h1>
        @endunless

        @if ($order)
            <section class="receipt" aria-labelledby="order-placed-title">
                <header class="receipt-banner">
                    <span class="receipt-mark" aria-hidden="true"><i class="bi bi-check-lg"></i></span>
                    <div class="receipt-intro">
                        <p class="receipt-kicker">Order placed</p>
                        <h1 id="order-placed-title">Thank you{{ $placedOrder ? ', '.$placedOrder->name : '' }}</h1>
                        <p>We saved this order. Payment is cash, and nothing is charged online.</p>
                    </div>
                    <div class="receipt-id">
                        <span class="receipt-label">Order number</span>
                        <strong>{{ $placedOrder->number ?? $order }}</strong>
                        @if ($placedOrder)
                            <span class="receipt-status is-{{ $placedOrder->status ?: 'pending' }}">{{ $placedOrder->statusLabel() }}</span>
                        @endif
                    </div>
                </header>

                @if ($placedOrder)
                    <div class="receipt-body">
                        <div class="receipt-facts">
                            <article>
                                <h2>Customer</h2>
                                <p>{{ $placedOrder->name }}</p>
                                <p><a href="mailto:{{ $placedOrder->email }}">{{ $placedOrder->email }}</a></p>
                                @if (filled($placedOrder->phone))
                                    <p>{{ $placedOrder->phone }}</p>
                                @endif
                            </article>
                            <article>
                                <h2>Ship to</h2>
                                <p>{{ $placedOrder->address }}</p>
                                <p>{{ collect([$placedOrder->city, $placedOrder->region, $placedOrder->postal])->filter()->implode(', ') }}</p>
                                @if (filled($placedOrder->country))
                                    <p>{{ $placedOrder->country }}</p>
                                @endif
                            </article>
                            <article>
                                <h2>Payment</h2>
                                <p>{{ $placedOrder->paymentLabel() }}</p>
                                <p class="receipt-note">Pay on delivery.</p>
                            </article>
                        </div>

                        <div class="receipt-lines">
                            <h2>Products</h2>
                            @foreach ($placedOrder->items as $item)
                                <article class="receipt-line">
                                    @if ($item->imageUrl())
                                        <img src="{{ $item->imageUrl() }}" alt="">
                                    @else
                                        <span class="receipt-thumb" aria-hidden="true"><i class="bi bi-box-seam"></i></span>
                                    @endif
                                    <div>
                                        <strong>{{ $item->name }}</strong>
                                        <span>
                                            Qty {{ $item->qty }}
                                            @if (filled($item->sku))
                                                · {{ $item->sku }}
                                            @endif
                                            · {{ $item->formattedPrice() }} each
                                        </span>
                                        <span>BV {{ $item->formattedLineBv() }}</span>
                                    </div>
                                    <strong>{{ $item->formattedLineTotal() }}</strong>
                                </article>
                            @endforeach
                        </div>

                        <dl class="receipt-totals">
                            <div>
                                <dt>Subtotal</dt>
                                <dd>{{ $placedOrder->formattedSubtotal() }}</dd>
                            </div>
                            @if ((float) $placedOrder->discount > 0)
                                <div>
                                    <dt>Discount{{ filled($placedOrder->promo) ? ' ('.$placedOrder->promo.')' : '' }}</dt>
                                    <dd>−{{ $placedOrder->formattedDiscount() }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt>Shipping</dt>
                                <dd>{{ (float) $placedOrder->shipping > 0 ? $placedOrder->formattedShipping() : 'Free' }}</dd>
                            </div>
                            <div class="is-total">
                                <dt>Total</dt>
                                <dd>{{ $placedOrder->formattedTotal() }}</dd>
                            </div>
                            <div class="is-bv">
                                <dt>Total BV</dt>
                                <dd>{{ $placedOrder->formattedTotalBv() }}</dd>
                            </div>
                        </dl>
                    </div>
                @endif

                <div class="receipt-actions">
                    @auth
                        <a class="btn btn-accent" href="{{ route('dashboard.section', 'order-history') }}">
                            <i class="bi bi-clock-history" aria-hidden="true"></i> Order history
                        </a>
                    @endauth
                    <a class="btn btn-ghost" href="{{ route('home') }}">
                        <i class="bi bi-shop" aria-hidden="true"></i> Back to the shop
                    </a>
                </div>
            </section>
        @elseif ($lines->isEmpty())
            <div class="empty-state">
                <i class="bi bi-bag"></i>
                <h2>Nothing to check out</h2>
                <p>Your cart is empty.</p>
                <a class="btn btn-accent" href="{{ route('shop') }}">Browse products</a>
            </div>
        @else
            <div class="row g-4">
                <div class="col-lg-7">
                    <form id="checkout-form" method="POST" action="{{ route('checkout.store') }}" novalidate>
                        @csrf
                        <section class="panel">
                            <h2 class="h4 mb-3" style="font-family: Fraunces, Georgia, serif;">Shipping</h2>
                            <div class="row g-3">
                                @foreach ([
                                    'name' => ['Full name', 'text', 'name', 'col-md-6'],
                                    'email' => ['Email', 'email', 'email', 'col-md-6'],
                                    'address' => ['Street address', 'text', 'address-line1', 'col-12'],
                                    'city' => ['City', 'text', 'address-level2', 'col-md-6'],
                                    'region' => ['State', 'text', 'address-level1', 'col-md-3'],
                                    'postal' => ['Postal code', 'text', 'postal-code', 'col-md-3'],
                                ] as $field => [$label, $type, $autocomplete, $column])
                                    <div class="{{ $column }}">
                                        <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                                        <input class="form-control @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field, $details[$field] ?? '') }}" autocomplete="{{ $autocomplete }}" required>
                                        @error($field)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                                <div class="col-md-6">
                                    <label class="form-label" for="country">Country</label>
                                    <select class="form-select @error('country') is-invalid @enderror" id="country" name="country" autocomplete="country-name" required>
                                        <option value="">Choose</option>
                                        @foreach (['India', 'United States', 'Canada', 'United Kingdom'] as $country)
                                            <option value="{{ $country }}" @selected(old('country', $details['country'] ?? '') === $country)>{{ $country }}</option>
                                        @endforeach
                                    </select>
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="phone">Phone <span class="optional">(optional)</span></label>
                                    <input class="form-control" id="phone" name="phone" type="tel" value="{{ old('phone', $details['phone'] ?? '') }}" autocomplete="tel">
                                </div>
                            </div>
                        </section>
                        <section class="panel mt-3">
                            <fieldset class="pay-box">
                                <legend>Payment</legend>
                                <label class="pay-option"><input type="radio" name="pay" id="pay-cash" value="cash" checked> Cash</label>
                                @error('pay')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </fieldset>
                        </section>
                    </form>
                </div>
                <div class="col-lg-5">
                    @include('store.partials.summary', ['mode' => 'checkout'])
                </div>
            </div>
        @endif
    </div>
@endsection
