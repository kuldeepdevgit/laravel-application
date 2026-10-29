@extends('admin.layout')

@section('title', ($product->exists ? 'Edit product' : 'Add product').' — Admin')

@section('heading', $product->exists ? 'Edit product' : 'Add product')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet">
@endpush

@section('content')
    @php
        $active = (string) old('is_active', $product->exists ? ($product->is_active ? '1' : '0') : '1');
        $categoryId = (string) old('category_id', $product->category_id);
    @endphp

    <div class="cat-page">
        <p class="mb-3"><a class="cat-back" href="{{ route('admin.products.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Product</a></p>

        <form class="cat-layout" method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($product->exists)
                @method('PUT')
            @endif

            <section class="panel">
                <h2>Details</h2>
                <p class="text-secondary">Name the product, set its price and stock, and choose whether it is shown as active.</p>

                <div class="mb-3">
                    <label class="form-label" for="name">Name</label>
                    <input class="form-control" id="name" name="name" type="text" value="{{ old('name', $product->name) }}" maxlength="120" required autocomplete="off">
                    @error('name')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="category_id">Category</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">No category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($categoryId === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" maxlength="1000">{{ old('description', $product->description) }}</textarea>
                    <p class="form-text mb-0">Optional. Up to 1,000 characters.</p>
                    @error('description')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label" for="price">Price</label>
                        <input class="form-control" id="price" name="price" type="number" inputmode="decimal" min="0" max="999999.99" step="0.01" value="{{ old('price', $product->price) }}" required>
                        @error('price')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="compare_price">Compare price</label>
                        <input class="form-control" id="compare_price" name="compare_price" type="number" inputmode="decimal" min="0" max="999999.99" step="0.01" value="{{ old('compare_price', $product->compare_price) }}">
                        <p class="form-text mb-0">Optional. Must be higher than the selling price.</p>
                        @error('compare_price')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mt-0 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label" for="stock">Stock</label>
                        <input class="form-control" id="stock" name="stock" type="number" inputmode="numeric" min="0" max="1000000" step="1" value="{{ old('stock', $product->stock ?? 0) }}" required>
                        @error('stock')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="product_bv">Product BV</label>
                        <input class="form-control" id="product_bv" name="product_bv" type="number" inputmode="decimal" min="0" max="999999.99" step="0.01" value="{{ old('product_bv', $product->product_bv ?? 0) }}" required>
                        @error('product_bv')
                            <p class="text-danger small mb-0">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="is_active">Status</label>
                    <select class="form-select" id="is_active" name="is_active">
                        <option value="1" @selected($active === '1')>Active</option>
                        <option value="0" @selected($active === '0')>Inactive</option>
                    </select>
                    @error('is_active')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button class="btn dash-btn" type="submit">Save product</button>
                    <a class="btn dash-btn-quiet" href="{{ route('admin.products.index') }}">Cancel</a>
                </div>
            </section>

            <section class="panel">
                <h2>Image</h2>
                <p class="text-secondary">Upload a photo, then crop it to a square. JPG, PNG, or WebP, up to 4 MB.</p>

                <div class="cat-drop" id="product-drop">
                    <input class="visually-hidden" id="product-image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
                    <div class="cat-preview-frame">
                        <img id="product-preview" alt="" @if ($product->imageUrl()) src="{{ $product->imageUrl() }}" @else hidden @endif>
                        <span id="product-placeholder" @if ($product->imageUrl()) hidden @endif>
                            <i class="bi bi-box-seam" aria-hidden="true"></i>
                            <span>No image yet</span>
                        </span>
                    </div>
                    <button class="btn dash-btn" id="product-choose" type="button">Choose image</button>
                    <p id="crop-ready" class="cat-ready @if (! $product->imageUrl()) d-none @endif">{{ $product->imageUrl() ? 'Current image' : '' }}</p>
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
    <script src="{{ asset('js/product-crop.js') }}"></script>
@endpush
