@extends('ai.layout')

@section('title', 'Product — Keel')

@section('content')
    <section class="page-hero">
        <div class="container">
            <p class="kicker"><i class="bi bi-stars"></i> Product</p>
            <h1>Three moves, then you edit.</h1>
            <p class="lede">The desk is built for people who already have the material and need the next readable page.</p>
        </div>
    </section>

    <section class="section">
        <div class="container split">
            <div class="step-list">
                <article class="step">
                    <b>1</b>
                    <div>
                        <h2 class="h4">Bring the pile</h2>
                        <p class="mb-0">A meeting note, a thread, or a rough outline. The sample desk accepts whatever you type.</p>
                    </div>
                </article>
                <article class="step">
                    <b>2</b>
                    <div>
                        <h2 class="h4">Ask for a shape</h2>
                        <p class="mb-0">A one-page brief, a short reply, or a checklist. Say which one you want.</p>
                    </div>
                </article>
                <article class="step">
                    <b>3</b>
                    <div>
                        <h2 class="h4">Keep the source</h2>
                        <p class="mb-0">The sample answers name where a line came from, so the page can be checked.</p>
                    </div>
                </article>
            </div>
            <div class="console">
                <div class="console-top">
                    <span class="dots"><span></span><span></span><span></span></span>
                    <span>Sources</span>
                </div>
                <div class="bubble model">
                    <strong>Attached</strong>
                    <p>Friday rota, vendor note from the 11th, and the decision still waiting on a date.</p>
                    <div class="source-row">
                        <span class="chip">Rota</span>
                        <span class="chip">Vendor note</span>
                        <span class="chip">Open decision</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section pt-0">
        <div class="container">
            <div class="section-head">
                <h2>What this template includes</h2>
            </div>
            <div class="card-grid">
                <article class="feature-card">
                    <h3>Landing</h3>
                    <p>A home page with a sample transcript, four ways in, and a path to pricing.</p>
                </article>
                <article class="feature-card">
                    <h3>Desk</h3>
                    <p>A workspace with starters and scripted replies for a brief or a short email.</p>
                </article>
                <article class="feature-card">
                    <h3>Pricing</h3>
                    <p>Three plans and a monthly or yearly switch. Prices are sample copy.</p>
                </article>
            </div>
            <a class="btn btn-ink mt-4" href="{{ route('ai.workspace') }}">Open the desk</a>
        </div>
    </section>
@endsection
