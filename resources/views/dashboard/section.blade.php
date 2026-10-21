@extends('dashboard.layout')

@section('title', $heading.' — '.$user->name)

@section('heading', $heading)

@section('content')
    <section class="panel">
        <h2>{{ $heading }}</h2>
        <p class="text-secondary @if (! in_array($section, ['ref-link', 'manage-profile', 'edit-profile'], true)) mb-0 @endif">{{ $intro }}</p>

        @if ($section === 'ref-link' && $referral)
            <p class="mb-2"><strong>{{ $user->user_name }}</strong></p>
            <p class="mb-0"><a href="{{ $referral }}">{{ $referral }}</a></p>
        @endif

        @if (in_array($section, ['manage-profile', 'edit-profile'], true))
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
@endsection
