<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Settings - Roles & Permissions</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f3f2f1; color: #323130; }
        .wrap { max-width: 980px; margin: 24px auto; padding: 0 16px; }
        .card { background: #fff; border: 1px solid #edebe9; border-radius: 4px; padding: 20px; margin-bottom: 16px; }
        .row { display: flex; gap: 12px; flex-wrap: wrap; align-items: end; }
        .field { min-width: 220px; flex: 1; }
        label { display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#605e5c; }
        input, select { width:100%; border:1px solid #8a8886; border-radius:2px; padding:8px; }
        .btn { background:#0078d4; color:#fff; border:1px solid #0078d4; border-radius:2px; padding:8px 14px; cursor:pointer; }
        table { width:100%; border-collapse: collapse; }
        th, td { text-align:left; padding:10px 8px; border-bottom:1px solid #edebe9; }
        .ok { background:#dff6dd; border:1px solid #9fd89f; color:#107c10; padding:10px 12px; border-radius:2px; margin-bottom:12px; }
        .hint { color:#605e5c; font-size:13px; }
    </style>
</head>
<body>
@include('partials.global-company-selector')
<div class="wrap">
    <div class="card">
        <h2>Roles & Permissions</h2>
        <p class="hint">Set role while creating user. <strong>Admin</strong> gets Dashboard + Modules + Masters + Settings. <strong>User</strong> gets Dashboard + Modules only.</p>
        @if(session('status'))
            <div class="ok">{{ session('status') }}</div>
        @endif
        <form method="post" action="{{ route('settings.roles-permissions.user-account.store') }}">
            @csrf
            <div class="row">
                <div class="field">
                    <label>Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="field">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="field">
                    <label>Password (optional)</label>
                    <input type="text" name="password">
                </div>
                <div class="field">
                    <label>Role</label>
                    <select name="role" required>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>
                <div><button class="btn" type="submit">Save User</button></div>
            </div>
        </form>
    </div>

    <div class="card">
        <h3>Existing Users</h3>
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Role</th></tr></thead>
            <tbody>
            @foreach($users as $u)
                <tr>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td>{{ $u->is_super_admin ? 'Admin' : 'User' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
