@extends('admin.layout')

@section('title', 'Auto Pool Management — Admin')

@section('heading', 'Auto Pool Management')

@section('content')
    <div class="cat-page">
        @if ($total === 0 && $search === '' && $pool === '')
            <section class="panel cat-empty">
                <span class="cat-empty-mark" aria-hidden="true"><i class="bi bi-diagram-3"></i></span>
                <h2>No auto pool seats yet</h2>
                <p class="mb-0">A member appears here after they are placed in a silver, gold, or diamond pool.</p>
            </section>
        @else
            <div class="cat-toolbar">
                <form class="cat-search" method="GET" action="{{ route('admin.auto-pool.index') }}">
                    <label class="visually-hidden" for="q">Search auto pool</label>
                    <input class="form-control" id="q" name="q" type="search" value="{{ $search }}" placeholder="Search member, user ID, or pool" autocomplete="off">
                    <label class="visually-hidden" for="pool">Pool</label>
                    <select class="form-select" id="pool" name="pool">
                        <option value="" @selected($pool === '')>All pools</option>
                        <option value="silver" @selected($pool === 'silver')>Silver</option>
                        <option value="gold" @selected($pool === 'gold')>Gold</option>
                        <option value="diamond" @selected($pool === 'diamond')>Diamond</option>
                    </select>
                    <button class="btn dash-btn" type="submit">Search</button>
                </form>
            </div>

            @if ($placements->isEmpty())
                <section class="panel cat-empty">
                    <h2>No matches</h2>
                    <p>Nothing matches this search.</p>
                    <a class="btn dash-btn-quiet" href="{{ route('admin.auto-pool.index') }}">Clear search</a>
                </section>
            @else
                <section class="panel">
                    <h2>Auto Pool Management</h2>
                    <p class="text-secondary">Seats in the silver, gold, and diamond pools. Position is 1 or 2. Total <strong>{{ number_format($total) }}</strong>.</p>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 team-table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Member</th>
                                    <th scope="col">Pool</th>
                                    <th scope="col">Position</th>
                                    <th scope="col">Placed under</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($placements as $placement)
                                    <tr>
                                        <td>{{ $placements->firstItem() + $loop->index }}</td>
                                        <td>{{ $placement->created_at?->format('M j, Y') ?: '—' }}</td>
                                        <td>
                                            {{ $placement->user?->name ?: '—' }}
                                            @if (filled($placement->user?->user_name))
                                                <div class="text-secondary">{{ $placement->user->user_name }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $placement->poolLabel() }}</td>
                                        <td>{{ $placement->position }}</td>
                                        <td>
                                            {{ $placement->tree?->name ?: '—' }}
                                            @if (filled($placement->tree?->user_name))
                                                <div class="text-secondary">{{ $placement->tree->user_name }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                @if ($placements->hasPages())
                    <div class="cat-pages">{{ $placements->links() }}</div>
                @endif
            @endif
        @endif
    </div>
@endsection
