@extends('ai.layout')

@section('title', 'Keel — AI design tool')

@section('content')
    <section class="hero">
        <div class="container">
            <h1>Introducing AI Design Tool for the Next Generation</h1>
            <p class="lede">Embrace the Future with AI-Driven Design</p>
            <div class="hero-actions">
                <a class="btn btn-lg" href="{{ route('ai.workspace') }}">Try For Free</a>
                <a class="btn btn-lg" href="#stage">Watch Demo</a>
            </div>
        </div>
    </section>

    <section class="stage-wrap" id="stage">
        <div class="container">
            <div class="stage">
                <div class="stage-top">
                    <span>Keel</span>
                    <span>Layers · Assets · Page 1</span>
                </div>
                <div class="stage-body">
                    <div class="stage-tools" aria-hidden="true">
                        <i class="bi bi-cursor"></i>
                        <i class="bi bi-square"></i>
                        <i class="bi bi-type"></i>
                        <i class="bi bi-image"></i>
                        <i class="bi bi-pen"></i>
                    </div>
                    <div class="stage-canvas">
                        <div class="art">
                            <button class="resize-pill" type="button">
                                <span class="rec" aria-hidden="true"></span>
                                Resize the Image Now
                                <i class="bi bi-mic"></i>
                            </button>
                            <span class="handle tl"></span>
                            <span class="handle tr"></span>
                            <span class="handle bl"></span>
                            <span class="handle br"></span>
                        </div>
                    </div>
                    <aside class="stage-side">
                        <h2>Layers</h2>
                        <div class="layer active"><span>Hero glow</span><i class="bi bi-eye"></i></div>
                        <div class="layer"><span>Abstract frame</span><i class="bi bi-eye"></i></div>
                        <div class="layer"><span>Headline</span><i class="bi bi-eye"></i></div>
                        <div class="layer"><span>Background</span><i class="bi bi-eye"></i></div>
                    </aside>
                </div>
            </div>
        </div>
    </section>

    <section class="logo-band">
        <div class="container">
            <p>Used by teams who design every day</p>
            <div class="logo-row" aria-hidden="true">
                <span>keel</span><span>keel</span><span>keel</span><span>keel</span><span>keel</span><span>keel</span>
            </div>
        </div>
    </section>

    <section class="section" id="features">
        <div class="container">
            <div class="section-head">
                <h2 class="feature-title">Packed with Features Beyond your Imagination</h2>
            </div>
            <div class="card-grid">
                <article class="feature-card">
                    <h3>Resize by asking</h3>
                    <p>Tell the canvas what to change. The frame stays selected while the layout updates.</p>
                </article>
                <article class="feature-card">
                    <h3>Layers you can see</h3>
                    <p>Glow, type, and art stay on their own layers so a change does not wipe the page.</p>
                </article>
                <article class="feature-card">
                    <h3>A desk, then a file</h3>
                    <p>Try a prompt in the workspace. This demo answers from a script, ready for a real model later.</p>
                </article>
            </div>
        </div>
    </section>
@endsection
