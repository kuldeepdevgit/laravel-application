@extends('ai.layout')

@section('title', 'Desk — Keel')

@section('content')
    <div class="desk">
        <aside class="threads">
            <h2>Threads</h2>
            <button class="thread" type="button" data-starter="Summarize these meeting notes into a one-page brief" aria-current="true">
                Q3 brief
                <small>Decision, risks, next step</small>
            </button>
            <button class="thread" type="button" data-starter="Write a short reply">
                A short reply
                <small>Ready to edit</small>
            </button>
            <button class="thread" type="button" data-starter="Make a checklist from this">
                Checklist
                <small>A desk note</small>
            </button>
            <p class="fine mt-3">Sample threads. They fill the composer with a scripted answer.</p>
        </aside>
        <section class="conversation" aria-label="Desk">
            <div class="messages" id="messages">
                <article class="msg model">
                    <div class="who">Keel · demo</div>
                    <h2 class="h4 serif">What should the page do?</h2>
                    <p>Ask for a brief or a short reply. This desk answers from a fixed script so you can design the screen first.</p>
                </article>
            </div>
            <div class="composer-wrap">
                <form class="composer" id="composer">
                    <label class="visually-hidden" for="prompt">Prompt</label>
                    <textarea id="prompt" rows="2" placeholder="Ask for a brief or a short reply" @if ($prompt !== '') data-prefill="{{ $prompt }}" @endif></textarea>
                    <button class="btn btn-ink" type="submit">Send</button>
                </form>
                <p class="fine demo-note">Scripted demo. The prompt is not sent to a model.</p>
            </div>
        </section>
    </div>
@endsection
