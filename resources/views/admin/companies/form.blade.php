@extends('admin.layout')

@section('title', ($company->exists ? 'Edit company' : 'Add company').' — Admin')

@section('heading', $company->exists ? 'Edit company' : 'Add company')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet">
@endpush

@section('content')
    @php
        $active = (string) old('is_active', $company->exists ? ($company->is_active ? '1' : '0') : '1');
    @endphp

    <div class="cat-page">
        <p class="mb-3"><a class="cat-back" href="{{ route('admin.companies.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Company</a></p>

        <form class="cat-layout" method="POST" action="{{ $company->exists ? route('admin.companies.update', $company) : route('admin.companies.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($company->exists)
                @method('PUT')
            @endif

            <section class="panel">
                <h2>Details</h2>
                <p class="text-secondary">Name the company and add the contact details you want to keep.</p>

                <div class="mb-3">
                    <label class="form-label" for="name">Name</label>
                    <input class="form-control" id="name" name="name" type="text" value="{{ old('name', $company->name) }}" maxlength="120" required autocomplete="organization">
                    @error('name')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" maxlength="400">{{ old('description', $company->description) }}</textarea>
                    <p class="form-text mb-0">Optional. Up to 400 characters.</p>
                    @error('description')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" id="email" name="email" type="email" value="{{ old('email', $company->email) }}" maxlength="255" autocomplete="off">
                    @error('email')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="phone">Phone</label>
                    <input class="form-control" id="phone" name="phone" type="tel" value="{{ old('phone', $company->phone) }}" maxlength="20" autocomplete="off">
                    @error('phone')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="address">Address</label>
                    <input class="form-control" id="address" name="address" type="text" value="{{ old('address', $company->address) }}" maxlength="255" autocomplete="off">
                    @error('address')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="website">Website</label>
                    <input class="form-control" id="website" name="website" type="url" value="{{ old('website', $company->website) }}" maxlength="255" placeholder="https://" autocomplete="off">
                    <p class="form-text mb-0">Optional. Include https://.</p>
                    @error('website')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
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
                    <button class="btn dash-btn" type="submit">Save company</button>
                    <a class="btn dash-btn-quiet" href="{{ route('admin.companies.index') }}">Cancel</a>
                </div>
            </section>

            <section class="panel">
                <h2>Logo</h2>
                <p class="text-secondary">Optional. Upload a logo, then crop it to a square. JPG, PNG, or WebP, up to 4 MB.</p>

                <div class="cat-drop" id="company-drop">
                    <input class="visually-hidden" id="company-image" name="logo" type="file" accept="image/jpeg,image/png,image/webp">
                    <div class="cat-preview-frame">
                        <img id="company-preview" alt="" @if ($company->logoUrl()) src="{{ $company->logoUrl() }}" @else hidden @endif>
                        <span id="company-placeholder" @if ($company->logoUrl()) hidden @endif>
                            <i class="bi bi-building" aria-hidden="true"></i>
                            <span>No logo yet</span>
                        </span>
                    </div>
                    <button class="btn dash-btn" id="company-choose" type="button">Choose logo</button>
                    <p id="crop-ready" class="cat-ready @if (! $company->logoUrl()) d-none @endif">{{ $company->logoUrl() ? 'Current logo' : '' }}</p>
                    <p id="crop-error" class="text-danger small mb-0" role="alert"></p>
                    @error('logo')
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
                    <h2 class="modal-title" id="cropModalLabel">Crop logo</h2>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="crop-stage">
                        <img id="crop-image" alt="Logo to crop">
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
    <script src="{{ asset('js/company-crop.js') }}"></script>
@endpush
