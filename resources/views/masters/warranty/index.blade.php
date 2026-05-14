<!DOCTYPE html>
<html>
<head>
    <title>Warranty</title>
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            background: #f3f2f1;
            color: #323130;
            min-height: 100vh;
        }
        .main { padding: 12px 16px; overflow: auto; }
        .page-shell { border: 1px solid #edebe9; background: #fff; border-radius: 2px; overflow: hidden; }
        .command-bar { height: 44px; border-bottom: 1px solid #edebe9; background: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 12px; }
        .crumb { font-size: 12px; color: #605e5c; }
        .title { margin: 0 0 12px; font-size: 24px; font-weight: 600; }
        .card { background: white; border-radius: 2px; border: 1px solid #edebe9; overflow: hidden; }
        .card-head { padding: 12px 14px; border-bottom: 1px solid #edebe9; font-size: 20px; font-weight: 600; }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }
        label { display: block; font-size: 14px; margin-bottom: 4px; font-weight: 600; }
        input, select {
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
    @include('settings.rbac.partials.styles')
</head>
<body>
    @include('partials.global-company-selector')
    @php
        $companyCode = $effectiveCompanyCode ?? ($globalSelectedCompany ?? '');
        $companyCode = strtoupper(trim((string) $companyCode));
        $companyQuery = $companyCode !== '' ? ['company' => $companyCode] : [];
    @endphp
    @include('settings.rbac.partials.sidebar')
    <main class="main">
        <div class="page-shell">
            <div class="command-bar">
                <div class="crumb">Masters / Warranty</div>
            </div>
            <div style="padding:12px;">
                <h1 class="title">Warranty Master</h1>
                @if ($companyCode !== '')
                    <p style="margin: -8px 0 16px; font-size: 14px; color: #605e5c;">Company: <strong>{{ $companyCode }}</strong> — only warranty rows for this company are listed and can be added here.</p>
                @else
                    <p style="margin: -8px 0 16px; font-size: 14px; color: #a4262c;">Choose a company in the header to manage warranty.</p>
                @endif

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
                    <div class="card-head">Add warranty</div>
                    <div style="padding: 12px;">
                        @if ($companyCode !== '')
                        <form method="post" action="{{ route('masters.warranty.store', $companyQuery) }}">
                            @csrf
                            <div class="form-row">
                                <div>
                                    <label for="warranty">Warranty code</label>
                                    <input id="warranty" name="warranty" type="text" value="{{ old('warranty') }}" required maxlength="100" placeholder="e.g. 1D, 24D">
                                </div>
                                <div>
                                    <label for="description">Description</label>
                                    <input id="description" name="description" type="text" value="{{ old('description') }}" maxlength="255" placeholder="e.g. 1 day, 24 days">
                                </div>
                            </div>
                            <button class="btn-primary" type="submit">Save</button>
                        </form>
                        @else
                        <p style="margin:0; color: #605e5c;">Select a company above to add warranty rows.</p>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-head">Warranty</div>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $index => $row)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $row->warranty }}</td>
                                    <td>{{ $row->description ?? '—' }}</td>
                                    <td>{{ optional($row->created_at)->format('d M Y H:i') }}</td>
                                    <td>
                                        <form method="post" action="{{ route('masters.warranty.destroy', array_merge($companyQuery, ['warranty' => $row->id])) }}" onsubmit="return confirm('Delete this row?');" style="margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn-delete" type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">No warranty rows for this company.</td>
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
