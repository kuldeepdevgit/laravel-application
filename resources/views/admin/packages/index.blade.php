@extends('admin.layout')

@section('title', 'List package — Admin')

@section('heading', 'List package')

@section('content')
    <div class="cat-page">
        @if (session('status'))
            <p class="cat-note" role="status">{{ session('status') }}</p>
        @endif

        @if ($total === 0)
            <section class="panel cat-empty">
                <span class="cat-empty-mark" aria-hidden="true"><i class="bi bi-layers"></i></span>
                <h2>No packages yet</h2>
                <p>Add a package with a name, an amount, and a status. Package BV matches the amount.</p>
                <a class="btn dash-btn" href="{{ route('admin.packages.create') }}">Add package</a>
            </section>
        @else
            <div class="cat-toolbar">
                <form class="cat-search" method="GET" action="{{ route('admin.packages.index') }}">
                    <label class="visually-hidden" for="q">Search packages</label>
                    <input class="form-control" id="q" name="q" type="search" value="{{ $search }}" placeholder="Search packages" autocomplete="off">
                    <button class="btn dash-btn" type="submit">Search</button>
                </form>
                <a class="btn dash-btn" href="{{ route('admin.packages.create') }}">Add package</a>
            </div>

            @if ($packages->isEmpty())
                <section class="panel cat-empty">
                    <h2>No matches</h2>
                    <p>Nothing matches “{{ $search }}”.</p>
                    <a class="btn dash-btn-quiet" href="{{ route('admin.packages.index') }}">Clear search</a>
                </section>
            @else
                <section class="panel">
                    <h2>Packages</h2>
                    <p class="text-secondary">Each package has a name, an amount, and a BV. Package BV matches the amount.</p>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0 team-table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Package name</th>
                                    <th scope="col">Package amount</th>
                                    <th scope="col">Package BV</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($packages as $package)
                                    <tr>
                                        <td>{{ $packages->firstItem() + $loop->index }}</td>
                                        <td>{{ $package->package_name }}</td>
                                        <td>₹{{ number_format((float) $package->package_amount, 2) }}</td>
                                        <td>{{ number_format((float) $package->package_bv, 2) }}</td>
                                        <td>{{ $package->isActive() ? 'Active' : 'Inactive' }}</td>
                                        <td>
                                            <div class="email-actions">
                                                <a class="btn cat-action" href="{{ route('admin.packages.edit', $package) }}">Edit</a>
                                                <button
                                                    class="btn cat-action cat-action-delete"
                                                    type="button"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deletePackage"
                                                    data-delete-action="{{ route('admin.packages.destroy', $package) }}"
                                                    data-delete-name="{{ $package->package_name }}"
                                                >Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                @if ($packages->hasPages())
                    <div class="cat-pages">{{ $packages->links() }}</div>
                @endif
            @endif
        @endif
    </div>

    <div class="modal fade" id="deletePackage" tabindex="-1" aria-labelledby="deletePackageLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content cat-modal">
                <div class="modal-body">
                    <h2 id="deletePackageLabel">Delete this package?</h2>
                    <p><strong id="delete-package-name"></strong> will be removed. This cannot be undone.</p>
                    <form id="delete-package-form" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="d-flex justify-content-end gap-2">
                            <button class="btn dash-btn-quiet" type="button" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn cat-action-delete" type="submit">Delete package</button>
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
                const form = document.getElementById('delete-package-form');
                const name = document.getElementById('delete-package-name');
                form.action = button.dataset.deleteAction;
                name.textContent = button.dataset.deleteName;
            });
        });
    </script>
@endpush
