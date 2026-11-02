@extends('admin.layout')

@section('title', 'SMTP — Admin')

@section('heading', 'SMTP')

@section('content')
    @php
        $encryption = (string) old('encryption', $smtp->encryption);
    @endphp

    <div class="cat-page">
        @if (session('status'))
            <p class="cat-note" role="status">{{ session('status') }}</p>
        @endif

        <p class="mb-3"><a class="cat-back" href="{{ route('admin.dashboard') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Admin</a></p>

        <form method="POST" action="{{ route('admin.smtp.update') }}">
            @csrf
            @method('PUT')

            <section class="panel">
                <h2>SMTP account</h2>
                <p class="text-secondary">Outgoing mail uses this account. Brevo uses smtp-relay.brevo.com on port 587 with TLS. Email wording is set under Email template.</p>

                <div class="mb-3">
                    <label class="form-label" for="host">Host</label>
                    <input class="form-control" id="host" name="host" type="text" value="{{ old('host', $smtp->host) }}" maxlength="255" required autocomplete="off">
                    @error('host')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="port">Port</label>
                    <input class="form-control" id="port" name="port" type="number" value="{{ old('port', $smtp->port) }}" min="1" max="65535" required>
                    @error('port')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="encryption">Encryption</label>
                    <select class="form-select" id="encryption" name="encryption">
                        <option value="tls" @selected($encryption === 'tls')>TLS</option>
                        <option value="ssl" @selected($encryption === 'ssl')>SSL</option>
                        <option value="none" @selected($encryption === 'none')>None</option>
                    </select>
                    @error('encryption')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="username">Username</label>
                    <input class="form-control" id="username" name="username" type="text" value="{{ old('username', $smtp->username) }}" maxlength="255" required autocomplete="off">
                    @error('username')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-control" id="password" name="password" type="password" value="" maxlength="255" autocomplete="new-password">
                    <p class="form-text mb-0">Leave this blank to keep the current password.</p>
                    @error('password')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="from_name">From name</label>
                    <input class="form-control" id="from_name" name="from_name" type="text" value="{{ old('from_name', $smtp->from_name) }}" maxlength="255" required>
                    @error('from_name')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label" for="from_address">From email</label>
                    <input class="form-control" id="from_address" name="from_address" type="email" value="{{ old('from_address', $smtp->from_address) }}" maxlength="255" required autocomplete="off">
                    @error('from_address')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <button class="btn dash-btn" type="submit">Save SMTP</button>
            </section>
        </form>
    </div>
@endsection
