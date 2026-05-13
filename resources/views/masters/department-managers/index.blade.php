<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Department Manager Master</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f3f2f1;
            color: #323130;
            min-height: 100vh;
        }
        .main { padding: 12px 16px; overflow: auto; }
        .title { margin: 0 0 12px; font-size: 24px; font-weight: 600; }
        .page-shell { border: 1px solid #edebe9; background: #fff; border-radius: 2px; overflow: hidden; }
        .command-bar { height: 44px; border-bottom: 1px solid #edebe9; background: #fff; display: flex; align-items: center; padding: 0 12px; }
        .crumb { font-size: 12px; color: #605e5c; }
        .card { background: #fff; border: 1px solid #edebe9; border-radius: 2px; padding: 14px; }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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
        button {
            background: #106ebe;
            color: #fff;
            border: 1px solid #106ebe;
            padding: 8px 12px;
            border-radius: 2px;
            cursor: pointer;
        }
        .danger {
            background: #a4262c;
            border-color: #a4262c;
            padding: 6px 10px;
            font-size: 12px;
        }
        .status {
            background: #e8f6ee;
            color: #1f7a48;
            padding: 10px;
            border-radius: 2px;
            margin-bottom: 12px;
            display: none;
        }
        .error {
            color: #a4262c;
            font-size: 13px;
            margin-bottom: 12px;
            display: none;
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
        .empty-note {
            text-align: center;
            color: #8a8886;
            padding: 16px 10px;
            font-size: 13px;
        }
    </style>
    @include('settings.rbac.partials.styles')
</head>
<body>
    @include('partials.global-company-selector')
    @php
        $companyCode = strtoupper((string) request()->query('company', ''));
        $companyQuery = $companyCode !== '' ? ['company' => $companyCode] : [];
    @endphp
    @include('settings.rbac.partials.sidebar')
    <main class="main">
        <div class="page-shell">
            <div class="command-bar">
                <div class="crumb">Masters / Department Managers</div>
            </div>
            <div style="padding:12px;">
                <h1 class="title">Department Manager Master</h1>
                <div class="card">
                    <div id="form-status" class="status"></div>
                    <div id="form-errors" class="error"></div>
                    <div class="form-row">
                        <div>
                            <label for="employee_name">Employee Name</label>
                            <input id="employee_name" name="employee_name" type="text" placeholder="e.g. John Doe" required>
                        </div>
                        <div>
                            <label for="department">Department</label>
                            <input id="department" name="department" type="text" placeholder="e.g. Procurement" required>
                        </div>
                        <div>
                            <label for="company_id">Company</label>
                            <input id="company_id" name="company_id" type="text" value="{{ strtoupper((string) request('company', '')) }}" placeholder="e.g. USMF" required>
                        </div>
                    </div>
                    <div>
                        <button id="save-btn" type="button">Save Department Manager</button>
                    </div>
                </div>
                <div class="card" style="margin-top:12px;">
                    <h2 style="margin:0 0 10px;">Department Managers</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Employee Name</th>
                                <th>Department</th>
                                <th>Company</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="managers-body">
                            <tr><td colspan="5">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <script>
        const managersBody = document.getElementById('managers-body');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const apiUrl = "{{ url('/masters/api/department-managers') }}";
        const globalCompanySelect = document.getElementById('global-company-select');
        const companyInput = document.getElementById('company_id');
        const employeeInput = document.getElementById('employee_name');
        const departmentInput = document.getElementById('department');
        const statusEl = document.getElementById('form-status');
        const errorEl = document.getElementById('form-errors');

        const defaultHeaders = {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        };

        const setMessage = (el, text, show) => {
            el.textContent = text;
            el.style.display = show ? 'block' : 'none';
        };

        const currentCompany = () => {
            const fromGlobal = (globalCompanySelect?.value || '').trim().toUpperCase();
            if (fromGlobal) return fromGlobal;
            const fromInput = (companyInput?.value || '').trim().toUpperCase();
            if (fromInput) return fromInput;
            const fromUrl = new URLSearchParams(window.location.search).get('company');
            return (fromUrl || '').trim().toUpperCase();
        };

        const syncCompanyField = () => {
            const code = currentCompany();
            if (code && companyInput) {
                companyInput.value = code;
            }
        };

        const escapeHtml = (s) => {
            const d = document.createElement('div');
            d.textContent = s ?? '';
            return d.innerHTML;
        };

        const loadManagers = async () => {
            syncCompanyField();
            const companyId = currentCompany();
            if (!companyId) {
                managersBody.innerHTML = '<tr><td colspan="5" class="empty-note">Select a company to view department managers.</td></tr>';
                return;
            }

            managersBody.innerHTML = '<tr><td colspan="5">Loading...</td></tr>';
            try {
                const response = await fetch(`${apiUrl}?company_id=${encodeURIComponent(companyId)}`, { headers: defaultHeaders });
                if (!response.ok) throw new Error('Failed to load managers');
                const payload = await response.json();
                const rows = payload.data || [];
                if (!rows.length) {
                    managersBody.innerHTML = '<tr><td colspan="5" class="empty-note">No department managers found.</td></tr>';
                    return;
                }

                managersBody.innerHTML = rows.map((row, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${escapeHtml(row.employee_name ?? '-')}</td>
                        <td>${escapeHtml(row.department ?? '-')}</td>
                        <td>${escapeHtml(row.company_id ?? '-')}</td>
                        <td><button type="button" class="danger" data-id="${row.id}">Delete</button></td>
                    </tr>
                `).join('');
            } catch {
                managersBody.innerHTML = '<tr><td colspan="5">Failed to load department managers.</td></tr>';
            }
        };

        document.getElementById('save-btn').addEventListener('click', async () => {
            setMessage(statusEl, '', false);
            setMessage(errorEl, '', false);

            const employeeName = employeeInput.value.trim();
            const department = departmentInput.value.trim();
            const companyId = currentCompany();
            if (!employeeName || !department || !companyId) {
                setMessage(errorEl, 'Employee Name, Department, and Company are required.', true);
                return;
            }

            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: defaultHeaders,
                    body: JSON.stringify({
                        employee_name: employeeName,
                        department: department,
                        company_id: companyId,
                    }),
                });
                const payload = await response.json().catch(() => ({}));
                if (!response.ok || payload.status === false) {
                    const msg = payload.message || (payload.errors ? JSON.stringify(payload.errors) : 'Save failed.');
                    setMessage(errorEl, msg, true);
                    return;
                }

                employeeInput.value = '';
                departmentInput.value = '';
                setMessage(statusEl, payload.message || 'Department manager saved.', true);
                await loadManagers();
            } catch {
                setMessage(errorEl, 'Network error.', true);
            }
        });

        managersBody.addEventListener('click', async (event) => {
            const btn = event.target.closest('.danger');
            if (!btn) return;
            const id = btn.getAttribute('data-id');
            if (!window.confirm('Delete this department manager?')) return;
            try {
                const response = await fetch(`${apiUrl}/${id}`, {
                    method: 'DELETE',
                    headers: defaultHeaders,
                });
                if (!response.ok) throw new Error('Delete failed.');
                await loadManagers();
            } catch {
                setMessage(errorEl, 'Failed to delete department manager.', true);
            }
        });

        if (globalCompanySelect) {
            globalCompanySelect.addEventListener('change', () => {
                syncCompanyField();
                loadManagers();
            });
        }

        syncCompanyField();
        loadManagers();
    </script>
</body>
</html>
