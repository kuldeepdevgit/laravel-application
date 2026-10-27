@extends('admin.layout')

@section('title', 'Company — Admin')

@section('heading', 'Company')

@section('content')
    <div class="cat-page">
        @if (session('status'))
            <p class="cat-note" role="status">{{ session('status') }}</p>
        @endif

        @if ($total === 0)
            <section class="panel cat-empty">
                <span class="cat-empty-mark" aria-hidden="true"><i class="bi bi-building"></i></span>
                <h2>No companies yet</h2>
                <p>Add a company with a name, contact details, and an optional logo.</p>
                <a class="btn dash-btn" href="{{ route('admin.companies.create') }}">Add company</a>
            </section>
        @else
            <div class="cat-toolbar">
                <form class="cat-search" method="GET" action="{{ route('admin.companies.index') }}">
                    <label class="visually-hidden" for="q">Search companies</label>
                    <input class="form-control" id="q" name="q" type="search" value="{{ $search }}" placeholder="Search companies" autocomplete="off">
                    <button class="btn dash-btn" type="submit">Search</button>
                </form>
                <a class="btn dash-btn" href="{{ route('admin.companies.create') }}">Add company</a>
            </div>

            @if ($companies->isEmpty())
                <section class="panel cat-empty">
                    <h2>No matches</h2>
                    <p>Nothing matches “{{ $search }}”.</p>
                    <a class="btn dash-btn-quiet" href="{{ route('admin.companies.index') }}">Clear search</a>
                </section>
            @else
                <div class="row g-3">
                    @foreach ($companies as $company)
                        <div class="col-sm-6 col-xl-4">
                            <article class="cat-card">
                                <div class="cat-card-media">
                                    @if ($company->logoUrl())
                                        <img src="{{ $company->logoUrl() }}" alt="{{ $company->name }}">
                                    @else
                                        <span class="cat-card-fallback" aria-hidden="true"><i class="bi bi-building"></i></span>
                                    @endif
                                    <span class="cat-pill @if ($company->is_active) is-live @endif">{{ $company->is_active ? 'Active' : 'Inactive' }}</span>
                                </div>
                                <div class="cat-card-body">
                                    <h3>{{ $company->name }}</h3>
                                    <p>{{ $company->description ?: 'No description' }}</p>
                                    @php
                                        $contact = collect([$company->email, $company->phone])->filter()->join(' · ');
                                    @endphp
                                    @if ($contact !== '')
                                        <p class="company-contact">{{ $contact }}</p>
                                    @endif
                                    <div class="cat-card-actions">
                                        <a class="btn cat-action" href="{{ route('admin.companies.edit', $company) }}">Edit</a>
                                        <button
                                            class="btn cat-action cat-action-delete"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteCompany"
                                            data-delete-action="{{ route('admin.companies.destroy', $company) }}"
                                            data-delete-name="{{ $company->name }}"
                                        >Delete</button>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>

                @if ($companies->hasPages())
                    <div class="cat-pages">{{ $companies->links() }}</div>
                @endif
            @endif
        @endif
    </div>

    <div class="modal fade" id="deleteCompany" tabindex="-1" aria-labelledby="deleteCompanyLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content cat-modal">
                <div class="modal-body">
                    <h2 id="deleteCompanyLabel">Delete this company?</h2>
                    <p><strong id="delete-company-name"></strong> and its logo will be removed. This cannot be undone.</p>
                    <form id="delete-company-form" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="d-flex justify-content-end gap-2">
                            <button class="btn dash-btn-quiet" type="button" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn cat-action-delete" type="submit">Delete company</button>
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
                const form = document.getElementById('delete-company-form');
                const name = document.getElementById('delete-company-name');
                form.action = button.dataset.deleteAction;
                name.textContent = button.dataset.deleteName;
            });
        });
    </script>
@endpush
