<div class="payment-view">
    <dl>
        <div>
            <dt>Franchise Sponsor ID</dt>
            <dd>{{ $payment->franchise_sponsor_id }}</dd>
        </div>
        <div>
            <dt>Amount</dt>
            <dd>{{ $payment->formattedAmount() }}</dd>
        </div>
        <div>
            <dt>Pay mode</dt>
            <dd>{{ $payment->payModeLabel() }}</dd>
        </div>
        <div>
            <dt>Remark</dt>
            <dd>{{ $payment->remark ?: '—' }}</dd>
        </div>
        <div>
            <dt>Uploaded</dt>
            <dd>{{ $payment->created_at?->format('M j, Y g:i A') ?: '—' }}</dd>
        </div>
    </dl>
    @if ($payment->screenshotUrl())
        <figure>
            <a href="{{ $payment->screenshotUrl() }}" target="_blank" rel="noopener">
                <img class="payment-shot" src="{{ $payment->screenshotUrl() }}" alt="Payment screenshot for {{ $number }}">
            </a>
            <figcaption><a href="{{ $payment->screenshotUrl() }}" target="_blank" rel="noopener">Open full size</a></figcaption>
        </figure>
    @endif
</div>
