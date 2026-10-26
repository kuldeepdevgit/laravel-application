<div class="table-responsive">
    <table class="table table-bordered align-middle mb-0 team-table">
        <thead>
            <tr>
                <th scope="col">#</th>
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
            @foreach ($members as $member)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $member->name }}</td>
                    <td>{{ $member->user_name ?: '—' }}</td>
                    <td>{{ $member->phone_number ?: '—' }}</td>
                    <td>—</td>
                    <td>—</td>
                    <td>{{ $member->created_at?->format('M j, Y') ?: '—' }}</td>
                    <td>—</td>
                    <td>—</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
