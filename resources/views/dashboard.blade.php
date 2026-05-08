<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('settings.rbac.partials.styles')
    <style>
        .main {
            flex: 1;
            padding: 24px 32px;
            overflow: auto;
        }
        .main-header {
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }
        .main-header h2 {
            margin: 0 0 6px;
            font-size: 1.5rem;
            color: #201f1e;
        }
        .info-card {
            max-width: 480px;
            padding: 20px;
            background: #fff;
            border-radius: 2px;
            box-shadow: none;
            border: 1px solid #edebe9;
        }
        .info-card h3 {
            margin: 0 0 12px;
            font-size: 1rem;
            color: #201f1e;
        }
        .info-card p { margin: 8px 0; font-size: 14px; color: #605e5c; }
    </style>
</head>
<body>
    @include('partials.global-company-selector')
    @php
        $companyCode = strtoupper((string) ($currentCompanyCode ?? $globalSelectedCompany ?? request()->query('company', '')));
        $companyQuery = $companyCode !== '' ? ['company' => $companyCode] : [];
    @endphp
    @php
        $authCanAccessMasters = $authCanAccessMasters ?? ($authShowMastersSettingsNav ?? false);
        $authIsSuperAdmin = $authIsSuperAdmin ?? ($authShowMastersSettingsNav ?? false);
    @endphp
    @include('settings.rbac.partials.sidebar')

    <main class="main">
        <div class="main-header">
            <h2>Dashboard</h2>
        </div>

        @if (session('warning'))
            <div style="max-width:520px;padding:10px 14px;margin-bottom:16px;background:#fff4ce;border:1px solid #e0d0a0;border-radius:2px;font-size:13px;color:#8a6d3b;">
                {{ session('warning') }}
            </div>
        @endif

        <div class="info-card">
            <h3>User Details</h3>
            <p><strong>Name:</strong> {{ auth()->user()->name }}</p>
            <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
            <p><strong>User ID:</strong> {{ auth()->user()->id }}</p>
        </div>
    </main>

</body>
</html>

