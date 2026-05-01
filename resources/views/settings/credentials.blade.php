<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Settings - D365 Credentials</title>
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
        .page-title    { margin: 0 0 6px; font-size: 22px; font-weight: 600; color: #201f1e; }
        .page-subtitle { margin: 0 0 28px; font-size: 13px; color: #8a8886; }

        .card { background: #fff; border: 1px solid #edebe9; border-radius: 4px; max-width: 860px; }
        .card-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 24px; border-bottom: 1px solid #edebe9;
        }
        .card-header-left h3 { margin: 0; font-size: 15px; font-weight: 600; color: #201f1e; }
        .card-header-left p  { margin: 4px 0 0; font-size: 12px; color: #8a8886; }

        .status-pill {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500;
        }
        .status-pill .dot { width: 7px; height: 7px; border-radius: 50%; }
        .pill-ok      { background: #dff6dd; color: #107c10; }
        .pill-ok .dot { background: #107c10; }
        .pill-missing      { background: #fde7e9; color: #a4262c; }
        .pill-missing .dot { background: #a4262c; }

        .card-body { padding: 24px; }
        .view-row {
            display: flex; align-items: center; padding: 11px 0;
            border-bottom: 1px solid #f3f2f1; gap: 0;
        }
        .view-row:last-child { border-bottom: none; }
        .view-label { width: 160px; flex-shrink: 0; font-size: 12px; font-weight: 600; color: #605e5c; }
        .view-value {
            flex: 1; font-size: 13px; color: #323130;
            font-family: 'Courier New', monospace; word-break: break-all; white-space: normal;
        }
        .view-value.empty { color: #c8c6c4; font-family: inherit; font-style: italic; font-size: 12px; }

        .edit-fields { display: none; }
        .field { margin-bottom: 18px; }
        .field:last-child { margin-bottom: 0; }
        .field label { display: block; font-size: 12px; font-weight: 600; color: #323130; margin-bottom: 5px; }
        .field .input-wrap { display: flex; align-items: stretch; }
        .field input {
            flex: 1; border: 1px solid #8a8886; border-radius: 2px;
            padding: 8px 10px; font-size: 13px;
            font-family: 'Courier New', monospace; background: #fff; color: #323130;
        }
        .field input:focus { outline: none; border-color: #0078d4; box-shadow: 0 0 0 1px #0078d4; }
        .field .suffix {
            background: #f3f2f1; border: 1px solid #8a8886; border-left: none;
            padding: 8px 10px; font-size: 13px; color: #605e5c;
            border-radius: 0 2px 2px 0; white-space: nowrap; font-family: inherit;
        }

        .card-footer {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 24px; border-top: 1px solid #edebe9; background: #faf9f8;
            border-radius: 0 0 4px 4px;
        }
        .footer-right { display: flex; gap: 8px; }
        .btn {
            padding: 8px 18px; border-radius: 2px; font-size: 13px;
            font-family: inherit; cursor: pointer; border: 1px solid transparent; font-weight: 500;
        }
        .btn-edit   { background: #fff; color: #0078d4; border-color: #0078d4; }
        .btn-edit:hover { background: #eff6fc; }
        .btn-cancel { background: #fff; color: #323130; border-color: #8a8886; }
        .btn-cancel:hover { background: #f3f2f1; }
        .btn-save   { background: #0078d4; color: #fff; border-color: #0078d4; }
        .btn-save:hover { background: #106ebe; }
        .btn-save:disabled { background: #c8c6c4; border-color: #c8c6c4; cursor: default; }

        .alert { padding: 10px 14px; border-radius: 2px; font-size: 13px; margin-top: 0; }
        .alert-success { background: #dff6dd; color: #107c10; border: 1px solid #9fd89f; }
        .alert-error   { background: #fde7e9; color: #a4262c; border: 1px solid #f1707b; }
        .info-note {
            background: #eff6fc; border-left: 3px solid #0078d4;
            padding: 10px 14px; font-size: 12px; color: #005a9e;
            margin-bottom: 20px; border-radius: 0 2px 2px 0;
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
            <a class="nav-link active" href="{{ route('settings.credentials') }}">D365 Credentials</a>
            <a class="nav-link" href="{{ route('settings.roles-permissions') }}">Roles & Permissions</a>
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
    <h1 class="page-title">D365 Credentials</h1>
    <p class="page-subtitle">Configure the connection details for your Dynamics 365 environment.</p>

    @php
        $allSet = !empty($creds['d365_tenant_id'])
               && !empty($creds['d365_client_id'])
               && !empty($creds['d365_client_secret'])
               && !empty($creds['d365_base_url']);

        $baseUrl = $creds['d365_base_url'] ?? '';
    @endphp

    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <h3>Azure App Registration</h3>
                <p>Used for token generation and all API requests</p>
            </div>
            <span class="status-pill {{ $allSet ? 'pill-ok' : 'pill-missing' }}">
                <span class="dot"></span>
                {{ $allSet ? 'Configured' : 'Not configured' }}
            </span>
        </div>

        <div class="card-body">
            <div id="view-mode">
                <div class="view-row">
                    <span class="view-label">Tenant ID</span>
                    <span class="view-value {{ empty($creds['d365_tenant_id']) ? 'empty' : '' }}">
                        {{ $creds['d365_tenant_id'] ?? 'Not set' }}
                    </span>
                </div>
                <div class="view-row">
                    <span class="view-label">Client ID</span>
                    <span class="view-value {{ empty($creds['d365_client_id']) ? 'empty' : '' }}">
                        {{ $creds['d365_client_id'] ?? 'Not set' }}
                    </span>
                </div>
                <div class="view-row">
                    <span class="view-label">Client Secret</span>
                    <span class="view-value {{ empty($creds['d365_client_secret']) ? 'empty' : '' }}">
                        {{ $creds['d365_client_secret'] ?? 'Not set' }}
                    </span>
                </div>
                <div class="view-row">
                    <span class="view-label">D365 Base URL</span>
                    <span class="view-value {{ empty($baseUrl) ? 'empty' : '' }}">
                        {{ $baseUrl ? $baseUrl . '/.default' : 'Not set' }}
                    </span>
                </div>
            </div>

            <div id="edit-mode" class="edit-fields">
                <div class="info-note">
                    Saving new credentials will immediately invalidate the current bearer token.
                    A fresh token will be fetched automatically on the next API call.
                </div>

                <div class="field">
                    <label>Tenant ID</label>
                    <input type="text" id="cred-tenant-id"
                           value="{{ $creds['d365_tenant_id'] ?? '' }}"
                           placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                           autocomplete="off">
                </div>
                <div class="field">
                    <label>Client ID</label>
                    <input type="text" id="cred-client-id"
                           value="{{ $creds['d365_client_id'] ?? '' }}"
                           placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                           autocomplete="off">
                </div>
                <div class="field">
                    <label>Client Secret</label>
                    <input type="text" id="cred-client-secret"
                           value="{{ $creds['d365_client_secret'] ?? '' }}"
                           placeholder="Paste client secret"
                           autocomplete="off">
                </div>
                <div class="field">
                    <label>D365 Base URL</label>
                    <div class="input-wrap">
                        <input type="text" id="cred-base-url"
                               value="{{ $baseUrl }}"
                               placeholder="https://yourinstance.axcloud.dynamics.com"
                               autocomplete="off"
                               style="border-radius:2px 0 0 2px;">
                        <span class="suffix">/.default</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div id="alert-area"></div>
            <div class="footer-right">
                <button type="button" class="btn btn-edit" id="btn-edit">Edit</button>
                <button type="button" class="btn btn-cancel" id="btn-cancel" style="display:none;">Cancel</button>
                <button type="button" class="btn btn-save" id="btn-save" style="display:none;">Save</button>
            </div>
        </div>
    </div>
</main>

<script>
    const viewMode  = document.getElementById('view-mode');
    const editMode  = document.getElementById('edit-mode');
    const btnEdit   = document.getElementById('btn-edit');
    const btnCancel = document.getElementById('btn-cancel');
    const btnSave   = document.getElementById('btn-save');
    const alertArea = document.getElementById('alert-area');

    const saved = {
        tenantId:     document.getElementById('cred-tenant-id').value,
        clientId:     document.getElementById('cred-client-id').value,
        clientSecret: document.getElementById('cred-client-secret').value,
        baseUrl:      document.getElementById('cred-base-url').value,
    };

    function enterEditMode() {
        viewMode.style.display  = 'none';
        editMode.style.display  = 'block';
        btnEdit.style.display   = 'none';
        btnCancel.style.display = '';
        btnSave.style.display   = '';
        alertArea.innerHTML     = '';
    }

    function enterViewMode(newValues) {
        if (newValues) {
            const rows = document.querySelectorAll('#view-mode .view-value');
            rows[0].textContent = newValues.tenantId     || 'Not set';
            rows[0].className   = 'view-value' + (newValues.tenantId     ? '' : ' empty');
            rows[1].textContent = newValues.clientId     || 'Not set';
            rows[1].className   = 'view-value' + (newValues.clientId     ? '' : ' empty');
            rows[2].textContent = newValues.clientSecret || 'Not set';
            rows[2].className   = 'view-value' + (newValues.clientSecret ? '' : ' empty');
            rows[3].textContent = newValues.baseUrl ? newValues.baseUrl + '/.default' : 'Not set';
            rows[3].className   = 'view-value' + (newValues.baseUrl      ? '' : ' empty');
        }
        viewMode.style.display  = 'block';
        editMode.style.display  = 'none';
        btnEdit.style.display   = '';
        btnCancel.style.display = 'none';
        btnSave.style.display   = 'none';
    }

    btnEdit.addEventListener('click', enterEditMode);

    btnCancel.addEventListener('click', function () {
        document.getElementById('cred-tenant-id').value     = saved.tenantId;
        document.getElementById('cred-client-id').value     = saved.clientId;
        document.getElementById('cred-client-secret').value = saved.clientSecret;
        document.getElementById('cred-base-url').value      = saved.baseUrl;
        enterViewMode();
    });

    btnSave.addEventListener('click', function () {
        const tenantId     = document.getElementById('cred-tenant-id').value.trim();
        const clientId     = document.getElementById('cred-client-id').value.trim();
        const clientSecret = document.getElementById('cred-client-secret').value.trim();
        const baseUrl      = document.getElementById('cred-base-url').value.trim();

        if (!tenantId || !clientId || !clientSecret || !baseUrl) {
            showAlert('All four fields are required.', 'error');
            return;
        }

        btnSave.disabled    = true;
        btnSave.textContent = 'Saving...';
        alertArea.innerHTML = '';

        fetch('{{ route("settings.credentials.save") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept':       'application/json',
                'Content-Type':'application/json',
            },
            body: JSON.stringify({
                d365_tenant_id:     tenantId,
                d365_client_id:     clientId,
                d365_client_secret: clientSecret,
                d365_base_url:      baseUrl,
            }),
        })
        .then(r => r.json())
        .then(function (data) {
            btnSave.disabled    = false;
            btnSave.textContent = 'Save';
            if (data.status) {
                saved.tenantId     = tenantId;
                saved.clientId     = clientId;
                saved.clientSecret = clientSecret;
                saved.baseUrl      = baseUrl;
                enterViewMode({ tenantId, clientId, clientSecret, baseUrl });
                showAlert(data.message, 'success');
                const pill = document.querySelector('.status-pill');
                pill.className   = 'status-pill pill-ok';
                pill.innerHTML   = '<span class="dot"></span> Configured';
                setTimeout(() => { alertArea.innerHTML = ''; }, 5000);
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(function (err) {
            btnSave.disabled    = false;
            btnSave.textContent = 'Save';
            showAlert('Request failed: ' + err.message, 'error');
        });
    });

    function showAlert(msg, type) {
        alertArea.innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
    }
</script>
</body>
</html>
