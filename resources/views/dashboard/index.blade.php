@extends('dashboard.layout')

@section('title', 'Dashboard — '.$user->name)

@section('heading', 'Dashboard')

@section('content')
    <section class="member-hero">
        <div class="member-hero-top">
            <p class="member-kicker">Member account</p>
            <span class="member-state {{ $user->isPaid() ? 'is-active' : 'is-inactive' }}">{{ $user->isPaid() ? 'Active' : 'Inactive' }}</span>
        </div>
        <dl class="member-facts">
            <div>
                <dt>User ID</dt>
                <dd>{{ $user->user_name ?: '—' }}</dd>
            </div>
            <div>
                <dt>Name</dt>
                <dd>{{ $user->name }}</dd>
            </div>
            <div>
                <dt>Sponsor</dt>
                <dd>
                    @if ($sponsor)
                        {{ $sponsor->name }}
                        @if (filled($sponsor->user_name))
                            <span class="member-code">{{ $sponsor->user_name }}</span>
                        @endif
                    @else
                        {{ $user->sponsor_name ?: '—' }}
                    @endif
                </dd>
            </div>
            <div>
                <dt>Current BV</dt>
                <dd>{{ number_format($currentBv, 2) }}</dd>
            </div>
            <div>
                <dt>Package</dt>
                <dd>
                    {{ $package->package_name ?? '—' }}
                    @if ($package)
                        <span class="member-code">{{ $package->isActive() ? 'Active' : 'Inactive' }}</span>
                    @endif
                </dd>
            </div>
        </dl>
    </section>

    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <article class="stat-card">
                <span>Email</span>
                <strong>{{ $user->email }}</strong>
            </article>
        </div>
        <div class="col-6 col-xl-3">
            <article class="stat-card">
                <span>Member since</span>
                <strong>{{ $user->created_at?->format('M j, Y') ?? 'Today' }}</strong>
            </article>
        </div>
        <div class="col-6 col-xl-3">
            <article class="stat-card">
                <span>Account status</span>
                <strong class="{{ $user->isPaid() ? 'is-active' : 'is-inactive' }}">{{ $user->isPaid() ? 'Active' : 'Inactive' }}</strong>
                <small>{{ $user->activation_date?->format('M j, Y') ?? 'Not activated' }}</small>
            </article>
        </div>
        <div class="col-6 col-xl-3">
            <article class="stat-card">
                <span>Active front page</span>
                <strong>{{ $template->name ?? 'Ecommerce' }}</strong>
            </article>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <section class="panel">
                <h2>Account</h2>
                <dl class="row mb-0">
                    <dt class="col-sm-3">User ID</dt>
                    <dd class="col-sm-9">{{ $user->user_name ?: '—' }}</dd>
                    <dt class="col-sm-3">Name</dt>
                    <dd class="col-sm-9">{{ $user->name }}</dd>
                    <dt class="col-sm-3">Sponsor</dt>
                    <dd class="col-sm-9">
                        @if ($sponsor)
                            {{ $sponsor->name }}@if (filled($sponsor->user_name)) ({{ $sponsor->user_name }})@endif
                        @else
                            {{ $user->sponsor_name ?: '—' }}
                        @endif
                    </dd>
                    <dt class="col-sm-3">Position</dt>
                    <dd class="col-sm-9">{{ $user->position ? ucfirst($user->position) : '' }}</dd>
                    <dt class="col-sm-3">Role</dt>
                    <dd class="col-sm-9">{{ $user->user_role }}</dd>
                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9">{{ $user->isPaid() ? 'Active' : 'Inactive' }}</dd>
                    <dt class="col-sm-3">Activation</dt>
                    <dd class="col-sm-9">{{ $user->activation_date?->format('M j, Y') ?? 'Not activated' }}</dd>
                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9">{{ $user->email }}</dd>
                    <dt class="col-sm-3">Phone</dt>
                    <dd class="col-sm-9">{{ $user->phone_number }}</dd>
                    <dt class="col-sm-3">Current BV</dt>
                    <dd class="col-sm-9">{{ number_format($currentBv, 2) }}</dd>
                    <dt class="col-sm-3">Package</dt>
                    <dd class="col-sm-9">
                        {{ $package->package_name ?? '—' }}
                        @if ($package)
                            ({{ $package->isActive() ? 'Active' : 'Inactive' }})
                        @endif
                    </dd>
                    <dt class="col-sm-3">Template</dt>
                    <dd class="col-sm-9 mb-0">{{ $template->name ?? 'Ecommerce' }} is the public home page.</dd>
                </dl>
            </section>
        </div>
        <div class="col-lg-5">
            <section class="panel">
                <h2>Next</h2>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn dash-btn" href="{{ route('bootstrap.home') }}">Open Brightpath</a>
                    <a class="btn btn-outline-dark" href="{{ route('templates.index') }}">Choose template</a>
                </div>
            </section>
        </div>
    </div>
@endsection
