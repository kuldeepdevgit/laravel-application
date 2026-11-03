@extends('admin.layout')

@section('title', 'Customer Management — Admin')

@section('heading', 'Customer Management')

@section('content')
    <div class="cat-page">
        @if (session('status'))
            <p class="cat-note" role="status">{{ session('status') }}</p>
        @endif

        @if ($total === 0)
            <section class="panel cat-empty">
                <span class="cat-empty-mark" aria-hidden="true"><i class="bi bi-people"></i></span>
                <h2>No customers yet</h2>
                <p>Accounts appear here after someone registers.</p>
            </section>
        @else
            <div class="cat-toolbar">
                <form class="cat-search" method="GET" action="{{ route('admin.customers.index') }}">
                    <label class="visually-hidden" for="q">Search customers</label>
                    <input class="form-control" id="q" name="q" type="search" value="{{ $search }}" placeholder="Search name, user name, email, or phone" autocomplete="off">
                    <button class="btn dash-btn" type="submit">Search</button>
                </form>
            </div>

            @if ($customers->isEmpty())
                <section class="panel cat-empty">
                    <h2>No matches</h2>
                    <p>Nothing matches “{{ $search }}”.</p>
                    <a class="btn dash-btn-quiet" href="{{ route('admin.customers.index') }}">Clear search</a>
                </section>
            @else
                <section class="panel">
                    <h2>Customers</h2>
                    <p class="text-secondary">Every account is listed here. Edit a customer, or sign in as that customer.</p>

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
                                    <th scope="col">Position</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Joined</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($customers as $customer)
                                    <tr>
                                        <td>{{ $customers->firstItem() + $loop->index }}</td>
                                        <td>{{ $customer->name }}</td>
                                        <td>{{ $customer->user_name ?: '—' }}</td>
                                        <td>{{ $customer->email }}</td>
                                        <td>{{ $customer->phone_number ?: '—' }}</td>
                                        <td>{{ $customer->sponsor_name ?: '—' }}</td>
                                        <td>{{ $customer->position ? ucfirst($customer->position) : '—' }}</td>
                                        <td>
                                            {{ match ($customer->user_role) {
                                                'super_admin' => 'Super Admin',
                                                'admin' => 'Admin',
                                                default => 'Customer',
                                            } }}
                                        </td>
                                        <td>{{ $customer->created_at?->format('M j, Y') ?: '—' }}</td>
                                        <td>
                                            <div class="email-actions">
                                                <a class="btn cat-action" href="{{ route('admin.customers.edit', $customer) }}">Edit</a>
                                                @unless ($customer->isSuperAdmin())
                                                    <form method="POST" action="{{ route('admin.customers.login', $customer) }}">
                                                        @csrf
                                                        <button class="btn cat-action" type="submit">Auto login</button>
                                                    </form>
                                                @endunless
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                @if ($customers->hasPages())
                    <div class="cat-pages">{{ $customers->links() }}</div>
                @endif
            @endif
        @endif
    </div>
@endsection
