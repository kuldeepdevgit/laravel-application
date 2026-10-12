@extends('ai.layout')

@section('title', 'Pricing — Keel')

@section('content')
    <section class="page-hero">
        <div class="container">
            <p class="kicker"><i class="bi bi-stars"></i> Pricing</p>
            <h1>A desk for the size of the work.</h1>
            <p class="lede">Sample prices for the template. Nothing on this page bills a card.</p>
            <div class="price-switch" role="group" aria-label="Billing period">
                <button type="button" id="bill-month" aria-pressed="true">Monthly</button>
                <button type="button" id="bill-year" aria-pressed="false">Yearly</button>
            </div>
        </div>
    </section>

    <section class="section pt-0">
        <div class="container price-grid">
            <article class="price-card">
                <h2 class="h4">Field</h2>
                <p class="fine mb-0">Try the scripted desk.</p>
                <p class="amount"><span data-month="$0" data-year="$0">$0</span></p>
                <ul>
                    <li>Sample replies in the browser</li>
                    <li>Briefs and short drafts</li>
                    <li>No account</li>
                </ul>
                <a class="btn btn-ghost" href="{{ route('ai.workspace') }}">Open the desk</a>
            </article>
            <article class="price-card featured">
                <h2 class="h4">Studio</h2>
                <p class="fine mb-0">For a person writing every day.</p>
                <p class="amount"><span data-month="$24" data-year="$20">$24</span> <small>/ month</small></p>
                <ul>
                    <li>Longer threads</li>
                    <li>Shared sources</li>
                    <li>A quieter desk for one writer</li>
                </ul>
                <a class="btn btn-ember" href="{{ route('ai.workspace') }}">Start in the desk</a>
            </article>
            <article class="price-card">
                <h2 class="h4">Floor</h2>
                <p class="fine mb-0">For a team passing drafts around.</p>
                <p class="amount"><span data-month="$64" data-year="$54">$64</span> <small>/ month</small></p>
                <ul>
                    <li>Several desks</li>
                    <li>Review before send</li>
                    <li>A shared source shelf</li>
                </ul>
                <a class="btn btn-ghost" href="{{ route('ai.workspace', ['q' => 'Write a short reply']) }}">See a draft</a>
            </article>
        </div>
    </section>

    <section class="section pt-0">
        <div class="container" style="max-width: 760px;">
            <h2>Questions</h2>
            <div class="accordion faq" id="faq">
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#q1">Does the desk call a live model?</button>
                    </h3>
                    <div id="q1" class="accordion-collapse collapse show" data-bs-parent="#faq">
                        <div class="accordion-body">No. Replies on this template are written in advance. Connect your own endpoint when you want a real model.</div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q2">Are the prices charged?</button>
                    </h3>
                    <div id="q2" class="accordion-collapse collapse" data-bs-parent="#faq">
                        <div class="accordion-body">They are sample numbers for the layout. This page does not take payment.</div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q3">Where does a prompt go?</button>
                    </h3>
                    <div id="q3" class="accordion-collapse collapse" data-bs-parent="#faq">
                        <div class="accordion-body">It stays in the browser for this demo. Refresh the desk and the thread is gone.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
