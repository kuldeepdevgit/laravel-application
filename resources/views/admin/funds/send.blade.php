@extends('admin.layout')

@section('title', 'Send Fund — Admin')

@section('heading', 'Send Fund')

@section('content')
    <div class="cat-page">
        @if (session('status'))
            <p class="cat-note" role="status">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('admin.funds.send.store') }}">
            @csrf

            <section class="panel">
                <h2>Send fund</h2>
                <p class="text-secondary">Send a USDT amount to a member account.</p>

                <div class="mb-3">
                    <label class="form-label" for="user_name">User name</label>
                    <input class="form-control" id="user_name" name="user_name" type="text" value="{{ old('user_name') }}" maxlength="255" autocomplete="off" placeholder="Member user name">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="amount">Amount (USDT)</label>
                    <input class="form-control" id="amount" name="amount" type="number" value="{{ old('amount') }}" min="0" step="0.01" placeholder="0.00">
                </div>

                <div class="mb-4">
                    <label class="form-label" for="remark">Remark</label>
                    <textarea class="form-control" id="remark" name="remark" rows="3" maxlength="500" placeholder="Optional note">{{ old('remark') }}</textarea>
                </div>

                <button class="btn dash-btn" type="submit">Send fund</button>
            </section>
        </form>
    </div>
@endsection
