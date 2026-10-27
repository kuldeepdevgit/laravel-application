@extends('admin.layout')

@section('title', 'Admin — '.auth()->user()->name)

@section('heading', 'Admin')

@section('content')
    <p class="mb-4">Welcome back, {{ auth()->user()->name }}. This desk is for the super admin.</p>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <article class="stat-card">
                <span>Categories</span>
                <strong>{{ $categoryCount }}</strong>
            </article>
        </div>
        <div class="col-md-4">
            <article class="stat-card">
                <span>Signed in as</span>
                <strong>{{ auth()->user()->email }}</strong>
            </article>
        </div>
    </div>

    <section class="panel">
        <h2>Category</h2>
        <p class="text-secondary">Add, edit, and remove categories from this admin desk.</p>
        <a class="btn dash-btn" href="{{ route('admin.categories.index') }}">Open categories</a>
    </section>
@endsection
