@extends('admin.layout')

@section('title', 'Category — Admin')

@section('heading', 'Category')

@section('content')
    <div class="cat-page">
        @if (session('status'))
            <p class="cat-note" role="status">{{ session('status') }}</p>
        @endif

        @if ($total === 0)
            <section class="panel cat-empty">
                <span class="cat-empty-mark" aria-hidden="true"><i class="bi bi-grid"></i></span>
                <h2>No categories yet</h2>
                <p>Add a category with a name, a short description, and a 400 × 267 image.</p>
                <a class="btn dash-btn" href="{{ route('admin.categories.create') }}">Add category</a>
            </section>
        @else
            <div class="cat-toolbar">
                <form class="cat-search" method="GET" action="{{ route('admin.categories.index') }}">
                    <label class="visually-hidden" for="q">Search categories</label>
                    <input class="form-control" id="q" name="q" type="search" value="{{ $search }}" placeholder="Search categories" autocomplete="off">
                    <button class="btn dash-btn" type="submit">Search</button>
                </form>
                <a class="btn dash-btn" href="{{ route('admin.categories.create') }}">Add category</a>
            </div>

            @if ($categories->isEmpty())
                <section class="panel cat-empty">
                    <h2>No matches</h2>
                    <p>Nothing matches “{{ $search }}”.</p>
                    <a class="btn dash-btn-quiet" href="{{ route('admin.categories.index') }}">Clear search</a>
                </section>
            @else
                <div class="row g-3">
                    @foreach ($categories as $category)
                        <div class="col-sm-6 col-xl-4">
                            <article class="cat-card">
                                <div class="cat-card-media is-banner">
                                    @if ($category->imageUrl())
                                        <img src="{{ $category->imageUrl() }}" alt="{{ $category->name }}">
                                    @else
                                        <span class="cat-card-fallback" aria-hidden="true"><i class="bi bi-image"></i></span>
                                    @endif
                                    <span class="cat-pill @if ($category->is_active) is-live @endif">{{ $category->is_active ? 'Active' : 'Inactive' }}</span>
                                    @if ($category->is_featured)
                                        <span class="cat-pill is-feature">Featured</span>
                                    @endif
                                </div>
                                <div class="cat-card-body">
                                    <h3>{{ $category->name }}</h3>
                                    <p>{{ $category->description ?: 'No description' }}</p>
                                    <div class="cat-card-actions">
                                        <a class="btn cat-action" href="{{ route('admin.categories.edit', $category) }}">Edit</a>
                                        <button
                                            class="btn cat-action cat-action-delete"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteCategory"
                                            data-delete-action="{{ route('admin.categories.destroy', $category) }}"
                                            data-delete-name="{{ $category->name }}"
                                        >Delete</button>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>

                @if ($categories->hasPages())
                    <div class="cat-pages">{{ $categories->links() }}</div>
                @endif
            @endif
        @endif
    </div>

    <div class="modal fade" id="deleteCategory" tabindex="-1" aria-labelledby="deleteCategoryLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content cat-modal">
                <div class="modal-body">
                    <h2 id="deleteCategoryLabel">Delete this category?</h2>
                    <p><strong id="delete-category-name"></strong> and its image will be removed. This cannot be undone.</p>
                    <form id="delete-category-form" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="d-flex justify-content-end gap-2">
                            <button class="btn dash-btn-quiet" type="button" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn cat-action-delete" type="submit">Delete category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-delete-category], [data-delete-action]').forEach((button) => {
            button.addEventListener('click', () => {
                const form = document.getElementById('delete-category-form');
                const name = document.getElementById('delete-category-name');
                form.action = button.dataset.deleteAction;
                name.textContent = button.dataset.deleteName;
            });
        });
    </script>
@endpush
