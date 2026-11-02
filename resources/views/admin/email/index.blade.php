@extends('admin.layout')

@section('title', 'Email template — Admin')

@section('heading', 'Email template')

@section('content')
    <div class="cat-page">
        @if (session('status'))
            <p class="cat-note" role="status">{{ session('status') }}</p>
        @endif

        @if ($errors->any())
            <p class="text-danger" role="alert">{{ $errors->first() }}</p>
        @endif

        <section class="panel">
            <h2>Templates</h2>
            <p class="text-secondary">Welcome mail, forget password, and reset password each have their own message. Outgoing mail uses the SMTP account.</p>

            @if ($templates->isEmpty())
                <p class="mb-0">No email templates yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 team-table email-table">
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Subject</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($templates as $template)
                                <tr>
                                    <td class="email-wrap">{{ $template->name }}</td>
                                    <td class="email-wrap">{{ $template->subject }}</td>
                                    <td>{{ $template->is_active ? 'Sending' : 'Not sending' }}</td>
                                    <td>
                                        <div class="email-actions">
                                            <a class="btn cat-action" href="{{ route('admin.email.edit', $template) }}">Edit</a>
                                            <button
                                                class="btn cat-action-delete"
                                                type="button"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteEmail"
                                                data-delete-action="{{ route('admin.email.destroy', $template) }}"
                                                data-delete-name="{{ $template->name }}"
                                            >Delete</button>
                                            <button
                                                class="btn cat-action"
                                                type="button"
                                                data-bs-toggle="modal"
                                                data-bs-target="#resendEmail"
                                                data-resend-action="{{ route('admin.email.resend', $template) }}"
                                                data-resend-name="{{ $template->name }}"
                                            >Resend</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>

    <div class="modal fade" id="deleteEmail" tabindex="-1" aria-labelledby="deleteEmailLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content cat-modal">
                <div class="modal-body">
                    <h2 id="deleteEmailLabel">Delete this email template?</h2>
                    <p><strong id="delete-email-name"></strong> will be removed. This cannot be undone.</p>
                    <form id="delete-email-form" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="d-flex justify-content-end gap-2">
                            <button class="btn dash-btn-quiet" type="button" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn cat-action-delete" type="submit">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resendEmail" tabindex="-1" aria-labelledby="resendEmailLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content cat-modal">
                <div class="modal-body">
                    <h2 id="resendEmailLabel">Resend <span id="resend-email-name"></span></h2>
                    <p>Send this template again to a member. A welcome resend does not include the original password. A forget-password resend includes a new reset link.</p>
                    <form id="resend-email-form" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="resend-email">Member email</label>
                            <input class="form-control" id="resend-email" name="email" type="email" value="{{ old('email') }}" required autocomplete="off">
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button class="btn dash-btn-quiet" type="button" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn dash-btn" type="submit">Resend</button>
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
                document.getElementById('delete-email-form').action = button.dataset.deleteAction;
                document.getElementById('delete-email-name').textContent = button.dataset.deleteName;
            });
        });

        document.querySelectorAll('[data-resend-action]').forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById('resend-email-form').action = button.dataset.resendAction;
                document.getElementById('resend-email-name').textContent = button.dataset.resendName;
                document.getElementById('resend-email').value = '';
            });
        });
    </script>
@endpush
