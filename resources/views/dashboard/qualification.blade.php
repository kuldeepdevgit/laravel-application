<dl class="row mb-4">
    <dt class="col-sm-4">Self BV</dt>
    <dd class="col-sm-8">{{ number_format((float) ($qualification->self_bv ?? 0), 2) }}</dd>
    <dt class="col-sm-4">Direct selling BV</dt>
    <dd class="col-sm-8">{{ number_format((float) ($qualification->direct_bv ?? 0), 2) }}</dd>
    <dt class="col-sm-4">Qualified package</dt>
    <dd class="col-sm-8 mb-0">
        @if ($qualification?->package)
            {{ $qualification->package->package_name }}
            <span class="member-pill is-active">Qualified</span>
            @if ($qualification->qualified_at)
                <span class="text-secondary">{{ $qualification->qualified_at->format('M j, Y') }}</span>
            @endif
        @else
            Not qualified
        @endif
    </dd>
</dl>

@if ($qualificationPackages->isEmpty())
    <p class="mb-0 text-secondary">No active packages yet.</p>
@else
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0 team-table">
            <thead>
                <tr>
                    <th scope="col">Package</th>
                    <th scope="col">Required BV</th>
                    <th scope="col">Direct selling BV</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($qualificationPackages as $package)
                    @php
                        $met = $qualification?->qualifiesFor($package) ?? false;
                    @endphp
                    <tr>
                        <td>{{ $package->package_name }}</td>
                        <td>{{ number_format((float) $package->package_bv, 2) }}</td>
                        <td>{{ number_format($package->directBv(), 2) }}</td>
                        <td>
                            <span class="member-pill {{ $met ? 'is-active' : 'is-inactive' }}">{{ $met ? 'Qualified' : 'Not qualified' }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
