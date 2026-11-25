@extends('admin.layout')

@section('title', 'Package Qualification History — Admin')

@section('heading', 'Package Qualification History')

@section('content')
    <div class="cat-page">
        @if ($total === 0)
            <section class="panel cat-empty">
                <span class="cat-empty-mark" aria-hidden="true"><i class="bi bi-clock-history"></i></span>
                <h2>No package qualification yet</h2>
                <p class="mb-0">A row appears here when an admin approves an order.</p>
            </section>
        @else
            <div class="cat-toolbar">
                <form class="cat-search" method="GET" action="{{ route('admin.packages.qualifications') }}">
                    <label class="visually-hidden" for="q">Search package qualification</label>
                    <input class="form-control" id="q" name="q" type="search" value="{{ $search }}" placeholder="Search member, user ID, or package" autocomplete="off">
                    <button class="btn dash-btn" type="submit">Search</button>
                </form>
            </div>

            @if ($qualifications->isEmpty())
                <section class="panel cat-empty">
                    <h2>No matches</h2>
                    <p>Nothing matches “{{ $search }}”.</p>
                    <a class="btn dash-btn-quiet" href="{{ route('admin.packages.qualifications') }}">Clear search</a>
                </section>
            @else
                <section class="panel">
                    <h2>Package Qualification History</h2>
                    <p class="text-secondary">Every saved qualification is listed here. Total <strong>{{ number_format($total) }}</strong>.</p>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 team-table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Member</th>
                                    <th scope="col">Package</th>
                                    <th scope="col">Self BV</th>
                                    <th scope="col">Direct selling BV</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($qualifications as $qualification)
                                    <tr>
                                        <td>{{ $qualifications->firstItem() + $loop->index }}</td>
                                        <td>{{ ($qualification->qualified_at ?? $qualification->updated_at)?->format('M j, Y') ?: '—' }}</td>
                                        <td>
                                            {{ $qualification->user?->name ?: '—' }}
                                            @if (filled($qualification->user?->user_name))
                                                <div class="text-secondary">{{ $qualification->user->user_name }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $qualification->package?->package_name ?: '—' }}</td>
                                        <td>{{ number_format((float) $qualification->self_bv, 2) }}</td>
                                        <td>{{ number_format((float) $qualification->direct_bv, 2) }}</td>
                                        <td>
                                            <span class="member-pill {{ $qualification->is_qualified ? 'is-active' : 'is-inactive' }}">{{ $qualification->is_qualified ? 'Qualified' : 'Not qualified' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                @if ($qualifications->hasPages())
                    <div class="cat-pages">{{ $qualifications->links() }}</div>
                @endif
            @endif
        @endif
    </div>
@endsection
