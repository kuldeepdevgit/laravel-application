@extends('admin.layout')

@section('title', 'Banner — Admin')

@section('heading', 'Banner')

@section('content')
    <div class="cat-page">
        @if (session('status'))
            <p class="cat-note" role="status">{{ session('status') }}</p>
        @endif

        @if ($total === 0)
            <section class="panel cat-empty">
                <span class="cat-empty-mark" aria-hidden="true"><i class="bi bi-image"></i></span>
                <h2>No banners yet</h2>
                <p>Add a home banner with a headline and an 1800 × 1201 image.</p>
                <a class="btn dash-btn" href="{{ route('admin.banners.create') }}">Add banner</a>
            </section>
        @else
            <div class="cat-toolbar">
                <form class="cat-search" method="GET" action="{{ route('admin.banners.index') }}">
                    <label class="visually-hidden" for="q">Search banners</label>
                    <input class="form-control" id="q" name="q" type="search" value="{{ $search }}" placeholder="Search banners" autocomplete="off">
                    <button class="btn dash-btn" type="submit">Search</button>
                </form>
                <a class="btn dash-btn" href="{{ route('admin.banners.create') }}">Add banner</a>
            </div>

            @if ($banners->isEmpty())
                <section class="panel cat-empty">
                    <h2>No matches</h2>
                    <p>Nothing matches “{{ $search }}”.</p>
                    <a class="btn dash-btn-quiet" href="{{ route('admin.banners.index') }}">Clear search</a>
                </section>
            @else
                <div class="row g-3">
                    @foreach ($banners as $banner)
                        <div class="col-sm-6 col-xl-4">
                            <article class="cat-card">
                                <div class="cat-card-media is-hero">
                                    @if ($banner->imageUrl())
                                        <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->title }}">
                                    @else
                                        <span class="cat-card-fallback" aria-hidden="true"><i class="bi bi-image"></i></span>
                                    @endif
                                    <span class="cat-pill @if ($banner->is_active) is-live @endif">{{ $banner->is_active ? 'Active' : 'Inactive' }}</span>
                                    <span class="cat-pill is-feature">{{ $banner->sort_order }}</span>
                                </div>
                                <div class="cat-card-body">
                                    <h3>{{ $banner->title }}</h3>
                                    <p>{{ $banner->eyebrow ?: 'No eyebrow' }}</p>
                                    <p>{{ $banner->description ?: 'No description' }}</p>
                                    <div class="cat-card-actions">
                                        <a class="btn cat-action" href="{{ route('admin.banners.edit', $banner) }}">Edit</a>
                                        <button
                                            class="btn cat-action cat-action-delete"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteBanner"
                                            data-delete-action="{{ route('admin.banners.destroy', $banner) }}"
                                            data-delete-name="{{ $banner->title }}"
                                        >Delete</button>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>

                @if ($banners->hasPages())
                    <div class="cat-pages">{{ $banners->links() }}</div>
                @endif
            @endif
        @endif
    </div>

    <div class="modal fade" id="deleteBanner" tabindex="-1" aria-labelledby="deleteBannerLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content cat-modal">
                <div class="modal-body">
                    <h2 id="deleteBannerLabel">Delete this banner?</h2>
                    <p><strong id="delete-banner-name"></strong> and its image will be removed. This cannot be undone.</p>
                    <form id="delete-banner-form" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="d-flex justify-content-end gap-2">
                            <button class="btn dash-btn-quiet" type="button" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn cat-action-delete" type="submit">Delete banner</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-delete-action]').forEach((button) => {
            button.addEventListener('click', () => {
                const form = document.getElementById('delete-banner-form');
                const name = document.getElementById('delete-banner-name');
                form.action = button.dataset.deleteAction;
                name.textContent = button.dataset.deleteName;
            });
        });
    </script>
@endpush
