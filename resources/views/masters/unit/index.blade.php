<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Unit Master (per item)</title>
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            padding: 16px;
            background: #f3f2f1;
            color: #323130;
        }
        .header {
            background: #fff;
            color: #201f1e;
            padding: 14px 16px;
            border: 1px solid #edebe9;
            border-radius: 2px;
            margin-bottom: 12px;
        }
        .card {
            background: #fff;
            border: 1px solid #edebe9;
            border-radius: 2px;
            padding: 14px;
            margin-bottom: 12px;
        }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-bottom: 12px;
            align-items: end;
        }
        label { display: block; font-size: 14px; margin-bottom: 4px; font-weight: 600; }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #8a8886;
            border-radius: 2px;
            box-sizing: border-box;
        }
        button {
            background: #106ebe;
            color: #fff;
            border: 1px solid #106ebe;
            padding: 8px 12px;
            border-radius: 2px;
            cursor: pointer;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; border-bottom: 1px solid #edebe9; padding: 10px 8px; }
        th { color: #605e5c; background: #faf9f8; font-weight: 600; }
        .error { color: #a4262c; font-size: 13px; margin-top: 4px; }
        .status { background: #e8f6ee; color: #1f7a48; padding: 10px; border-radius: 2px; margin-bottom: 12px; }
        .back-link { text-decoration: none; display: inline-block; margin-top: 12px; }
        .danger { background: #a4262c; border-color: #a4262c; padding: 6px 10px; font-size: 12px; }
        .hint { font-size: 12px; color: #605e5c; margin-top: 4px; }
    </style>
</head>
<body>
    @include('partials.global-company-selector')
    @php
        $companyQuery = !empty($currentCompanyCode) ? ['company' => strtoupper((string) $currentCompanyCode)] : [];
    @endphp
    <div class="header">
        <h1>Unit Master</h1>
       
    </div>

    <div class="card">
        <h2>Add / update unit for an item</h2>
        <div id="form-status" class="status" style="display:none;"></div>
        <div id="form-errors" class="error" style="display:none;"></div>
        <form id="unit-form">
            <div class="form-row">
                <div>
                    <label for="item_id">Item (from Item master)</label>
                    <select id="item_id" name="item_id" required>
                        <option value="">— Select item —</option>
                        @foreach($items as $it)
                            @php($code = $it->d365_id ?? $it->d365_item_id ?? '')
                            <option value="{{ $it->id }}">{{ $it->id }} — {{ $code }} — {{ $it->item_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="unit_id">Unit id</label>
                    <input id="unit_id" name="unit_id" type="text" maxlength="50" required placeholder="e.g. EA">
                </div>
                <div>
                    <label for="unit_name">Unit name</label>
                    <input id="unit_name" name="unit_name" type="text" maxlength="255" required placeholder="e.g. Each">
                </div>
                <button type="submit">Save</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Item units</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item id</th>
                    <th>Item code</th>
                    <th>Unit id</th>
                    <th>Unit name</th>
                    <th>Definition</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="7">Loading…</td></tr>
            </tbody>
        </table>
        <a class="back-link" href="{{ route('dashboard', $companyQuery) }}">Back to Dashboard</a>
    </div>

    <script>
        const tbody = document.querySelector('tbody');
        const companyCode = "{{ strtoupper((string) ($currentCompanyCode ?? request()->query('company', ''))) }}";
        const apiBaseUrl = "{{ url('/masters/api/item-units') }}";
        const apiUrl = new URL(apiBaseUrl);
        if (companyCode) {
            apiUrl.searchParams.set('company', companyCode);
        }
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        const defaultHeaders = {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        };

        const escapeHtml = (s) => {
            const d = document.createElement('div');
            d.textContent = s ?? '';
            return d.innerHTML;
        };

        const setFormMessage = (el, text, show) => {
            el.textContent = text;
            el.style.display = show ? 'block' : 'none';
        };

        const loadRows = async () => {
            tbody.innerHTML = '<tr><td colspan="7">Loading…</td></tr>';
            try {
                const response = await fetch(apiUrl, { headers: defaultHeaders });
                if (!response.ok) throw new Error('Failed to load');
                const payload = await response.json();
                const rows = payload.data || [];
                if (!rows.length) {
                    tbody.innerHTML = '<tr><td colspan="7">No records found.</td></tr>';
                    return;
                }
                tbody.innerHTML = rows.map((row, index) => {
                    const item = row.item || {};
                    const code = item.d365_id || item.d365_item_id || '—';
                    return `<tr>
                        <td>${index + 1}</td>
                        <td>${escapeHtml(String(row.item_id ?? ''))}</td>
                        <td>${escapeHtml(String(code))}</td>
                        <td>${escapeHtml(row.unit_id ?? '')}</td>
                        <td>${escapeHtml(row.unit_name ?? '')}</td>
                        <td>${escapeHtml(row.definition ?? '—')}</td>
                        <td><button type="button" class="danger" data-id="${row.id}">Delete</button></td>
                    </tr>`;
                }).join('');
            } catch {
                tbody.innerHTML = '<tr><td colspan="7">Failed to load.</td></tr>';
            }
        };

        document.getElementById('unit-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const statusEl = document.getElementById('form-status');
            const errEl = document.getElementById('form-errors');
            setFormMessage(errEl, '', false);
            setFormMessage(statusEl, '', false);

            const item_id = parseInt(document.getElementById('item_id').value, 10);
            const unit_id = document.getElementById('unit_id').value.trim();
            const unit_name = document.getElementById('unit_name').value.trim();

            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: defaultHeaders,
                    body: JSON.stringify({
                        company_id: companyCode,
                        item_id,
                        unit_id,
                        unit_name
                    }),
                });
                const payload = await response.json().catch(() => ({}));
                if (!response.ok || payload.status === false) {
                    const msg = payload.message || (payload.errors ? JSON.stringify(payload.errors) : 'Save failed');
                    setFormMessage(errEl, msg, true);
                    return;
                }
                document.getElementById('unit_id').value = '';
                document.getElementById('unit_name').value = '';
                setFormMessage(statusEl, payload.message || 'Saved.', true);
                await loadRows();
            } catch {
                setFormMessage(errEl, 'Network error.', true);
            }
        });

        tbody.addEventListener('click', async (event) => {
            if (!event.target.matches('.danger')) return;
            const id = event.target.getAttribute('data-id');
            if (!window.confirm('Delete this record?')) return;
            try {
                const response = await fetch(`${apiBaseUrl}/${id}`, { method: 'DELETE', headers: defaultHeaders });
                if (!response.ok) throw new Error('Delete failed');
                await loadRows();
            } catch {
                window.alert('Failed to delete.');
            }
        });

        loadRows();
    </script>
</body>
</html>
