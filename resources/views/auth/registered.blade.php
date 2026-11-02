@extends('ai.layout')

@section('title', 'Account created — Keel')

@section('content')
    <section class="auth-shell">
        <div class="auth-card">
            <h1>Create an account</h1>
            <p class="lede">Save your username and password. Your password is shown only this once.</p>
            @if (session('signup_email_sent'))
                <p class="lede">We also sent these details to your email.</p>
            @endif
            @if (session('signup_email_failed'))
                <p class="lede">The welcome email could not be sent. Keep the username and password on this page.</p>
            @endif

            <div class="field">
                <label>Username</label>
                <p class="auth-credential">{{ $userName }}</p>
            </div>
            <div class="field">
                <label>Password</label>
                <p class="auth-credential">{{ $password }}</p>
            </div>

            <div class="auth-center">
                <a class="btn btn-lg" href="{{ route('dashboard') }}">Dashboard</a>
            </div>
        </div>
    </section>
@endsection
