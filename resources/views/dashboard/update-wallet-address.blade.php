<form method="POST" action="{{ route('dashboard.wallet.update') }}">
    @csrf
    @if (session('status'))
        <p class="text-success" role="status">{{ session('status') }}</p>
    @endif

    <div class="mb-3">
        <label class="form-label" for="usdt_bep20">USDT BEP 20 :</label>
        <input class="form-control" id="usdt_bep20" name="usdt_bep20" type="text" value="{{ old('usdt_bep20', $user->usdt_bep20) }}" autocomplete="off" spellcheck="false">
        @error('usdt_bep20')
            <p class="text-danger small mb-0">{{ $message }}</p>
        @enderror
    </div>

    <button class="btn dash-btn" type="submit">Submit</button>
</form>
