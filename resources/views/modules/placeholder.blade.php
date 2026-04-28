<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }} — Module</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; background: #f5f5f5; }
        .card {
            max-width: 560px;
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        a { color: #2563eb; }
    </style>
</head>
<body>
    @include('partials.global-company-selector')
    <div class="card">
        <h1 style="margin-top:0;color:#1e293b;">{{ $title }}</h1>
        <p>Module — coming soon.</p>
        <p><a href="{{ route('dashboard') }}">← Back to Dashboard</a></p>
    </div>
</body>
</html>
