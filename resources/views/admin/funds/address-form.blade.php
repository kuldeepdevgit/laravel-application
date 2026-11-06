@extends('admin.layout')

@section('title', ($address->exists ? 'Edit fund address' : 'Add fund address').' — Admin')

@section('heading', $address->exists ? 'Edit fund address' : 'Add fund address')

@section('content')
    @php
        $addressType = (string) old('address_type', $address->address_type ?: 'usdt');
    @endphp

    <div class="cat-page">
        <p class="mb-3"><a class="cat-back" href="{{ route('admin.funds.address') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Update Fund Address</a></p>

        <form method="POST" action="{{ $address->exists ? route('admin.funds.address.update', $address) : route('admin.funds.address.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($address->exists)
                @method('PUT')
            @endif

            <section class="panel">
                <h2>Fund address</h2>
                <p class="text-secondary">Choose INR for a bank account, or USDT for a wallet address.</p>

                <div class="mb-3">
                    <label class="form-label" for="address_type">Address type</label>
                    <select class="form-select" id="address_type" name="address_type">
                        <option value="inr" @selected($addressType === 'inr')>INR</option>
                        <option value="usdt" @selected($addressType === 'usdt')>USDT</option>
                    </select>
                    @error('address_type')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div data-address="usdt" @if ($addressType !== 'usdt') hidden @endif>
                    <div class="mb-3">
                        <label class="form-label" for="wallet_address">Wallet address</label>
                        <input class="form-control" id="wallet_address" name="wallet_address" type="text" value="{{ old('wallet_address', $address->wallet_address) }}" maxlength="255" autocomplete="off" spellcheck="false">
                        @error('wallet_address')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="wallet_screenshot">Wallet screenshot</label>
                        @if ($address->screenshotUrl())
                            <p class="mb-2"><img src="{{ $address->screenshotUrl() }}" alt="Current wallet screenshot" width="160" height="100"></p>
                        @endif
                        <input class="form-control" id="wallet_screenshot" name="wallet_screenshot" type="file" accept="image/jpeg,image/png,image/webp">
                        <p class="form-text mb-0">JPEG, PNG, or WebP.{{ $address->screenshotUrl() ? ' Upload a new image to replace the current screenshot.' : '' }}</p>
                        @error('wallet_screenshot')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div data-address="inr" @if ($addressType !== 'inr') hidden @endif>
                    <div class="mb-3">
                        <label class="form-label" for="bank_name">Bank name</label>
                        <input class="form-control" id="bank_name" name="bank_name" type="text" value="{{ old('bank_name', $address->bank_name) }}" maxlength="120" autocomplete="off">
                        @error('bank_name')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="account_name">Account name</label>
                        <input class="form-control" id="account_name" name="account_name" type="text" value="{{ old('account_name', $address->account_name) }}" maxlength="120" autocomplete="off">
                        @error('account_name')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="account_number">Account number</label>
                        <input class="form-control" id="account_number" name="account_number" type="text" value="{{ old('account_number', $address->account_number) }}" maxlength="20" autocomplete="off" inputmode="numeric">
                        @error('account_number')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="ifsc_code">IFSC code</label>
                        <input class="form-control" id="ifsc_code" name="ifsc_code" type="text" value="{{ old('ifsc_code', $address->ifsc_code) }}" maxlength="11" autocomplete="off" spellcheck="false">
                        @error('ifsc_code')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button class="btn dash-btn" type="submit">{{ $address->exists ? 'Save address' : 'Add address' }}</button>
                    <a class="btn dash-btn-quiet" href="{{ route('admin.funds.address') }}">Cancel</a>
                </div>
            </section>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const addressType = document.getElementById('address_type');
        const addressGroups = document.querySelectorAll('[data-address]');

        const showAddressFields = () => {
            addressGroups.forEach((group) => {
                group.hidden = group.dataset.address !== addressType.value;
            });
        };

        addressType.addEventListener('change', showAddressFields);
        showAddressFields();
    </script>
@endpush
