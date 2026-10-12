@extends('store.layout')

@section('title', 'Checkout — Northbay')

@section('content')
    <div class="container page">
        <h1 class="page-title">Checkout</h1>

        @if ($order)
            <section class="confirm">
                <i class="bi bi-check-circle"></i>
                <h2>Order placed</h2>
                <p>Your demo order number is <strong>{{ $order }}</strong>.</p>
                <p>No payment was processed and nothing will be delivered.</p>
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
                                        @foreach (['United States', 'Canada', 'United Kingdom'] as $country)
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
                                <label class="pay-option"><input type="radio" name="pay" id="pay-card" value="card" @checked(old('pay', 'card') === 'card')> Card</label>
                                <label class="pay-option"><input type="radio" name="pay" id="pay-delivery" value="delivery" @checked(old('pay') === 'delivery')> Pay on delivery</label>
                                <div id="card-fields" class="row g-3 mt-1" @if (old('pay') === 'delivery') hidden @endif>
                                    <div class="col-12">
                                        <label class="form-label" for="card-name">Name on card</label>
                                        <input class="form-control @error('card_name') is-invalid @enderror" id="card-name" name="card_name" value="{{ old('card_name') }}" autocomplete="cc-name" @required(old('pay', 'card') !== 'delivery')>
                                        @error('card_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="card-number">Card number</label>
                                        <input class="form-control @error('card_number') is-invalid @enderror" id="card-number" name="card_number" inputmode="numeric" value="{{ old('card_number') }}" autocomplete="cc-number" placeholder="1234 5678 9012 3456" maxlength="19" @required(old('pay', 'card') !== 'delivery')>
                                        @error('card_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="card-exp">Expiry</label>
                                        <input class="form-control @error('card_exp') is-invalid @enderror" id="card-exp" name="card_exp" value="{{ old('card_exp') }}" autocomplete="cc-exp" placeholder="MM/YY" maxlength="7" @required(old('pay', 'card') !== 'delivery')>
                                        @error('card_exp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="card-cvc">Security code</label>
                                        <input class="form-control @error('card_cvc') is-invalid @enderror" id="card-cvc" name="card_cvc" value="{{ old('card_cvc') }}" autocomplete="cc-csc" maxlength="4" inputmode="numeric" @required(old('pay', 'card') !== 'delivery')>
                                        @error('card_cvc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
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
