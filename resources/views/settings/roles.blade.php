<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Settings - Roles</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f3f2f1; color: #323130; }
        .wrap { max-width: 1080px; margin: 24px auto; padding: 0 16px; }
        .card { background: #fff; border: 1px solid #edebe9; border-radius: 4px; padding: 20px; margin-bottom: 16px; }
        .row { display: flex; gap: 12px; flex-wrap: wrap; align-items: end; }
        .field { min-width: 220px; flex: 1; }
        label { display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#605e5c; }
        input, select { width:100%; border:1px solid #8a8886; border-radius:2px; padding:8px; }
        select[multiple] { min-height: 180px; }
        .btn { background:#0078d4; color:#fff; border:1px solid #0078d4; border-radius:2px; padding:8px 14px; cursor:pointer; }
        .btn-danger { background:#a4262c; border-color:#a4262c; }
        .actions { display:flex; gap:8px; align-items:center; }
        table { width:100%; border-collapse: collapse; }
        th, td { text-align:left; padding:10px 8px; border-bottom:1px solid #edebe9; vertical-align: top; }
        .ok { background:#dff6dd; border:1px solid #9fd89f; color:#107c10; padding:10px 12px; border-radius:2px; margin-bottom:12px; }
        .err { background:#fde7e9; border:1px solid #f1aeb5; color:#a4262c; padding:10px 12px; border-radius:2px; margin-bottom:12px; }
        .pill { display:inline-block; padding:2px 8px; border-radius:999px; background:#eff6fc; color:#005a9e; font-size:12px; margin:2px 4px 2px 0; }
        .hint { color:#605e5c; font-size:13px; }
        .top-nav { margin-bottom: 12px; }
        .top-nav a { text-decoration: none; margin-right: 12px; color: #005a9e; font-weight: 600; }
    </style>
</head>
<body>
@include('partials.global-company-selector')
<div class="wrap">
    <div class="top-nav">
        <a href="{{ route('settings.token', ['company' => request('company')]) }}">Token</a>
        <a href="{{ route('settings.credentials', ['company' => request('company')]) }}">Credentials</a>
        <a href="{{ route('settings.roles-permissions', ['company' => request('company')]) }}">Users</a>
        <a href="{{ route('settings.roles.index', ['company' => request('company')]) }}">Roles</a>
    </div>

    <div class="card">
        <h2>Roles</h2>
        <p class="hint">Create company roles and map permissions. Current company: <strong>{{ $selectedCompany?->name ?? 'N/A' }}</strong></p>

        @if(session('status'))
            <div class="ok">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="err">
                @foreach($errors->all() as $message)
                    <div>{{ $message }}</div>
                @endforeach
            </div>
        @endif

        <form method="post" action="{{ route('settings.roles.store', ['company' => request('company')]) }}">
            @csrf
            <div class="row">
                <div class="field">
                    <label>Role name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="field">
                    <label>Permissions (Ctrl/Cmd + click for multi-select)</label>
                    <select name="permission_ids[]" multiple>
                        @foreach($permissions as $permission)
                            <option value="{{ $permission->id }}">{{ $permission->name }} ({{ $permission->slug }})</option>
                        @endforeach
                    </select>
                </div>
                <div><button class="btn" type="submit">Create Role</button></div>
            </div>
        </form>
    </div>

    <div class="card">
        <h3>Existing Roles</h3>
        <table>
            <thead><tr><th>Name</th><th>Slug</th><th>Permissions</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($roles as $role)
                <tr>
                    <td>{{ $role->name }}</td>
                    <td>{{ $role->slug }}</td>
                    <td>
                        @forelse($role->permissions as $permission)
                            <span class="pill">{{ $permission->slug }}</span>
                        @empty
                            <span class="hint">No permissions</span>
                        @endforelse
                    </td>
                    <td>
                        <form method="post" action="{{ route('settings.roles.destroy', ['role' => $role->id, 'company' => request('company')]) }}">
                            @csrf
                            @method('delete')
                            <div class="actions">
                                <button class="btn btn-danger" type="submit" onclick="return confirm('Delete this role?')">Delete</button>
                            </div>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="hint">No roles found for selected company.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
