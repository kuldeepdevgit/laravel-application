@extends('admin.layout')

@section('title', 'Fund Request List — Admin')

@section('heading', 'Fund Request List')

@section('content')
    <div class="cat-page">
        <div class="cat-toolbar">
            <form class="cat-search" method="GET" action="{{ route('admin.funds.requests') }}">
                <label class="visually-hidden" for="q">Search fund requests</label>
                <input class="form-control" id="q" name="q" type="search" value="{{ $search }}" placeholder="Search member, user name, or wallet" autocomplete="off">
                <button class="btn dash-btn" type="submit">Search</button>
            </form>
        </div>

        <section class="panel">
            <h2>Fund requests</h2>
            <p class="text-secondary">Member USDT fund requests will be listed here.</p>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 team-table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Member</th>
                            <th scope="col">User Name</th>
                            <th scope="col">Amount (USDT)</th>
                            <th scope="col">Wallet Address</th>
                            <th scope="col">Status</th>
                            <th scope="col">Requested</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="text-center text-secondary py-4">
                                @if ($search !== '')
                                    Nothing matches “{{ $search }}”.
                                @else
                                    No fund requests yet
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
