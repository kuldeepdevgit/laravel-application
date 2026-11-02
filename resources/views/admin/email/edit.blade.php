@extends('admin.layout')

@section('title', $template->name.' — Admin')

@section('heading', $template->name)

@section('content')
    @php
        $active = (string) old('is_active', $template->is_active ? '1' : '0');
    @endphp

    <div class="cat-page">
        @if (session('status'))
            <p class="cat-note" role="status">{{ session('status') }}</p>
        @endif

        <p class="mb-3"><a class="cat-back" href="{{ route('admin.email.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Email template</a></p>

        <form method="POST" action="{{ route('admin.email.update', $template) }}">
            @csrf
            @method('PUT')

            <section class="panel">
                <h2>{{ $template->name }}</h2>
                <p class="text-secondary">{{ $template->summary() }} The sending account is set under SMTP.</p>

                <div class="mb-3">
                    <label class="form-label" for="subject">Subject</label>
                    <input class="form-control" id="subject" name="subject" type="text" value="{{ old('subject', $template->subject) }}" maxlength="255" required>
                    @error('subject')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="body">Message</label>
                    <textarea class="form-control" id="body" name="body" rows="14" maxlength="10000" required>{{ old('body', $template->body) }}</textarea>
                    <p class="form-text mb-0">Placeholders: @foreach ($placeholders as $token => $label)<code>{{ $token }}</code>@if (! $loop->last), @endif@endforeach</p>
                    @error('body')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label" for="is_active">{{ $template->name }}</label>
                    <select class="form-select" id="is_active" name="is_active">
                        <option value="1" @selected($active === '1')>{{ $template->sendLabel() }}</option>
                        <option value="0" @selected($active === '0')>Do not send</option>
                    </select>
                    @error('is_active')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <button class="btn dash-btn" type="submit">Save template</button>
            </section>
        </form>
    </div>
@endsection
