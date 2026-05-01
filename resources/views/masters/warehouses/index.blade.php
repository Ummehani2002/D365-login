<!DOCTYPE html>
<html>
<head>
    <title>Warehouse Master</title>
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            background: #f3f2f1;
            color: #323130;
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 260px;
            background: #fff;
            border-right: 1px solid #edebe9;
            padding: 12px 0;
            flex-shrink: 0;
        }
        .logo { padding: 10px 16px 18px; border-bottom: 1px solid #edebe9; margin-bottom: 8px; font-weight: 700; }
        .label { padding: 10px 16px 4px; color: #8a8886; font-size: 11px; text-transform: uppercase; }
        .menu-link { display: block; padding: 10px 16px; color: #323130; text-decoration: none; border-radius: 8px; margin: 2px 8px; font-size: 14px; }
        .menu-link:hover { background: #f3f2f1; }
        .menu-link.active { background: #deecf9; color: #005a9e; }
        .sub { margin-left: 16px; padding-left: 8px; border-left: 2px solid #edebe9; }
        .main { flex: 1; padding: 12px 16px; overflow: auto; }
        .page-shell { border: 1px solid #edebe9; background: #fff; border-radius: 2px; overflow: hidden; }
        .command-bar { height: 44px; border-bottom: 1px solid #edebe9; background: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 12px; }
        .crumb { font-size: 12px; color: #605e5c; }
        .title { margin: 0 0 12px; font-size: 24px; font-weight: 600; }
        .card { background: white; border-radius: 2px; border: 1px solid #edebe9; overflow: hidden; }
        .card-head { padding: 12px 14px; border-bottom: 1px solid #edebe9; font-size: 20px; font-weight: 600; }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }
        label { display: block; font-size: 14px; margin-bottom: 4px; font-weight: 600; }
        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #8a8886;
            border-radius: 2px;
            box-sizing: border-box;
        }
        .btn-primary {
            background: #005a9e;
            color: white;
            border: 1px solid #005a9e;
            padding: 8px 12px;
            border-radius: 2px;
            cursor: pointer;
            font-size: 13px;
        }
        .btn-delete {
            background: #a4262c;
            color: white;
            border: 1px solid #a4262c;
            padding: 6px 10px;
            border-radius: 2px;
            cursor: pointer;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            border-bottom: 1px solid #edebe9;
            padding: 10px 8px;
        }
        th {
            color: #605e5c;
            background: #faf9f8;
            font-weight: 600;
        }
        .status {
            background: #e8f6ee;
            color: #1f7a48;
            padding: 10px;
            border-radius: 2px;
            margin-bottom: 12px;
        }
        .errors {
            background: #fde7e9;
            color: #a4262c;
            padding: 10px;
            border-radius: 2px;
            margin-bottom: 12px;
        }
        .back-link { text-decoration: none; display: inline-block; margin-top: 12px; font-size: 13px; }
    </style>
</head>
<body>
    @include('partials.global-company-selector')
    @php
        $companyCode = strtoupper((string) request()->query('company', ''));
        $companyQuery = $companyCode !== '' ? ['company' => $companyCode] : [];
    @endphp
    <aside class="sidebar">
        <div class="logo">Logo</div>
        <div class="label">Menu</div>
        <a class="menu-link" href="{{ route('dashboard', $companyQuery) }}">Dashboard</a>
        <a class="menu-link active" href="{{ route('masters.company.index', $companyQuery) }}">Masters</a>
        <a class="menu-link" href="#">Modules</a>
        <div class="sub">
            <a class="menu-link" href="#">Project Management</a>
            <a class="menu-link" href="{{ route('modules.project-management.item-issue', $companyQuery) }}">Item Issue</a>
            <a class="menu-link" href="#">Procurement &amp; Sourcing</a>
            <div class="sub">
                <a class="menu-link" href="{{ route('modules.procurement.purch-req', $companyQuery) }}">Purchase Requisition</a>
                <a class="menu-link" href="{{ route('modules.procurement.grn', $companyQuery) }}">GRN</a>
            </div>
        </div>
        <a class="menu-link" href="{{ route('settings.index', $companyQuery) }}">Settings</a>
    </aside>
    <main class="main">
        <div class="page-shell">
            <div class="command-bar">
                <div class="crumb">Masters / Warehouses</div>
            </div>
            <div style="padding:12px;">
                <h1 class="title">Warehouse Master</h1>

                @if (session('status'))
                    <div class="status">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="errors">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="card" style="margin-bottom: 12px;">
                    <div class="card-head">Create Warehouse</div>
                    <div style="padding: 12px;">
                        <form method="post" action="{{ route('masters.warehouses.store', $companyQuery) }}">
                            @csrf
                            <div class="form-row">
                                <div>
                                    <label for="warehouse_id">Warehouse ID</label>
                                    <input id="warehouse_id" name="warehouse_id" type="text" value="{{ old('warehouse_id') }}" required>
                                </div>
                                <div>
                                    <label for="warehouse_name">Warehouse Name</label>
                                    <input id="warehouse_name" name="warehouse_name" type="text" value="{{ old('warehouse_name') }}" required>
                                </div>
                            </div>
                            <button class="btn-primary" type="submit">Save Warehouse</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head">Warehouses</div>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Warehouse ID</th>
                                <th>Warehouse Name</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($warehouses as $index => $warehouse)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $warehouse->warehouse_id }}</td>
                                    <td>{{ $warehouse->warehouse_name }}</td>
                                    <td>{{ optional($warehouse->created_at)->format('d M Y H:i') }}</td>
                                    <td>
                                        <form method="post" action="{{ route('masters.warehouses.destroy', array_merge($companyQuery, ['warehouse' => $warehouse->id])) }}" onsubmit="return confirm('Delete this warehouse?');" style="margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn-delete" type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">No warehouses found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div style="padding: 0 14px 12px;">
                        <a class="back-link" href="{{ route('dashboard', $companyQuery) }}">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
