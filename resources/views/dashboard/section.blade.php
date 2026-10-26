@extends('dashboard.layout')

@section('title', $heading.' — '.$user->name)

@section('heading', $heading)

@section('content')
    @if ($section === 'edit-profile')
        <section class="panel">
            <h2>{{ $heading }}</h2>
            <p class="text-secondary">{{ $intro }}</p>
            @include('dashboard.edit-profile')
        </section>
    @elseif ($section === 'change-password')
        <section class="panel">
            <h2>{{ $heading }}</h2>
            <p class="text-secondary">{{ $intro }}</p>
            @include('dashboard.change-password')
        </section>
    @elseif ($section === 'update-wallet-address')
        <section class="panel">
            <h2>{{ $heading }}</h2>
            <p class="text-secondary">{{ $intro }}</p>
            @include('dashboard.update-wallet-address')
        </section>
    @elseif ($section === 'my-team')
        <section class="panel">
            <h2>{{ $heading }}</h2>
            <p class="text-secondary">{{ $intro }}</p>
            @include('dashboard.my-team')
        </section>
    @else
        <section class="panel">
            <h2>{{ $heading }}</h2>
            <p class="text-secondary @if (! in_array($section, ['ref-link', 'manage-profile'], true)) mb-0 @endif">{{ $intro }}</p>

            @if ($section === 'ref-link' && $referral)
                <p class="mb-2"><strong>{{ $user->user_name }}</strong></p>
                <p class="mb-0"><a href="{{ $referral }}">{{ $referral }}</a></p>
            @endif

            @if ($section === 'manage-profile')
                <dl class="row mb-0">
                    <dt class="col-sm-3">Name</dt>
                    <dd class="col-sm-9">{{ $user->name }}</dd>
                    <dt class="col-sm-3">Username</dt>
                    <dd class="col-sm-9">{{ $user->user_name ?: '—' }}</dd>
                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9">{{ $user->email }}</dd>
                    <dt class="col-sm-3">Sponsor</dt>
                    <dd class="col-sm-9 mb-0">{{ $user->sponsor_name ?: '—' }}</dd>
                </dl>
            @endif
        </section>
    @endif
@endsection
