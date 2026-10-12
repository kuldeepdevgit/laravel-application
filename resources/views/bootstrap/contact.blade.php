@extends('bootstrap.layout')

@section('title', 'Contact — Brightpath')

@section('content')
    <section class="bp-hero">
        <div class="container" style="max-width: 720px;">
            <p class="bp-kicker mb-0">Contact</p>
            <h1>Send a note</h1>
            <p class="bp-lede">Tell us what you need. The form checks the fields and then stays here.</p>

            @if (session('status'))
                <div class="alert alert-success mt-4" role="status">{{ session('status') }}</div>
            @endif

            <form class="bp-form mt-4" method="POST" action="{{ route('bootstrap.contact.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="name">Name</label>
                    <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="message">Message</label>
                    <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="5" required>{{ old('message') }}</textarea>
                    @error('message')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button class="btn bp-btn" type="submit">Send note</button>
            </form>
        </div>
    </section>
@endsection
