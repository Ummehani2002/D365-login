<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Item Sales Tax Group Master</title>
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
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
            margin-bottom: 12px;
            align-items: end;
        }
        label {
            display: block;
            font-size: 14px;
            margin-bottom: 4px;
            font-weight: 600;
        }
        input {
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
        .error {
            color: #a4262c;
            font-size: 13px;
            margin-top: 4px;
        }
        .status {
            background: #e8f6ee;
            color: #1f7a48;
            padding: 10px;
            border-radius: 2px;
            margin-bottom: 12px;
        }
        .back-link {
            text-decoration: none;
            display: inline-block;
            margin-top: 12px;
        }
        .danger {
            background: #a4262c;
            border-color: #a4262c;
            padding: 6px 10px;
            font-size: 12px;
        }
    </style>
    @include('settings.rbac.partials.styles')
</head>
<body>
    @include('partials.global-company-selector')
    @php
        $companyCode = strtoupper((string) ($currentCompanyCode ?? request()->query('company', '')));
        $companyQuery = $companyCode !== '' ? ['company' => $companyCode] : [];
    @endphp
    @include('settings.rbac.partials.sidebar')
    <main class="main">
    <div class="header">
        <h1>Item Sales Tax Group Master</h1>
    </div>

    <div class="card">
        <h2>Add item sales tax group</h2>
        <div id="form-status" class="status" style="display:none;"></div>
        <div id="form-errors" class="error" style="display:none;"></div>
        <form id="group-form">
            <div class="form-row">
                <div>
                    <label for="tax_item_group">Tax item group</label>
                    <input id="tax_item_group" name="tax_item_group" type="text" maxlength="100" required placeholder="Unique code">
                </div>
                <div>
                    <label for="tax_group_name">Tax group name</label>
                    <input id="tax_group_name" name="tax_group_name" type="text" maxlength="255" required placeholder="Display name">
                </div>
                <button type="submit">Save</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Item sales tax groups</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tax item group</th>
                    <th>Tax group name</th>
                    <th>Created at</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5">Loading...</td>
                </tr>
            </tbody>
        </table>
        <a class="back-link" href="{{ route('dashboard', $companyQuery) }}">Back to Dashboard</a>
    </div>
    </main>

    <script>
        const tbody = document.querySelector('tbody');
        const companyCode = "{{ $companyCode }}";
        const apiBaseUrl = "{{ url('/masters/api/item-sales-tax-groups') }}";
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

        const formatDate = (value) => {
            if (!value) return '-';
            return new Date(value).toLocaleString();
        };

        const setFormMessage = (el, text, show) => {
            el.textContent = text;
            el.style.display = show ? 'block' : 'none';
        };

        const escapeHtml = (s) => {
            const d = document.createElement('div');
            d.textContent = s ?? '';
            return d.innerHTML;
        };

        const loadGroups = async () => {
            tbody.innerHTML = '<tr><td colspan="5">Loading...</td></tr>';
            try {
                const response = await fetch(apiUrl, { headers: defaultHeaders });
                if (!response.ok) throw new Error('Failed to load');
                const payload = await response.json();
                const rows = payload.data || [];

                if (!rows.length) {
                    tbody.innerHTML = '<tr><td colspan="5">No records found.</td></tr>';
                    return;
                }

                tbody.innerHTML = rows.map((row, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${escapeHtml(row.tax_item_group ?? '-')}</td>
                        <td>${escapeHtml(row.tax_group_name ?? '-')}</td>
                        <td>${formatDate(row.created_at)}</td>
                        <td><button class="danger" data-id="${row.id}">Delete</button></td>
                    </tr>
                `).join('');
            } catch {
                tbody.innerHTML = '<tr><td colspan="5">Failed to load.</td></tr>';
            }
        };

        document.getElementById('group-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const statusEl = document.getElementById('form-status');
            const errEl = document.getElementById('form-errors');
            setFormMessage(errEl, '', false);
            setFormMessage(statusEl, '', false);

            const tax_item_group = document.getElementById('tax_item_group').value.trim();
            const tax_group_name = document.getElementById('tax_group_name').value.trim();

            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: defaultHeaders,
                    body: JSON.stringify({
                        company_id: companyCode,
                        tax_item_group,
                        tax_group_name,
                    }),
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok || payload.status === false) {
                    const msg = payload.message || (payload.errors ? JSON.stringify(payload.errors) : 'Save failed');
                    setFormMessage(errEl, msg, true);
                    return;
                }

                document.getElementById('tax_item_group').value = '';
                document.getElementById('tax_group_name').value = '';
                setFormMessage(statusEl, payload.message || 'Saved.', true);
                await loadGroups();
            } catch {
                setFormMessage(errEl, 'Network error.', true);
            }
        });

        tbody.addEventListener('click', async (event) => {
            if (!event.target.matches('.danger')) return;
            const id = event.target.getAttribute('data-id');
            if (!window.confirm('Delete this record?')) return;

            try {
                const response = await fetch(`${apiBaseUrl}/${id}`, {
                    method: 'DELETE',
                    headers: defaultHeaders,
                });

                if (!response.ok) throw new Error('Delete failed');
                await loadGroups();
            } catch {
                window.alert('Failed to delete.');
            }
        });

        loadGroups();
    </script>
</body>
</html>
