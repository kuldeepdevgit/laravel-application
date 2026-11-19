@extends('admin.layout')

@section('title', 'Franchise Members — Admin')

@section('heading', 'Franchise Members')

@section('content')
    <div class="cat-page">
        @if (session('status'))
            <p class="cat-note" role="status">{{ session('status') }}</p>
        @endif

        @if ($total === 0)
            <section class="panel cat-empty">
                <span class="cat-empty-mark" aria-hidden="true"><i class="bi bi-shop"></i></span>
                <h2>No franchise members yet</h2>
                <p>Make a customer a franchise member from Customer Management. Their user ID can then be used as a franchise sponsor ID.</p>
                <a class="btn dash-btn" href="{{ route('admin.customers.index') }}">Open customers</a>
            </section>
        @else
            <div class="cat-toolbar">
                <form class="cat-search" method="GET" action="{{ route('admin.franchises.index') }}">
                    <label class="visually-hidden" for="q">Search franchise members</label>
                    <input class="form-control" id="q" name="q" type="search" value="{{ $search }}" placeholder="Search name, user name, email, or phone" autocomplete="off">
                    <button class="btn dash-btn" type="submit">Search</button>
                </form>
                <a class="btn dash-btn-quiet" href="{{ route('admin.customers.index') }}">Customer Management</a>
            </div>

            @if ($members->isEmpty())
                <section class="panel cat-empty">
                    <h2>No matches</h2>
                    <p>Nothing matches “{{ $search }}”.</p>
                    <a class="btn dash-btn-quiet" href="{{ route('admin.franchises.index') }}">Clear search</a>
                </section>
            @else
                <section class="panel">
                    <h2>Franchise members</h2>
                    <p class="text-secondary">These accounts can be entered as a franchise sponsor ID. An approved order credits them 10% of the order BV.</p>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 team-table customer-table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Full Name</th>
                                    <th scope="col">User Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Sponsor</th>
                                    <th scope="col">User paid</th>
                                    <th scope="col">Joined</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($members as $member)
                                    <tr>
                                        <td>{{ $members->firstItem() + $loop->index }}</td>
                                        <td>{{ $member->name }}</td>
                                        <td>{{ $member->user_name ?: '—' }}</td>
                                        <td>{{ $member->email }}</td>
                                        <td>{{ $member->phone_number ?: '—' }}</td>
                                        <td>{{ $member->sponsor_name ?: '—' }}</td>
                                        <td>{{ $member->isPaid() ? 'Paid' : 'Unpaid' }}</td>
                                        <td>{{ $member->created_at?->format('M j, Y') ?: '—' }}</td>
                                        <td>
                                            <div class="email-actions">
                                                <a class="btn cat-action" href="{{ route('admin.customers.edit', $member) }}">Edit</a>
                                                <form method="POST" action="{{ route('admin.customers.franchise', $member) }}">
                                                    @csrf
                                                    <button class="btn cat-action" type="submit">Remove franchise</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.customers.login', $member) }}">
                                                    @csrf
                                                    <button class="btn cat-action" type="submit">Auto login</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                @if ($members->hasPages())
                    <div class="cat-pages">{{ $members->links() }}</div>
                @endif
            @endif
        @endif
    </div>
@endsection
