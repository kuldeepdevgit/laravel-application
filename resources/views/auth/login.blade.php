@extends('ai.layout')

@section('title', 'Log in — Green Smart India')

@section('content')
    <section class="auth-shell">
        <div class="auth-card">
            <a class="auth-logo" href="{{ route('home') }}" aria-label="Green Smart India E Services">
                @include('partials.logo', ['class' => 'auth-logo-img', 'size' => 148])
            </a>
            <h1>Log in</h1>
            <p class="lede">Use the email and password for your account.</p>

            @if (session('status'))
                <p class="auth-status" role="status">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required>
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <label class="check">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                    Remember me
                </label>
                <button class="btn btn-lg auth-submit" type="submit">Log in</button>
            </form>

            <div class="auth-links">
                <a href="{{ route('password.request') }}">Forgot password?</a>
                <a href="{{ route('register') }}">Create an account</a>
            </div>
        </div>
    </section>
@endsection
