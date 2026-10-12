@extends('store.layout')

@section('title', 'Cart — Northbay')

@section('content')
    <div class="container page">
        <h1 class="page-title">Your cart</h1>
        @if ($lines->isEmpty())
            <div class="empty-state">
                <i class="bi bi-bag"></i>
                <h2>Your cart is empty</h2>
                <p>Add a kettle, a throw, or whatever the week needs.</p>
                <a class="btn btn-accent" href="{{ route('shop') }}">Browse products</a>
            </div>
        @else
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="panel">
                        @foreach ($lines as $line)
                            <article class="cart-line">
                                <a href="{{ route('products.show', $line['product']['id']) }}">
                                    <img src="{{ $line['product']['images'][0] }}" alt="">
                                </a>
                                <div>
                                    <h2><a href="{{ route('products.show', $line['product']['id']) }}">{{ $line['product']['name'] }}</a></h2>
                                    <p class="fine mb-2">{{ \App\Support\Catalog::money($line['product']['price']) }} each</p>
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <form method="POST" action="{{ route('cart.update', $line['product']['id']) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="qty" value="{{ max(0, $line['qty'] - 1) }}">
                                            <button class="btn btn-ghost" type="submit" aria-label="Decrease quantity">−</button>
                                        </form>
                                        <span class="fw-bold">{{ $line['qty'] }}</span>
                                        <form method="POST" action="{{ route('cart.update', $line['product']['id']) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="qty" value="{{ $line['qty'] + 1 }}">
                                            <button class="btn btn-ghost" type="submit" aria-label="Increase quantity">+</button>
                                        </form>
                                        <form method="POST" action="{{ route('cart.destroy', $line['product']['id']) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-link btn-sm" type="submit">Remove</button>
                                        </form>
                                    </div>
                                </div>
                                <div class="line-total">{{ \App\Support\Catalog::money($line['product']['price'] * $line['qty']) }}</div>
                            </article>
                        @endforeach
                    </div>
                </div>
                <div class="col-lg-4">
                    @include('store.partials.summary', ['mode' => 'cart'])
                </div>
            </div>
        @endif
    </div>
@endsection
