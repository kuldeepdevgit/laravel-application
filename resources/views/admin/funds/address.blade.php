@extends('admin.layout')

@section('title', 'Update Fund Address — Admin')

@section('heading', 'Update Fund Address')

@section('content')
    <div class="cat-page">
        @if (session('status'))
            <p class="cat-note" role="status">{{ session('status') }}</p>
        @endif

        @if ($total === 0)
            <section class="panel cat-empty">
                <span class="cat-empty-mark" aria-hidden="true"><i class="bi bi-wallet"></i></span>
                <h2>No fund addresses yet</h2>
                <p>Add a USDT wallet or an INR bank account. You can keep more than one.</p>
                <a class="btn dash-btn" href="{{ route('admin.funds.address.create') }}">Add address</a>
            </section>
        @else
            <div class="cat-toolbar">
                <form class="cat-search" method="GET" action="{{ route('admin.funds.address') }}">
                    <label class="visually-hidden" for="q">Search fund addresses</label>
                    <input class="form-control" id="q" name="q" type="search" value="{{ $search }}" placeholder="Search wallet, bank, or account" autocomplete="off">
                    <button class="btn dash-btn" type="submit">Search</button>
                </form>
                <a class="btn dash-btn" href="{{ route('admin.funds.address.create') }}">Add address</a>
            </div>

            @if ($addresses->isEmpty())
                <section class="panel cat-empty">
                    <h2>No matches</h2>
                    <p>Nothing matches “{{ $search }}”.</p>
                    <a class="btn dash-btn-quiet" href="{{ route('admin.funds.address') }}">Clear search</a>
                </section>
            @else
                <section class="panel">
                    <h2>Fund addresses</h2>
                    <p class="text-secondary">USDT wallets and INR bank accounts used for fund requests.</p>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 team-table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Wallet address</th>
                                    <th scope="col">Bank name</th>
                                    <th scope="col">Account name</th>
                                    <th scope="col">Account number</th>
                                    <th scope="col">IFSC code</th>
                                    <th scope="col">Screenshot</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($addresses as $address)
                                    <tr>
                                        <td>{{ $addresses->firstItem() + $loop->index }}</td>
                                        <td>{{ $address->isUsdt() ? 'USDT' : 'INR' }}</td>
                                        <td>{{ $address->wallet_address ?: '—' }}</td>
                                        <td>{{ $address->bank_name ?: '—' }}</td>
                                        <td>{{ $address->account_name ?: '—' }}</td>
                                        <td>{{ $address->account_number ?: '—' }}</td>
                                        <td>{{ $address->ifsc_code ?: '—' }}</td>
                                        <td>
                                            @if ($address->screenshotUrl())
                                                <a href="{{ $address->screenshotUrl() }}" target="_blank" rel="noopener">
                                                    <img src="{{ $address->screenshotUrl() }}" alt="Wallet screenshot" width="72" height="48">
                                                </a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            <div class="email-actions">
                                                <a class="btn cat-action" href="{{ route('admin.funds.address.edit', $address) }}">Edit</a>
                                                <button
                                                    class="btn cat-action cat-action-delete"
                                                    type="button"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteFundAddress"
                                                    data-delete-action="{{ route('admin.funds.address.destroy', $address) }}"
                                                    data-delete-name="{{ $address->label() }}"
                                                >Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                @if ($addresses->hasPages())
                    <div class="cat-pages">{{ $addresses->links() }}</div>
                @endif
            @endif
        @endif
    </div>

    <div class="modal fade" id="deleteFundAddress" tabindex="-1" aria-labelledby="deleteFundAddressLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content cat-modal">
                <div class="modal-body">
                    <h2 id="deleteFundAddressLabel">Delete this fund address?</h2>
                    <p><strong id="delete-fund-address-name"></strong> will be removed. This cannot be undone.</p>
                    <form id="delete-fund-address-form" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="d-flex justify-content-end gap-2">
                            <button class="btn dash-btn-quiet" type="button" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn cat-action-delete" type="submit">Delete address</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-delete-action]').forEach((button) => {
            button.addEventListener('click', () => {
                const form = document.getElementById('delete-fund-address-form');
                const name = document.getElementById('delete-fund-address-name');
                form.action = button.dataset.deleteAction;
                name.textContent = button.dataset.deleteName;
            });
        });
    </script>
@endpush
