@extends('ai.layout')

@section('title', 'Create an account — Green Smart India')

@section('content')
    <section class="auth-shell">
        <div class="auth-card">
            <a class="auth-logo" href="{{ route('home') }}" aria-label="Green Smart India E Services">
                @include('partials.logo', ['class' => 'auth-logo-img', 'size' => 148])
            </a>
            <h1>Create an account</h1>
            <p class="lede">Register with your sponsor name, name, phone number, email, and a password.</p>

            <form method="POST" action="{{ route('register.store') }}">
                @csrf
                <div class="field">
                    <label for="sponsor_name">Sponsor name</label>
                    <input id="sponsor_name" name="sponsor_name" type="text" value="{{ old('sponsor_name', $sponsorName) }}" autocomplete="organization" required @unless (old('sponsor_name', $sponsorName)) autofocus @endunless>
                    @error('sponsor_name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                    <p id="sponsor-full-name" class="sponsor-full-name" aria-live="polite"></p>
                </div>
                <div class="field">
                    <label for="position">Position</label>
                    <select id="position" name="position" required>
                        <option value="" @selected(old('position') === null || old('position') === '')>Select position</option>
                        <option value="left" @selected(old('position') === 'left')>Left</option>
                        <option value="right" @selected(old('position') === 'right')>Right</option>
                    </select>
                    @error('position')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="name">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required @if (old('sponsor_name', $sponsorName)) autofocus @endif>
                    @error('name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="phone_number">Phone number</label>
                    <input id="phone_number" name="phone_number" type="tel" value="{{ old('phone_number') }}" autocomplete="tel" required>
                    @error('phone_number')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required>
                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required>
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <button class="btn btn-lg auth-submit" type="submit">Register</button>
            </form>

            <div class="auth-links">
                <a href="{{ route('login') }}">Already have an account? Log in</a>
            </div>
        </div>
    </section>
    <script>
        const sponsorInput = document.getElementById('sponsor_name');
        const sponsorFullName = document.getElementById('sponsor-full-name');
        let sponsorLookup;
        let sponsorRequest = 0;

        const showSponsorName = () => {
            const value = sponsorInput.value.trim();
            const requestId = ++sponsorRequest;

            if (value === '') {
                sponsorFullName.textContent = '';
                return;
            }

            fetch(@json(route('register.sponsor')) + '?ref=' + encodeURIComponent(value), {
                headers: { 'Accept': 'application/json' },
            })
                .then((response) => response.ok ? response.json() : { name: null })
                .then((data) => {
                    if (requestId !== sponsorRequest) {
                        return;
                    }

                    sponsorFullName.textContent = data.name || '';
                })
                .catch(() => {
                    if (requestId === sponsorRequest) {
                        sponsorFullName.textContent = '';
                    }
                });
        };

        sponsorInput.addEventListener('input', () => {
            clearTimeout(sponsorLookup);
            sponsorLookup = setTimeout(showSponsorName, 250);
        });

        showSponsorName();
    </script>
@endsection
