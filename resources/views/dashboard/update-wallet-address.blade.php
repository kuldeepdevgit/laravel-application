<form method="POST" action="{{ route('dashboard.wallet.update') }}">
    @csrf
    @if (session('status'))
        <p class="text-success" role="status">{{ session('status') }}</p>
    @endif

    <h3 class="h6">USDT wallet</h3>
    <div class="mb-3">
        <label class="form-label" for="usdt_bep20">USDT BEP 20 :</label>
        <input class="form-control" id="usdt_bep20" name="usdt_bep20" type="text" value="{{ old('usdt_bep20', $user->usdt_bep20) }}" autocomplete="off" spellcheck="false">
        @error('usdt_bep20')
            <p class="text-danger small mb-0">{{ $message }}</p>
        @enderror
    </div>

    <h3 class="h6 mt-4">Bank details</h3>
    <div class="mb-3">
        <label class="form-label" for="bank_name">Bank name :</label>
        <input class="form-control" id="bank_name" name="bank_name" type="text" value="{{ old('bank_name', $user->bank_name) }}" maxlength="120" autocomplete="off">
        @error('bank_name')
            <p class="text-danger small mb-0">{{ $message }}</p>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="account_name">Account name :</label>
        <input class="form-control" id="account_name" name="account_name" type="text" value="{{ old('account_name', $user->account_name) }}" maxlength="120" autocomplete="off">
        @error('account_name')
            <p class="text-danger small mb-0">{{ $message }}</p>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="account_number">Account number :</label>
        <input class="form-control" id="account_number" name="account_number" type="text" value="{{ old('account_number', $user->account_number) }}" maxlength="20" autocomplete="off" inputmode="numeric" spellcheck="false">
        @error('account_number')
            <p class="text-danger small mb-0">{{ $message }}</p>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="ifsc_code">IFSC code :</label>
        <input class="form-control" id="ifsc_code" name="ifsc_code" type="text" value="{{ old('ifsc_code', $user->ifsc_code) }}" maxlength="11" autocomplete="off" spellcheck="false">
        @error('ifsc_code')
            <p class="text-danger small mb-0">{{ $message }}</p>
        @enderror
    </div>

    <div class="mb-3 mt-4">
        <label class="form-label" for="google_pay">Google Pay :</label>
        <input class="form-control" id="google_pay" name="google_pay" type="text" value="{{ old('google_pay', $user->google_pay) }}" maxlength="255" autocomplete="off" spellcheck="false" placeholder="name@okaxis or mobile number">
        <p class="form-text mb-0">UPI ID or 10-digit mobile number.</p>
        @error('google_pay')
            <p class="text-danger small mb-0">{{ $message }}</p>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="phonepe">PhonePe :</label>
        <input class="form-control" id="phonepe" name="phonepe" type="text" value="{{ old('phonepe', $user->phonepe) }}" maxlength="255" autocomplete="off" spellcheck="false" placeholder="name@ybl or mobile number">
        <p class="form-text mb-0">UPI ID or 10-digit mobile number.</p>
        @error('phonepe')
            <p class="text-danger small mb-0">{{ $message }}</p>
        @enderror
    </div>

    <button class="btn dash-btn" type="submit">Submit</button>
</form>
