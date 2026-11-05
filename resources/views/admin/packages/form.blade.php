@extends('admin.layout')

@section('title', ($package->exists ? 'Edit package' : 'Add package').' — Admin')

@section('heading', $package->exists ? 'Edit package' : 'Add package')

@section('content')
    @php
        $status = (string) old('package_status', $package->package_status ?: 'active');
    @endphp

    <div class="cat-page">
        <p class="mb-3"><a class="cat-back" href="{{ route('admin.packages.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> List package</a></p>

        <form method="POST" action="{{ $package->exists ? route('admin.packages.update', $package) : route('admin.packages.store') }}">
            @csrf
            @if ($package->exists)
                @method('PUT')
            @endif

            <section class="panel">
                <h2>Details</h2>
                <p class="text-secondary">Name the package and set its amount and status. Package BV matches the amount.</p>

                <div class="mb-3">
                    <label class="form-label" for="package_name">Package name</label>
                    <input class="form-control" id="package_name" name="package_name" type="text" value="{{ old('package_name', $package->package_name) }}" maxlength="120" required autocomplete="off">
                    @error('package_name')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="package_amount">Package amount</label>
                    <input class="form-control" id="package_amount" name="package_amount" type="number" value="{{ old('package_amount', $package->package_amount) }}" min="0" max="99999999.99" step="0.01" required inputmode="decimal">
                    @error('package_amount')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="package_bv">Package BV</label>
                    <input class="form-control" id="package_bv" type="text" value="{{ old('package_amount', $package->package_bv ?? $package->package_amount) }}" readonly>
                    <p class="form-text mb-0">Package BV matches the package amount.</p>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="package_status">Status</label>
                    <select class="form-select" id="package_status" name="package_status">
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    </select>
                    @error('package_status')
                        <p class="text-danger small mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button class="btn dash-btn" type="submit">Save package</button>
                    <a class="btn dash-btn-quiet" href="{{ route('admin.packages.index') }}">Cancel</a>
                </div>
            </section>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const amount = document.getElementById('package_amount');
        const bv = document.getElementById('package_bv');

        amount.addEventListener('input', () => {
            bv.value = amount.value;
        });
    </script>
@endpush
