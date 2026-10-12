@extends('ai.layout')

@section('title', 'Reset password — Keel')

@section('content')
    <section class="auth-shell">
        <div class="auth-card">
            <h1>Choose a new password</h1>
            <p class="lede">Use the email the reset link was sent to.</p>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="username" required autofocus>
                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="password">New password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required>
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                </div>
                <button class="btn btn-lg auth-submit" type="submit">Reset password</button>
            </form>

            <div class="auth-links">
                <a href="{{ route('login') }}">Back to log in</a>
            </div>
        </div>
    </section>
@endsection
