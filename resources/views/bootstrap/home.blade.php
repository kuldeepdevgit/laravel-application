@extends('bootstrap.layout')

@section('title', 'Brightpath — Bootstrap template')

@section('content')
    <section class="bp-hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <p class="bp-kicker mb-0">Bootstrap template</p>
                    <h1>A Bootstrap front page you can switch on</h1>
                    <p class="bp-lede">Brightpath is a member-ready landing page built with Bootstrap. Log in and the same account opens a dashboard.</p>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <a class="btn bp-btn btn-lg" href="{{ route('register') }}">Create an account</a>
                        <a class="btn bp-btn-ghost btn-lg" href="{{ route('bootstrap.services') }}">See services</a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="bp-panel">
                                <strong>3</strong>
                                <span>pages in this template</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bp-panel">
                                <strong>1</strong>
                                <span>member dashboard</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="bp-panel">
                                <strong>Bootstrap 5</strong>
                                <span>Navbar, grid, forms, and buttons. No extra front-end build.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bp-section">
        <div class="container">
            <div class="row g-3">
                <div class="col-md-4">
                    <article class="bp-card">
                        <span class="bp-icon"><i class="bi bi-layout-text-window-reverse"></i></span>
                        <h2>Switchable front</h2>
                        <p class="mb-0 text-secondary">Mark Bootstrap active on the templates page and this home page is what visitors see at the root.</p>
                    </article>
                </div>
                <div class="col-md-4">
                    <article class="bp-card">
                        <span class="bp-icon"><i class="bi bi-person-check"></i></span>
                        <h2>Member login</h2>
                        <p class="mb-0 text-secondary">Register or log in and Laravel sends you to the member dashboard, not back to the public home page.</p>
                    </article>
                </div>
                <div class="col-md-4">
                    <article class="bp-card">
                        <span class="bp-icon"><i class="bi bi-chat-left-text"></i></span>
                        <h2>Contact form</h2>
                        <p class="mb-0 text-secondary">The contact page validates a name, email, and message. This demo does not send the note.</p>
                    </article>
                </div>
            </div>
        </div>
    </section>
@endsection
