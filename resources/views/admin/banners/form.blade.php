@extends('admin.layout')

@section('title', ($banner->exists ? 'Edit banner' : 'Add banner').' — Admin')

@section('heading', $banner->exists ? 'Edit banner' : 'Add banner')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet">
@endpush

@section('content')
    @php
        $active = (string) old('is_active', $banner->exists ? ($banner->is_active ? '1' : '0') : '1');
    @endphp

    <div class="cat-page">
        <p class="mb-3"><a class="cat-back" href="{{ route('admin.banners.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Banner</a></p>

        <form class="cat-layout" method="POST" action="{{ $banner->exists ? route('admin.banners.update', $banner) : route('admin.banners.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($banner->exists)
                @method('PUT')
            @endif

            <section class="panel">
                <h2>Details</h2>
                <p class="text-secondary">This slide appears on the home page. Lower order numbers come first.</p>

                <div class="mb-3">
                    <label class="form-label" for="eyebrow">Eyebrow</label>
                    <input class="form-control" id="eyebrow" name="eyebrow" type="text" value="{{ old('eyebrow', $banner->eyebrow) }}" maxlength="60" autocomplete="off">
                    <p class="form-text mb-0">Optional short line above the headline.</p>
                    @error('eyebrow')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="title">Headline</label>
                    <input class="form-control" id="title" name="title" type="text" value="{{ old('title', $banner->title) }}" maxlength="80" required autocomplete="off">
                    @error('title')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" maxlength="280">{{ old('description', $banner->description) }}</textarea>
                    <p class="form-text mb-0">Optional. Up to 280 characters.</p>
                    @error('description')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="button_label">Button label</label>
                    <input class="form-control" id="button_label" name="button_label" type="text" value="{{ old('button_label', $banner->button_label) }}" maxlength="40" autocomplete="off">
                    @error('button_label')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="button_url">Button link</label>
                    <input class="form-control" id="button_url" name="button_url" type="text" value="{{ old('button_url', $banner->button_url) }}" maxlength="255" placeholder="/shop" autocomplete="off">
                    <p class="form-text mb-0">Optional. A path such as /shop, or a full https link.</p>
                    @error('button_url')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="sort_order">Order</label>
                    <input class="form-control" id="sort_order" name="sort_order" type="number" min="0" max="9999" value="{{ old('sort_order', $banner->sort_order) }}" required>
                    @error('sort_order')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label" for="is_active">Status</label>
                    <select class="form-select" id="is_active" name="is_active">
                        <option value="1" @selected($active === '1')>Active</option>
                        <option value="0" @selected($active === '0')>Inactive</option>
                    </select>
                    <p class="form-text mb-0">Inactive banners stay off the home page.</p>
                    @error('is_active')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button class="btn dash-btn" type="submit">Save banner</button>
                    <a class="btn dash-btn-quiet" href="{{ route('admin.banners.index') }}">Cancel</a>
                </div>
            </section>

            <section class="panel">
                <h2>Image</h2>
                <p class="text-secondary">Upload a photo, then crop it to 1800 × 1201. JPG, PNG, or WebP, up to 8 MB.</p>

                <div class="cat-drop" id="banner-drop">
                    <input class="visually-hidden" id="banner-image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
                    <div class="cat-preview-frame is-hero">
                        <img id="banner-preview" alt="" @if ($banner->imageUrl()) src="{{ $banner->imageUrl() }}" @else hidden @endif>
                        <span id="banner-placeholder" @if ($banner->imageUrl()) hidden @endif>
                            <i class="bi bi-image" aria-hidden="true"></i>
                            <span>No image yet</span>
                        </span>
                    </div>
                    <button class="btn dash-btn" id="banner-choose" type="button">Choose image</button>
                    <p id="crop-ready" class="cat-ready @if (! $banner->imageUrl()) d-none @endif">{{ $banner->imageUrl() ? 'Current image' : '' }}</p>
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
    <script src="{{ asset('js/banner-crop.js') }}"></script>
@endpush
