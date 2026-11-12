<div class="email-actions">
    @if ($details ?? false)
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
