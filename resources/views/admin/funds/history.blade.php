@extends('admin.layout')

@section('title', 'Fund Transfer History — Admin')

@section('heading', 'Fund Transfer History')

@section('content')
    <div class="cat-page">
        <div class="cat-toolbar">
            <form class="cat-search" method="GET" action="{{ route('admin.funds.history') }}">
                <label class="visually-hidden" for="q">Search fund transfers</label>
                <input class="form-control" id="q" name="q" type="search" value="{{ $search }}" placeholder="Search member or user name" autocomplete="off">
                <button class="btn dash-btn" type="submit">Search</button>
            </form>
        </div>

        <section class="panel">
            <h2>Transfers</h2>
            <p class="text-secondary">Funds sent from this desk will be listed here.</p>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 team-table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Member</th>
                            <th scope="col">User Name</th>
                            <th scope="col">Amount (USDT)</th>
                            <th scope="col">Remark</th>
                            <th scope="col">Sent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">
                                @if ($search !== '')
                                    Nothing matches “{{ $search }}”.
                                @else
                                    No transfers yet
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
