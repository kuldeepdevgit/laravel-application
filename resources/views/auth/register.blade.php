@extends('ai.layout')

@section('title', 'Create an account — Keel')

@section('content')
    <section class="auth-shell">
        <div class="auth-card">
            <h1>Create an account</h1>
            <p class="lede">Register with your name, sponsor name, email, and a password.</p>

            <form method="POST" action="{{ route('register.store') }}">
                @csrf
                <div class="field">
                    <label for="name">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus>
                    @error('name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="sponsor_name">Sponsor name</label>
                    <input id="sponsor_name" name="sponsor_name" type="text" value="{{ old('sponsor_name') }}" autocomplete="organization" required>
                    @error('sponsor_name')
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
                <div class="field">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                </div>
                <button class="btn btn-lg auth-submit" type="submit">Register</button>
            </form>

            <div class="auth-links">
                <a href="{{ route('login') }}">Already have an account? Log in</a>
            </div>
        </div>
    </section>
@endsection
