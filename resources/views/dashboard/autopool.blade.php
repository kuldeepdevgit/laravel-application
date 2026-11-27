@extends('dashboard.layout')

@section('title', $heading.' — '.$user->name)

@section('heading', $heading)

@section('content')
    <section class="panel">
        <h2>{{ $heading }}</h2>
        <p class="text-secondary">{{ $intro }}</p>
        @include('dashboard.matrix-2x')
    </section>
@endsection
