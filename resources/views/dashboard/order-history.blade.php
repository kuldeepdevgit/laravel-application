@if (session('status'))
    <p class="cat-note" role="status">{{ session('status') }}</p>
@endif

@if ($orderTotal === 0)
    <section class="panel cat-empty">
        <span class="cat-empty-mark" aria-hidden="true"><i class="bi bi-bag-check"></i></span>
        <h2>No orders yet</h2>
        <p class="mb-0">Orders appear here after you check out.</p>
    </section>
@else
    <div class="cat-toolbar">
        <form class="cat-search" method="GET" action="{{ route('dashboard.section', 'order-history') }}">
            <label class="visually-hidden" for="q">Search your orders</label>
            <input class="form-control" id="q" name="q" type="search" value="{{ $orderSearch }}" placeholder="Search order number or product" autocomplete="off">
            <button class="btn dash-btn" type="submit">Search</button>
        </form>
    </div>

    @if ($orders->isEmpty())
        <section class="panel cat-empty">
            <h2>No matches</h2>
            <p>Nothing matches “{{ $orderSearch }}”.</p>
            <a class="btn dash-btn-quiet" href="{{ route('dashboard.section', 'order-history') }}">Clear search</a>
        </section>
    @else
        <section class="panel">
            <h2>Order History</h2>
            <p class="text-secondary">{{ $intro }}</p>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 team-table order-table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Order</th>
                            <th scope="col" class="order-wrap">Products</th>
                            <th scope="col">Total</th>
                            <th scope="col">Total BV</th>
                            <th scope="col">Status</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>{{ $orders->firstItem() + $loop->index }}</td>
                                <td>
                                    <strong>{{ $order->number }}</strong>
                                    <div class="text-secondary">{{ $order->created_at?->format('M j, Y g:i A') ?: '—' }}</div>
                                </td>
                                <td class="order-wrap">
                                    @if ($order->items->isEmpty())
                                        <span class="text-secondary">No products</span>
                                    @else
                                        <ul class="order-lines">
                                            @foreach ($order->items as $item)
                                                <li class="order-line">
                                                    @if ($item->imageUrl())
                                                        <img src="{{ $item->imageUrl() }}" alt="" width="40" height="40">
                                                    @else
                                                        <span class="order-line-fallback" aria-hidden="true"><i class="bi bi-box-seam"></i></span>
                                                    @endif
                                                    <div>
                                                        <p>{{ $item->name }}</p>
                                                        <small>{{ $item->sku }} · Qty {{ $item->qty }} · {{ $item->formattedPrice() }} each · {{ $item->formattedLineTotal() }} · BV {{ $item->formattedBv() }} each · Line BV {{ $item->formattedLineBv() }}</small>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td>{{ $order->formattedTotal() }}</td>
                                <td>{{ $order->formattedTotalBv() }}</td>
                                <td><span class="order-status is-{{ $order->status }}">{{ $order->statusLabel() }}</span></td>
                                <td>
                                    <div class="order-actions">
                                        <a class="btn cat-action" href="{{ route('dashboard.orders.show', $order) }}">Details</a>
                                        <button
                                            class="btn cat-action"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#upload-payment"
                                            data-payment-action="{{ route('dashboard.orders.payment', $order) }}"
                                            data-number="{{ $order->number }}"
                                            data-sponsor="{{ $order->payment?->franchise_sponsor_id }}"
                                            data-amount="{{ $order->payment?->amount ?? $order->total }}"
                                            data-pay-mode="{{ $order->payment?->pay_mode }}"
                                            data-remark="{{ $order->payment?->remark }}"
                                            data-screenshot="{{ $order->payment?->screenshotUrl() }}"
                                        >Upload payment</button>
                                        @if ($order->payment)
                                            <span class="payment-uploaded">Payment uploaded</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        @if ($orders->hasPages())
            <div class="cat-pages">{{ $orders->links() }}</div>
        @endif

        @php
            $failedPaymentOrder = $orders->getCollection()->firstWhere('id', (int) session('payment_order'));
        @endphp
        <div class="modal fade" id="upload-payment" tabindex="-1" aria-labelledby="upload-payment-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content cat-modal">
                    <form id="upload-payment-form" method="POST" action="{{ $failedPaymentOrder ? route('dashboard.orders.payment', $failedPaymentOrder) : '' }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <div>
                                <h2 class="modal-title" id="upload-payment-title">Upload payment</h2>
                                <p class="text-secondary mb-0" id="upload-payment-order">{{ $failedPaymentOrder?->number }}</p>
                            </div>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label" for="franchise_sponsor_id">Franchise Sponsor ID</label>
                                <input class="form-control" id="franchise_sponsor_id" name="franchise_sponsor_id" type="text" value="{{ old('franchise_sponsor_id', $failedPaymentOrder?->payment?->franchise_sponsor_id) }}" maxlength="64" autocomplete="off" required>
                                @error('franchise_sponsor_id')
                                    <p class="text-danger small mb-0">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="payment_amount">Amount</label>
                                <input class="form-control" id="payment_amount" name="amount" type="number" inputmode="decimal" min="0.01" step="0.01" value="{{ old('amount', $failedPaymentOrder?->payment?->amount ?? $failedPaymentOrder?->total) }}" required>
                                @error('amount')
                                    <p class="text-danger small mb-0">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="payment_screenshot">Screenshot</label>
                                <input class="form-control" id="payment_screenshot" name="screenshot" type="file" accept="image/jpeg,image/png,image/webp" @required(! filled($failedPaymentOrder?->payment?->screenshot))>
                                <p class="small text-secondary mb-0 mt-1" id="upload-payment-current" @if (! $failedPaymentOrder?->payment?->screenshotUrl()) hidden @endif>
                                    Current screenshot: <a id="upload-payment-current-link" href="{{ $failedPaymentOrder?->payment?->screenshotUrl() }}" target="_blank" rel="noopener">View</a>
                                </p>
                                @error('screenshot')
                                    <p class="text-danger small mb-0">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="pay_mode">Pay mode</label>
                                <select class="form-select" id="pay_mode" name="pay_mode" required>
                                    <option value="">Select pay mode</option>
                                    @foreach (\App\Models\OrderPayment::MODES as $value => $label)
                                        <option value="{{ $value }}" @selected(old('pay_mode', $failedPaymentOrder?->payment?->pay_mode) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('pay_mode')
                                    <p class="text-danger small mb-0">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-0">
                                <label class="form-label" for="payment_remark">Remark</label>
                                <textarea class="form-control" id="payment_remark" name="remark" rows="3" maxlength="500">{{ old('remark', $failedPaymentOrder?->payment?->remark) }}</textarea>
                                @error('remark')
                                    <p class="text-danger small mb-0">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn dash-btn-quiet" type="button" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn dash-btn" type="submit">Upload payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @push('scripts')
            <script>
                document.querySelectorAll('[data-payment-action]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        var form = document.getElementById('upload-payment-form');
                        if (!form) {
                            return;
                        }

                        form.action = button.getAttribute('data-payment-action') || '';
                        form.querySelector('[name="franchise_sponsor_id"]').value = button.getAttribute('data-sponsor') || '';
                        form.querySelector('[name="amount"]').value = button.getAttribute('data-amount') || '';
                        form.querySelector('[name="pay_mode"]').value = button.getAttribute('data-pay-mode') || '';
                        form.querySelector('[name="remark"]').value = button.getAttribute('data-remark') || '';

                        var current = button.getAttribute('data-screenshot') || '';
                        var file = form.querySelector('[name="screenshot"]');
                        var note = document.getElementById('upload-payment-current');
                        var link = document.getElementById('upload-payment-current-link');
                        file.value = '';
                        file.required = current === '';
                        note.hidden = current === '';
                        if (current === '') {
                            link.removeAttribute('href');
                        } else {
                            link.href = current;
                        }

                        document.getElementById('upload-payment-order').textContent = button.getAttribute('data-number') || '';
                    });
                });
            </script>
            @if ($failedPaymentOrder && $errors->any())
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var modal = document.getElementById('upload-payment');
                        if (modal && window.bootstrap) {
                            window.bootstrap.Modal.getOrCreateInstance(modal).show();
                        }
                    });
                </script>
            @endif
        @endpush
    @endif
@endif
