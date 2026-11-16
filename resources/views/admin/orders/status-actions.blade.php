<div class="email-actions">
    @if ($details ?? false)
        <button
            class="btn cat-action @if ($order->payment) is-ready @endif"
            type="button"
            data-bs-toggle="modal"
            data-bs-target="#view-payment"
            data-number="{{ $order->number }}"
            data-sponsor="{{ $order->payment?->franchise_sponsor_id }}"
            data-amount="{{ $order->payment?->formattedAmount() }}"
            data-mode="{{ $order->payment?->payModeLabel() }}"
            data-remark="{{ $order->payment?->remark }}"
            data-when="{{ $order->payment?->created_at?->format('M j, Y g:i A') }}"
            data-screenshot="{{ $order->payment?->screenshotUrl() }}"
        >View payment</button>
        <a class="btn cat-action" href="{{ route('admin.orders.show', $order) }}">Details</a>
    @endif
    @foreach (['confirmed' => 'Confirm', 'pending' => 'Pending', 'rejected' => 'Rejected'] as $value => $label)
        <form method="POST" action="{{ route('admin.orders.status', $order) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="{{ $value }}">
            <button class="btn cat-action @if ($order->status === $value) is-current @endif" type="submit" @if ($order->status === $value) aria-current="true" @endif>{{ $label }}</button>
        </form>
    @endforeach
</div>
