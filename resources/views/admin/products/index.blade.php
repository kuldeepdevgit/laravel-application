@extends('admin.layout')

@section('title', 'Product — Admin')

@section('heading', 'Product')

@section('content')
    <div class="cat-page">
        @if (session('status'))
            <p class="cat-note" role="status">{{ session('status') }}</p>
        @endif

        @if ($total === 0)
            <section class="panel cat-empty">
                <span class="cat-empty-mark" aria-hidden="true"><i class="bi bi-box-seam"></i></span>
                <h2>No products yet</h2>
                <p>Add a product with a name, a price, stock, and a square image.</p>
                <a class="btn dash-btn" href="{{ route('admin.products.create') }}">Add product</a>
            </section>
        @else
            <div class="cat-toolbar">
                <form class="cat-search" method="GET" action="{{ route('admin.products.index') }}">
                    <label class="visually-hidden" for="q">Search products</label>
                    <input class="form-control" id="q" name="q" type="search" value="{{ $search }}" placeholder="Search products" autocomplete="off">
                    <button class="btn dash-btn" type="submit">Search</button>
                </form>
                <a class="btn dash-btn" href="{{ route('admin.products.create') }}">Add product</a>
            </div>

            @if ($products->isEmpty())
                <section class="panel cat-empty">
                    <h2>No matches</h2>
                    <p>Nothing matches “{{ $search }}”.</p>
                    <a class="btn dash-btn-quiet" href="{{ route('admin.products.index') }}">Clear search</a>
                </section>
            @else
                <div class="row g-3">
                    @foreach ($products as $product)
                        <div class="col-sm-6 col-xl-4">
                            <article class="cat-card">
                                <div class="cat-card-media">
                                    @if ($product->imageUrl())
                                        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}">
                                    @else
                                        <span class="cat-card-fallback" aria-hidden="true"><i class="bi bi-box-seam"></i></span>
                                    @endif
                                    <span class="cat-pill @if ($product->is_active) is-live @endif">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
                                </div>
                                <div class="cat-card-body">
                                    <h3>{{ $product->name }}</h3>
                                    <p>{{ $product->description ?: 'No description' }}</p>
                                    <p class="product-meta">
                                        {{ $product->category?->name ?? 'No category' }}
                                        · {{ $product->formattedPrice() }}
                                        @if ($product->formattedComparePrice())
                                            <s>{{ $product->formattedComparePrice() }}</s>
                                        @endif
                                        · {{ $product->stock === 0 ? 'Sold out' : $product->stock.' in stock' }}
                                    </p>
                                    <div class="cat-card-actions">
                                        <a class="btn cat-action" href="{{ route('admin.products.edit', $product) }}">Edit</a>
                                        <button
                                            class="btn cat-action cat-action-delete"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteProduct"
                                            data-delete-action="{{ route('admin.products.destroy', $product) }}"
                                            data-delete-name="{{ $product->name }}"
                                        >Delete</button>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>

                @if ($products->hasPages())
                    <div class="cat-pages">{{ $products->links() }}</div>
                @endif
            @endif
        @endif
    </div>

    <div class="modal fade" id="deleteProduct" tabindex="-1" aria-labelledby="deleteProductLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content cat-modal">
                <div class="modal-body">
                    <h2 id="deleteProductLabel">Delete this product?</h2>
                    <p><strong id="delete-product-name"></strong> and its image will be removed. This cannot be undone.</p>
                    <form id="delete-product-form" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="d-flex justify-content-end gap-2">
                            <button class="btn dash-btn-quiet" type="button" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn cat-action-delete" type="submit">Delete product</button>
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
                const form = document.getElementById('delete-product-form');
                const name = document.getElementById('delete-product-name');
                form.action = button.dataset.deleteAction;
                name.textContent = button.dataset.deleteName;
            });
        });
    </script>
@endpush
