<div class="row g-2 mb-3">
    @foreach ($downlineCounts as $level => $count)
        <div class="col-6 col-md-4 col-lg-2">
            <article class="stat-card">
                <span>Level {{ $level }}</span>
                <strong>{{ $count }}</strong>
            </article>
        </div>
    @endforeach
</div>

<p class="mb-3"><strong>{{ $members->count() }}</strong> {{ $members->count() === 1 ? 'member' : 'members' }} across {{ count($downlineCounts) }} levels.</p>

<div class="table-responsive">
    <table class="table table-bordered align-middle mb-0 team-table">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Level</th>
                <th scope="col">Full Name</th>
                <th scope="col">User Name</th>
                <th scope="col">Phone Number</th>
                <th scope="col">Self Investment</th>
                <th scope="col">Status</th>
                <th scope="col">Join Date</th>
                <th scope="col">Activation Date</th>
                <th scope="col">Self+Team RV</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($members as $member)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $member->downline_level }}</td>
                    <td>{{ $member->name }}</td>
                    <td>{{ $member->user_name ?: '—' }}</td>
                    <td>{{ $member->phone_number ?: '—' }}</td>
                    <td>—</td>
                    <td>—</td>
                    <td>{{ $member->created_at?->format('M j, Y') ?: '—' }}</td>
                    <td>—</td>
                    <td>—</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">No members in your downline.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
