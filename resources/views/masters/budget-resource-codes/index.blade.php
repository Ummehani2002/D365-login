<!DOCTYPE html>
<html>
<head>
    <title>Budget Resource Codes</title>
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
</head>
<body>
    @include('partials.global-company-selector')
    @php
        $companyCode = strtoupper((string) request()->query('company', ''));
        $companyQuery = $companyCode !== '' ? ['company' => $companyCode] : [];
    @endphp
    <main class="main">
        <div class="page-shell">
            <div class="command-bar">
                <div class="crumb">Masters / Budget Resource Codes</div>
            </div>
            <div style="padding:12px;">
                <h1 class="title">Budget Resource Code Master</h1>
                <p style="margin:0 0 12px;font-size:13px;color:#605e5c;">Company ID scopes resource codes (same code may exist per company). Aligns with D365 Budget Resources.</p>

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
                    <div class="card-head">Add budget resource code</div>
                    <div style="padding: 12px;">
                        <form method="post" action="{{ route('masters.budget-resource-codes.store', $companyQuery) }}">
                            @csrf
                            <div class="form-row">
                                <div>
                                    <label for="company_id">Company ID (D365)</label>
                                    <input id="company_id" name="company_id" type="text" value="{{ old('company_id', $companyCode !== '' ? $companyCode : '') }}" required maxlength="100" placeholder="e.g. PS">
                                </div>
                                <div>
                                    <label for="resource_code">Resource code</label>
                                    <input id="resource_code" name="resource_code" type="text" value="{{ old('resource_code') }}" required maxlength="100" placeholder="D365 resource code">
                                </div>
                                <div>
                                    <label for="description">Description</label>
                                    <input id="description" name="description" type="text" value="{{ old('description') }}" maxlength="255">
                                </div>
                                <div>
                                    <label for="unit">Unit</label>
                                    <input id="unit" name="unit" type="text" value="{{ old('unit') }}" maxlength="30">
                                </div>
                                <div>
                                    <label for="resource_category">Category</label>
                                    <select id="resource_category" name="resource_category">
                                        <option value="">—</option>
                                        <option value="Materials" @selected(old('resource_category') === 'Materials')>Materials</option>
                                        <option value="Sub Contracts" @selected(old('resource_category') === 'Sub Contracts')>Sub Contracts</option>
                                        <option value="Plants" @selected(old('resource_category') === 'Plants')>Plants</option>
                                        <option value="Others" @selected(old('resource_category') === 'Others')>Others</option>
                                    </select>
                                </div>
                            </div>
                            <button class="btn-primary" type="submit">Save</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head">Budget resource codes</div>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Company ID</th>
                                <th>Resource code</th>
                                <th>Description</th>
                                <th>Unit</th>
                                <th>Category</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($budgetResourceCodes as $index => $row)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $row->company_id }}</td>
                                    <td>{{ $row->resource_code }}</td>
                                    <td>{{ $row->description ?? '—' }}</td>
                                    <td>{{ $row->unit ?? '—' }}</td>
                                    <td>{{ $row->resource_category ?? '—' }}</td>
                                    <td>{{ optional($row->created_at)->format('d M Y H:i') }}</td>
                                    <td>
                                        <form method="post" action="{{ route('masters.budget-resource-codes.destroy', array_merge($companyQuery, ['budgetResourceCode' => $row->id])) }}" onsubmit="return confirm('Delete this row?');" style="margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn-delete" type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">No budget resource codes found.</td>
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
