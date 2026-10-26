<form method="POST" action="{{ route('dashboard.password.update') }}">
    @csrf
    @if (session('status'))
        <p class="text-success" role="status">{{ session('status') }}</p>
    @endif

    <div class="mb-3">
        <label class="form-label" for="password">New Password :</label>
        <input class="form-control" id="password" name="password" type="password" autocomplete="new-password" required>
        @error('password')
            <p class="text-danger small mb-0">{{ $message }}</p>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="password_confirmation">Confirm Password :</label>
        <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        @error('password_confirmation')
            <p class="text-danger small mb-0">{{ $message }}</p>
        @enderror
    </div>

    <button class="btn dash-btn" type="submit">Submit</button>
</form>
