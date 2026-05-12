<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Item Categories Master</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: "Segoe UI", Arial, sans-serif; background: #f3f2f1; color: #323130; min-height: 100vh; }
        .main { padding: 12px 16px; overflow: auto; }
        .page-shell { border: 1px solid #edebe9; background: #fff; border-radius: 2px; overflow: hidden; }
        .command-bar { height: 44px; border-bottom: 1px solid #edebe9; background: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 12px; }
        .crumb { font-size: 12px; color: #605e5c; }
        .toolbar { margin-bottom: 12px; }
        .toolbar-row { display: flex; justify-content: flex-start; align-items: center; gap: 12px; }
        .title { margin: 0 0 4px; font-size: 24px; font-weight: 600; }
        .card { background: #fff; border: 1px solid #edebe9; border-radius: 2px; margin-bottom: 12px; overflow: hidden; }
        .card-head { padding: 12px 14px; border-bottom: 1px solid #edebe9; font-size: 20px; font-weight: 600; }
        .card-body { padding: 14px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; margin-bottom: 8px; max-width: 800px; }
        .form-row label { display: block; font-size: 12px; color: #605e5c; font-weight: 600; margin-bottom: 4px; }
        .form-row input, .form-row select { width: 100%; padding: 8px; border: 1px solid #8a8886; border-radius: 2px; }
        .btn { background: #106ebe; color: #fff; border: 1px solid #106ebe; padding: 8px 12px; border-radius: 2px; cursor: pointer; align-self: end; }
        .status { background: #e8f6ee; color: #1f7a48; padding: 10px; border-radius: 2px; margin-bottom: 10px; }
        .error { background: #fde7e9; color: #a4262c; padding: 10px; border-radius: 2px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; border-bottom: 1px solid #edebe9; padding: 10px 8px; }
        th { color: #605e5c; background: #faf9f8; font-weight: 600; }
        .empty { text-align: center; color: #8a8886; padding: 24px 8px; }
    </style>
</head>
<body>
    @include('partials.global-company-selector')
    @php
        $companyQuery = !empty($currentCompanyCode) ? ['company' => strtoupper((string) $currentCompanyCode)] : [];
    @endphp
    <main class="main">
        <div class="page-shell">
            <div class="command-bar">
                <div class="crumb">Masters / Item Categories</div>
            </div>
            <div style="padding:12px;">
                <div class="toolbar">
                    <div class="toolbar-row">
                        <div><h1 class="title">Item Categories</h1></div>
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
                                <th>Item Category ID</th>
                                <th>Category Name</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $idx => $category)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $category->item_category_id ?: $category->d365_id ?: '—' }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ optional($category->created_at)->format('d M Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="empty">No categories yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
