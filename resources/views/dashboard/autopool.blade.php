@extends('dashboard.layout')

@section('title', $heading.' — '.$user->name)

@section('heading', $heading)

@section('content')
    <section class="panel">
        <h2>{{ $heading }}</h2>
        @include('dashboard.matrix-2x')
    </section>
@endsection
