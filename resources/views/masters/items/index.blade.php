<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Items Master</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: "Segoe UI", Arial, sans-serif; background: #f3f2f1; color: #323130; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #fff; border-right: 1px solid #edebe9; padding: 12px 0; flex-shrink: 0; }
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
        .toolbar { margin-bottom: 12px; }
        .toolbar-row { display: flex; justify-content: flex-start; align-items: center; gap: 12px; }
        .title { margin: 0 0 4px; font-size: 24px; font-weight: 600; }
        .card { background: #fff; border: 1px solid #edebe9; border-radius: 2px; margin-bottom: 12px; overflow: hidden; }
        .card-head { padding: 12px 14px; border-bottom: 1px solid #edebe9; font-size: 20px; font-weight: 600; }
        .card-body { padding: 14px; }
        .form-row { display: grid; grid-template-columns: repeat(4, minmax(160px, 1fr)); gap: 10px; margin-bottom: 10px; }
        .form-row label { display: block; font-size: 12px; color: #605e5c; font-weight: 600; margin-bottom: 4px; }
        .form-row input, .form-row select { width: 100%; padding: 8px; border: 1px solid #8a8886; border-radius: 2px; }
        .btn { background: #106ebe; color: #fff; border: 1px solid #106ebe; padding: 8px 12px; border-radius: 2px; cursor: pointer; }
        .status { background: #e8f6ee; color: #1f7a48; padding: 10px; border-radius: 2px; margin-bottom: 10px; }
        .error { background: #fde7e9; color: #a4262c; padding: 10px; border-radius: 2px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; border-bottom: 1px solid #edebe9; padding: 10px 8px; }
        th { color: #605e5c; background: #faf9f8; font-weight: 600; }
        .empty { text-align: center; color: #8a8886; padding: 24px 8px; }
    </style>
    @include('settings.rbac.partials.styles')
</head>
<body>
    @include('partials.global-company-selector')
    @php
        $companyQuery = !empty($currentCompanyCode) ? ['company' => strtoupper((string) $currentCompanyCode)] : [];
    @endphp
    @include('settings.rbac.partials.sidebar')
    <main class="main">
        <div class="page-shell">
            <div class="command-bar">
                <div class="crumb">Masters / Items</div>
            </div>
            <div style="padding:12px;">
                <div class="toolbar">
                    <div class="toolbar-row">
                        <div><h1 class="title">Items</h1></div>
                    </div>
                </div>

                <div class="card">
                    @if(session('status'))
                        <div class="card-body">
                            <div class="status">{{ session('status') }}</div>
                        </div>
                    @endif
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item ID</th>
                                <th>Item Name</th>
                                <th>Type</th>
                                <th>Item Category</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $idx => $item)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $item->item_id ?: $item->d365_id ?: $item->d365_item_id }}</td>
                                    <td>{{ $item->item_name }}</td>
                                    <td>{{ $item->type ?: '—' }}</td>
                                    <td>{{ $item->item_category_id ?: '—' }}</td>
                                    <td>{{ optional($item->created_at)->format('d M Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="empty">No items synced yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
