@extends('admin.layout')

@section('title', 'Update Fund Address — Admin')

@section('heading', 'Update Fund Address')

@section('content')
    <div class="cat-page">
        @if (session('status'))
            <p class="cat-note" role="status">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('admin.funds.address.update') }}">
            @csrf
            @method('PUT')

            <section class="panel">
                <h2>Fund address</h2>
                <p class="text-secondary">The USDT BEP 20 address members use when they send a fund request.</p>

                <div class="mb-4">
                    <label class="form-label" for="usdt_bep20">USDT BEP 20</label>
                    <input class="form-control" id="usdt_bep20" name="usdt_bep20" type="text" value="{{ old('usdt_bep20') }}" maxlength="255" autocomplete="off" spellcheck="false" placeholder="0x…">
                </div>

                <button class="btn dash-btn" type="submit">Submit</button>
            </section>
        </form>
    </div>
@endsection
