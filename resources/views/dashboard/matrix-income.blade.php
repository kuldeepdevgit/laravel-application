<div class="row g-2 mb-3">
    <div class="col-6 col-lg-3">
        <article class="stat-card">
            <span>Level 1</span>
            <strong>₹0.00</strong>
            <small>3 seats under you</small>
        </article>
    </div>
    <div class="col-6 col-lg-3">
        <article class="stat-card">
            <span>Level 2</span>
            <strong>₹0.00</strong>
            <small>9 seats, 3 under each</small>
        </article>
    </div>
    <div class="col-6 col-lg-3">
        <article class="stat-card">
            <span>Seats filled</span>
            <strong>0 / 12</strong>
            <small>Open seats are waiting</small>
        </article>
    </div>
    <div class="col-6 col-lg-3">
        <article class="stat-card">
            <span>Total income</span>
            <strong>₹0.00</strong>
            <small>Level 1 + level 2</small>
        </article>
    </div>
</div>

<div class="matrix-plan mb-3">
    <div>
        <span>Level 1</span>
        <strong>3 seats</strong>
        <p>A member placed directly under you. That income is credited to your account.</p>
    </div>
    <div>
        <span>Level 2</span>
        <strong>9 seats</strong>
        <p>Three members under each level 1 seat. That income is credited to your account too.</p>
    </div>
</div>

<div class="matrix-board mb-4" aria-label="3 by 2 matrix">
    <div class="matrix-tree">
        <article class="matrix-node is-you">
            <span>You</span>
            <strong>{{ $user->name }}</strong>
            <small>{{ $user->user_name ?: '—' }}</small>
        </article>

        <div class="matrix-arms">
            @for ($seat = 1; $seat <= 3; $seat++)
                <div class="matrix-arm">
                    <article class="matrix-node is-open">
                        <span>Level 1</span>
                        <strong>Seat {{ $seat }}</strong>
                        <small>Open</small>
                    </article>
                    <div class="matrix-kids">
                        @for ($child = 1; $child <= 3; $child++)
                            <article class="matrix-node is-open is-child">
                                <span>Level 2</span>
                                <strong>{{ $seat }}.{{ $child }}</strong>
                                <small>Open</small>
                            </article>
                        @endfor
                    </div>
                </div>
            @endfor
        </div>
    </div>
</div>

<h3 class="matrix-history-title">Income history</h3>
<p class="mb-3 text-secondary">Credits from level 1 and level 2 will be listed here.</p>
<p class="mb-3">Total <strong>₹0.00</strong></p>
<div class="table-responsive">
    <table class="table table-bordered align-middle mb-0 team-table">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Date</th>
                <th scope="col">Level</th>
                <th scope="col">From</th>
                <th scope="col">Seat</th>
                <th scope="col">Order BV</th>
                <th scope="col">Credit</th>
                <th scope="col">Debit</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="8">No 3x matrix income yet.</td>
            </tr>
        </tbody>
    </table>
</div>
