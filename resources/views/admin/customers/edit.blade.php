@extends('admin.layout')

@section('title', 'Edit customer — Admin')

@section('heading', 'Edit customer')

@section('content')
    <div class="cat-page">
        <p class="mb-3"><a class="cat-back" href="{{ route('admin.customers.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Customer Management</a></p>

        <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
            @csrf
            @method('PUT')

            <section class="panel">
                <h2>{{ $customer->name }}</h2>
                <p class="text-secondary">
                    Role:
                    {{ match ($customer->user_role) {
                        'super_admin' => 'Super Admin',
                        'admin' => 'Admin',
                        default => 'Customer',
                    } }}.
                    Leave the password blank to keep the current one.
                </p>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="name">Full name</label>
                        <input class="form-control" id="name" name="name" type="text" value="{{ old('name', $customer->name) }}" maxlength="255" required autocomplete="name">
                        @error('name')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="user_name">User name</label>
                        <input class="form-control" id="user_name" name="user_name" type="text" value="{{ old('user_name', $customer->user_name) }}" maxlength="255" required autocomplete="off">
                        @error('user_name')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control" id="email" name="email" type="email" value="{{ old('email', $customer->email) }}" maxlength="255" required autocomplete="off" @if ($customer->isSuperAdmin()) readonly @endif>
                        @if ($customer->isSuperAdmin())
                            <p class="form-text mb-0">The super admin email stays fixed.</p>
                        @endif
                        @error('email')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="phone_number">Phone</label>
                        <input class="form-control" id="phone_number" name="phone_number" type="tel" value="{{ old('phone_number', $customer->phone_number) }}" maxlength="20" autocomplete="off" @unless ($customer->isSuperAdmin()) required @endunless>
                        @error('phone_number')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="sponsor_name">Sponsor</label>
                        <input class="form-control" id="sponsor_name" name="sponsor_name" type="text" value="{{ old('sponsor_name', $customer->sponsor_name) }}" maxlength="255" autocomplete="off">
                        @error('sponsor_name')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="position">Position</label>
                        <select class="form-select" id="position" name="position">
                            <option value="" @selected(old('position', $customer->position) === null || old('position', $customer->position) === '')>Not set</option>
                            <option value="left" @selected(old('position', $customer->position) === 'left')>Left</option>
                            <option value="right" @selected(old('position', $customer->position) === 'right')>Right</option>
                        </select>
                        @error('position')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="address">Address</label>
                    <input class="form-control" id="address" name="address" type="text" value="{{ old('address', $customer->address) }}" maxlength="255" autocomplete="off">
                    @error('address')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="city">City</label>
                        <input class="form-control" id="city" name="city" type="text" value="{{ old('city', $customer->city) }}" maxlength="80" autocomplete="off">
                        @error('city')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="state">State</label>
                        <input class="form-control" id="state" name="state" type="text" value="{{ old('state', $customer->state) }}" maxlength="80" autocomplete="off">
                        @error('state')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="zip_code">Zip code</label>
                        <input class="form-control" id="zip_code" name="zip_code" type="text" value="{{ old('zip_code', $customer->zip_code) }}" maxlength="20" autocomplete="off">
                        @error('zip_code')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="usdt_bep20">USDT BEP 20</label>
                    <input class="form-control" id="usdt_bep20" name="usdt_bep20" type="text" value="{{ old('usdt_bep20', $customer->usdt_bep20) }}" autocomplete="off">
                    @error('usdt_bep20')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="password">New password</label>
                        <input class="form-control" id="password" name="password" type="password" autocomplete="new-password">
                        @error('password')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label" for="password_confirmation">Confirm password</label>
                        <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
                    </div>
                </div>

                <button class="btn dash-btn" type="submit">Save customer</button>
            </section>
        </form>
    </div>
@endsection
