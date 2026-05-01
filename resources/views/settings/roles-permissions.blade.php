<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Settings - Roles & Permissions</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f3f2f1;
            color: #323130;
            display: flex;
            min-height: 100vh;
        }

        .sidebar { width: 260px; min-height: 100vh; background: #fff; border-right: 1px solid #edebe9; padding: 16px 0; flex-shrink: 0; }
        .sidebar-brand { padding: 10px 16px 18px; border-bottom: 1px solid #edebe9; margin-bottom: 8px; font-weight: 700; font-size: 15px; }
        .nav-section-label { padding: 10px 16px 4px; color: #8a8886; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; }
        .nav-link { display: block; padding: 9px 16px; color: #323130; text-decoration: none; font-size: 14px; border-radius: 2px; margin: 1px 8px; }
        .nav-link:hover { background: #f3f2f1; }
        .nav-link.active { background: #deecf9; color: #005a9e; font-weight: 500; }
        .nav-sub { margin-left: 16px; border-left: 2px solid #edebe9; padding-left: 4px; }
        .nav-sub .nav-link { font-size: 13px; padding: 7px 12px; }
        .sidebar-footer { padding: 16px; border-top: 1px solid #edebe9; margin-top: 8px; }
        .btn-logout { background: transparent; color: #605e5c; border: 1px solid #8a8886; padding: 7px 14px; border-radius: 2px; cursor: pointer; font-size: 13px; font-family: inherit; width: 100%; }
        .btn-logout:hover { background: #f3f2f1; }

        .main { flex: 1; padding: 32px 40px; overflow: auto; }
        .page-title { margin: 0 0 6px; font-size: 22px; font-weight: 600; color: #201f1e; }
        .page-subtitle { margin: 0 0 24px; font-size: 13px; color: #8a8886; }

        .card {
            background: #fff;
            border: 1px solid #edebe9;
            border-radius: 4px;
            max-width: 760px;
            padding: 24px;
        }
        .card h3 {
            margin: 0 0 10px;
            font-size: 16px;
            color: #201f1e;
        }
        .card p {
            margin: 0;
            color: #605e5c;
            font-size: 14px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    @include('partials.global-company-selector')

<aside class="sidebar">
    <div class="sidebar-brand">TI Web App</div>
    <nav>
        <div class="nav-section-label">Menu</div>
        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
        <a class="nav-link" href="{{ route('masters.company.index') }}">Masters</a>
        <a class="nav-link" href="{{ route('modules.project-management.item-issue') }}">Modules</a>

        <div class="nav-section-label" style="margin-top:8px;">Settings</div>
        <div class="nav-sub">
            <a class="nav-link" href="{{ route('settings.token') }}">API Token Timer</a>
            <a class="nav-link" href="{{ route('settings.credentials') }}">D365 Credentials</a>
            <a class="nav-link active" href="{{ route('settings.roles-permissions') }}">Roles & Permissions</a>
        </div>
    </nav>
    <div class="sidebar-footer">
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">Log out</button>
        </form>
    </div>
</aside>

<main class="main">
    <h1 class="page-title">Roles & Permissions</h1>
    <p class="page-subtitle">Manage user access and authorization rules.</p>

    <div class="card">
        <h3>Coming Soon</h3>
        <p>
            This section has been added under Settings and is ready for role and permission management implementation.
        </p>
    </div>
</main>
</body>
</html>
