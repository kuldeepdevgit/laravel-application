<form method="POST" action="{{ route('dashboard.profile.update') }}">
    @csrf
    @if (session('status'))
        <p class="text-success" role="status">{{ session('status') }}</p>
    @endif

    <div class="mb-3">
        <label class="form-label" for="name">Full Name :</label>
        <input class="form-control" id="name" name="name" type="text" value="{{ old('name', $user->name) }}" autocomplete="name" required>
        @error('name')
            <p class="text-danger small mb-0">{{ $message }}</p>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="phone_number">Mobile Number:</label>
        <input class="form-control" id="phone_number" name="phone_number" type="tel" value="{{ old('phone_number', $user->phone_number) }}" autocomplete="tel" required>
        @error('phone_number')
            <p class="text-danger small mb-0">{{ $message }}</p>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="email">Email ID:</label>
        <input class="form-control" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="email" required>
        @error('email')
            <p class="text-danger small mb-0">{{ $message }}</p>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="address">Address:</label>
        <input class="form-control" id="address" name="address" type="text" value="{{ old('address', $user->address) }}" autocomplete="street-address">
        @error('address')
            <p class="text-danger small mb-0">{{ $message }}</p>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="city">City:</label>
        <input class="form-control" id="city" name="city" type="text" value="{{ old('city', $user->city) }}" autocomplete="address-level2">
        @error('city')
            <p class="text-danger small mb-0">{{ $message }}</p>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="state">State:</label>
        <input class="form-control" id="state" name="state" type="text" value="{{ old('state', $user->state) }}" autocomplete="address-level1">
        @error('state')
            <p class="text-danger small mb-0">{{ $message }}</p>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="zip_code">Zip Code:</label>
        <input class="form-control" id="zip_code" name="zip_code" type="text" value="{{ old('zip_code', $user->zip_code) }}" autocomplete="postal-code">
        @error('zip_code')
            <p class="text-danger small mb-0">{{ $message }}</p>
        @enderror
    </div>

    <button class="btn dash-btn" type="submit">Submit</button>
</form>
