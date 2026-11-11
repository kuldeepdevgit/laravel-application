@extends('store.layout')

@section('title', 'Checkout — Northbay')

@section('content')
    <div class="container page">
        <h1 class="page-title">Checkout</h1>

        @if ($order)
            <section class="confirm">
                <i class="bi bi-check-circle"></i>
                <h2>Order placed</h2>
                <p>Your order number is <strong>{{ $order }}</strong>.</p>
                <p>We saved the order in this shop. Payment is cash, and nothing is charged online.</p>
                <a class="btn btn-accent" href="{{ route('home') }}">Back to the shop</a>
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
                                        <input class="form-control @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field) }}" autocomplete="{{ $autocomplete }}" required>
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
                                            <option value="{{ $country }}" @selected(old('country') === $country)>{{ $country }}</option>
                                        @endforeach
                                    </select>
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="phone">Phone <span class="optional">(optional)</span></label>
                                    <input class="form-control" id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel">
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
