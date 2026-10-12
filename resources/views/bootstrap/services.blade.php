@extends('bootstrap.layout')

@section('title', 'Services — Brightpath')

@section('content')
    <section class="bp-hero pb-4">
        <div class="container">
            <p class="bp-kicker mb-0">Services</p>
            <h1>What Brightpath ships</h1>
            <p class="bp-lede">Three sample offers so the Bootstrap template has a second page, the same way Keel has pricing and Northbay has a shop.</p>
        </div>
    </section>

    <section class="bp-section">
        <div class="container">
            <div class="row g-3">
                <div class="col-md-4">
                    <article class="bp-card">
                        <h2>Landing</h2>
                        <p class="text-secondary">A home page with a hero, three highlights, and a path into member login.</p>
                        <p class="mb-0 fw-bold">Included</p>
                    </article>
                </div>
                <div class="col-md-4">
                    <article class="bp-card">
                        <h2>Services</h2>
                        <p class="text-secondary">This page. Use it as the place to describe what the template is for.</p>
                        <p class="mb-0 fw-bold">Included</p>
                    </article>
                </div>
                <div class="col-md-4">
                    <article class="bp-card">
                        <h2>Contact</h2>
                        <p class="text-secondary">A validated form that flashes a status message and stays on the page.</p>
                        <p class="mb-0 fw-bold">Included</p>
                    </article>
                </div>
            </div>
        </div>
    </section>
@endsection
