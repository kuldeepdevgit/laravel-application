@extends('dashboard.layout')

@section('title', 'Dashboard — '.auth()->user()->name)

@section('heading', 'Dashboard')

@section('content')
    <p class="mb-4">Welcome back, {{ auth()->user()->name }}. This desk is what members see after they log in.</p>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <article class="stat-card">
                <span>Email</span>
                <strong>{{ auth()->user()->email }}</strong>
            </article>
        </div>
        <div class="col-md-4">
            <article class="stat-card">
                <span>Member since</span>
                <strong>{{ auth()->user()->created_at?->format('M j, Y') ?? 'Today' }}</strong>
            </article>
        </div>
        <div class="col-md-4">
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
                    <dt class="col-sm-3">Name</dt>
                    <dd class="col-sm-9">{{ auth()->user()->name }}</dd>
                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9">{{ auth()->user()->email }}</dd>
                    <dt class="col-sm-3">Template</dt>
                    <dd class="col-sm-9 mb-0">{{ $template->name ?? 'Ecommerce' }} is the public home page.</dd>
                </dl>
            </section>
        </div>
        <div class="col-lg-5">
            <section class="panel">
                <h2>Next</h2>
                <p class="text-secondary">Open the Bootstrap template, or choose which front page visitors see.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn dash-btn" href="{{ route('bootstrap.home') }}">Open Brightpath</a>
                    <a class="btn btn-outline-dark" href="{{ route('templates.index') }}">Choose template</a>
                </div>
            </section>
        </div>
    </div>
@endsection
