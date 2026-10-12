@extends('ai.layout')

@section('title', 'Forgot password — Keel')

@section('content')
    <section class="auth-shell">
        <div class="auth-card">
            <h1>Forgot password</h1>
            <p class="lede">Enter your email and we will send a link to choose a new password.</p>

            @if (session('status'))
                <p class="auth-status" role="status">{{ session('status') }}</p>
            @endif

            @if (session('reset_url'))
                <p class="auth-status">This demo writes mail to the log, so the reset link is here: <a href="{{ session('reset_url') }}">Reset your password</a></p>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <button class="btn btn-lg auth-submit" type="submit">Send reset link</button>
            </form>

            <div class="auth-links">
                <a href="{{ route('login') }}">Back to log in</a>
                <a href="{{ route('register') }}">Create an account</a>
            </div>
        </div>
    </section>
@endsection
