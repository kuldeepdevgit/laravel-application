@extends('admin.layout')

@section('title', ($category->exists ? 'Edit category' : 'Add category').' — Admin')

@section('heading', $category->exists ? 'Edit category' : 'Add category')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet">
@endpush

@section('content')
    @php
        $active = (string) old('is_active', $category->exists ? ($category->is_active ? '1' : '0') : '1');
        $featured = (string) old('is_featured', $category->is_featured ? '1' : '0');
    @endphp

    <div class="cat-page">
        <p class="mb-3"><a class="cat-back" href="{{ route('admin.categories.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Category</a></p>

        <form class="cat-layout" method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($category->exists)
                @method('PUT')
            @endif

            <section class="panel">
                <h2>Details</h2>
                <p class="text-secondary">Name the category and choose whether it is shown as active.</p>

                <div class="mb-3">
                    <label class="form-label" for="name">Name</label>
                    <input class="form-control" id="name" name="name" type="text" value="{{ old('name', $category->name) }}" maxlength="80" required autocomplete="off">
                    @error('name')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" maxlength="400">{{ old('description', $category->description) }}</textarea>
                    <p class="form-text mb-0">Optional. Up to 400 characters.</p>
                    @error('description')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="is_active">Status</label>
                    <select class="form-select" id="is_active" name="is_active">
                        <option value="1" @selected($active === '1')>Active</option>
                        <option value="0" @selected($active === '0')>Inactive</option>
                    </select>
                    @error('is_active')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label" for="is_featured">Feature</label>
                    <select class="form-select" id="is_featured" name="is_featured">
                        <option value="1" @selected($featured === '1')>Yes</option>
                        <option value="0" @selected($featured === '0')>No</option>
                    </select>
                    <p class="form-text mb-0">Featured categories are shown on the home page.</p>
                    @error('is_featured')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button class="btn dash-btn" type="submit">Save category</button>
                    <a class="btn dash-btn-quiet" href="{{ route('admin.categories.index') }}">Cancel</a>
                </div>
            </section>

            <section class="panel">
                <h2>Image</h2>
                <p class="text-secondary">Upload a photo, then crop it to 400 × 267. JPG, PNG, or WebP, up to 4 MB.</p>

                <div class="cat-drop" id="category-drop">
                    <input class="visually-hidden" id="category-image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
                    <div class="cat-preview-frame is-banner">
                        <img id="category-preview" alt="" @if ($category->imageUrl()) src="{{ $category->imageUrl() }}" @else hidden @endif>
                        <span id="category-placeholder" @if ($category->imageUrl()) hidden @endif>
                            <i class="bi bi-image" aria-hidden="true"></i>
                            <span>No image yet</span>
                        </span>
                    </div>
                    <button class="btn dash-btn" id="category-choose" type="button">Choose image</button>
                    <p id="crop-ready" class="cat-ready @if (! $category->imageUrl()) d-none @endif">{{ $category->imageUrl() ? 'Current image' : '' }}</p>
                    <p id="crop-error" class="text-danger small mb-0" role="alert"></p>
                    @error('image')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>
            </section>
        </form>
    </div>

    <div class="modal fade" id="cropModal" tabindex="-1" aria-labelledby="cropModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content cat-modal">
                <div class="modal-header">
                    <h2 class="modal-title" id="cropModalLabel">Crop image</h2>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="crop-stage">
                        <img id="crop-image" alt="Image to crop">
                    </div>
                    <div class="crop-tools">
                        <label class="form-label" for="crop-zoom">Zoom</label>
                        <input class="cat-zoom" id="crop-zoom" type="range" min="0" max="100" value="0" aria-valuemin="0" aria-valuemax="100">
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <button class="btn dash-btn-quiet" id="crop-rotate-left" type="button"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Rotate left</button>
                            <button class="btn dash-btn-quiet" id="crop-rotate-right" type="button"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Rotate right</button>
                            <button class="btn dash-btn-quiet" id="crop-reset" type="button">Reset</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn dash-btn-quiet" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn dash-btn" id="crop-apply" type="button">Apply crop</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script src="{{ asset('js/category-crop.js') }}"></script>
@endpush
