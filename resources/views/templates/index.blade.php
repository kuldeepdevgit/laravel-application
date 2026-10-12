<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Templates</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5" style="max-width: 640px;">
        <h1 class="h3 mb-2">Front page</h1>
        <p class="text-secondary">The row marked active is what <a href="{{ route('home') }}">the home page</a> shows.</p>
        @if (session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif
        <div class="list-group">
            @foreach ($templates as $template)
                <div class="list-group-item d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <strong>{{ $template->name }}</strong>
                        <div class="small text-secondary">{{ $template->key }}</div>
                    </div>
                    @if ($template->is_active)
                        <span class="badge text-bg-success">Active</span>
                    @else
                        <form method="POST" action="{{ route('templates.update', $template) }}">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-dark btn-sm" type="submit">Make active</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </main>
</body>
</html>
