<!DOCTYPE html>
<html>
<head>
    <title>Pool Master</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-bearer-token" content="{{ $apiBearerToken }}">
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
            background: white;
            border-radius: 2px;
            border: 1px solid #edebe9;
            padding: 14px;
            margin-bottom: 12px;
        }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }
        label {
            display: block;
            font-size: 14px;
            margin-bottom: 4px;
            font-weight: 600;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #8a8886;
            border-radius: 2px;
            box-sizing: border-box;
        }
        button {
            background: #106ebe;
            color: white;
            border: 1px solid #106ebe;
            padding: 8px 12px;
            border-radius: 2px;
            cursor: pointer;
        }
        button.secondary {
            background: #fff;
            color: #323130;
            border-color: #8a8886;
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
            color: #c0392b;
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
        .action-btn {
            background: #a4262c;
            padding: 6px 10px;
            border-radius: 2px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    @include('partials.global-company-selector')
    <div class="header">
        <h1>Pool Master</h1>
    </div>

    <div class="card">
        <h2>Add pool</h2>
        <div id="form-status" class="status" style="display:none;"></div>
        <div id="form-errors" class="error" style="display:none;"></div>
        <form id="pool-form">
            <div class="form-row">
                <div>
                    <label for="company_id">Company</label>
                    <select id="company_id" name="company_id" required>
                        <option value="">Loading companies…</option>
                    </select>
                </div>
                <div>
                    <label for="d365_pool_id">Pool ID</label>
                    <input id="d365_pool_id" name="d365_pool_id" type="text" maxlength="100" required placeholder="Pool ID">
                </div>
                <div>
                    <label for="name">Pool name</label>
                    <input id="name" name="name" type="text" maxlength="255" required placeholder="Pool name">
                </div>
            </div>
            <button type="submit">Save pool</button>
        </form>
    </div>

    <div class="card">
        <h2>Filter by company</h2>
        <div class="form-row">
            <div>
                <label for="filter_company">Company</label>
                <select id="filter_company">
                    <option value="">All companies</option>
                </select>
            </div>
            <div style="display:flex;align-items:flex-end;">
                <button type="button" class="secondary" id="apply-filter">Apply filter</button>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Pools</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Company</th>
                    <th>Pool ID</th>
                    <th>Name</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" id="pools-loading">Loading pools...</td>
                </tr>
            </tbody>
        </table>
        <a class="back-link" href="{{ route('dashboard') }}">Back to Dashboard</a>
    </div>
    <script>
        const poolsTbody = document.querySelectorAll('tbody')[0];
        const companySelect = document.getElementById('company_id');
        const filterSelect = document.getElementById('filter_company');
        const poolsApiUrl = '/api/pools';
        const companiesApiUrl = '/api/companies';
        const apiBearerToken = document.querySelector('meta[name="api-bearer-token"]')?.content ?? '';
        const defaultHeaders = {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            Authorization: `Bearer ${apiBearerToken}`,
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

        const loadCompaniesForSelects = async () => {
            try {
                const response = await fetch(companiesApiUrl, { headers: defaultHeaders });
                if (!response.ok) throw new Error('companies');
                const payload = await response.json();
                const companies = payload.data || [];
                const options = companies.length
                    ? companies.map((c) => `<option value="${escapeHtml(c.company_id ?? '')}">${escapeHtml(c.name)} (${escapeHtml(c.company_id ?? '')})</option>`).join('')
                    : '<option value="">No companies — create in Company Master first</option>';
                companySelect.innerHTML = '<option value="">Select company</option>' + options;
                filterSelect.innerHTML = '<option value="">All companies</option>' + companies.map((c) =>
                    `<option value="${escapeHtml(c.company_id ?? '')}">${escapeHtml(c.name)} (${escapeHtml(c.company_id ?? '')})</option>`).join('');
            } catch {
                companySelect.innerHTML = '<option value="">Failed to load companies</option>';
            }
        };

        const loadPools = async () => {
            poolsTbody.innerHTML = '<tr><td colspan="6">Loading pools...</td></tr>';
            const companyId = filterSelect.value;
            const url = companyId ? `${poolsApiUrl}?company_id=${encodeURIComponent(companyId)}` : poolsApiUrl;

            try {
                const response = await fetch(url, { headers: defaultHeaders });
                if (!response.ok) throw new Error('pools');
                const payload = await response.json();
                const pools = payload.data || [];

                if (!pools.length) {
                    poolsTbody.innerHTML = '<tr><td colspan="6">No pools found. Add one above.</td></tr>';
                    return;
                }

                poolsTbody.innerHTML = pools.map((p, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${escapeHtml(p.company_id ?? '-')}</td>
                        <td>${escapeHtml(p.d365_pool_id ?? '-')}</td>
                        <td>${escapeHtml(p.name ?? '-')}</td>
                        <td>${formatDate(p.created_at)}</td>
                        <td>
                            <button class="action-btn" data-id="${p.id}">Delete</button>
                        </td>
                    </tr>
                `).join('');
            } catch {
                poolsTbody.innerHTML = '<tr><td colspan="6">Failed to load pools.</td></tr>';
            }
        };

        document.getElementById('pool-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const statusEl = document.getElementById('form-status');
            const errEl = document.getElementById('form-errors');
            setFormMessage(errEl, '', false);
            setFormMessage(statusEl, '', false);

            const body = {
                company_id: companySelect.value.trim(),
                d365_pool_id: document.getElementById('d365_pool_id').value.trim(),
                name: document.getElementById('name').value.trim(),
            };

            if (!body.company_id) {
                setFormMessage(errEl, 'Please select a company.', true);
                return;
            }

            try {
                const response = await fetch(poolsApiUrl, {
                    method: 'POST',
                    headers: defaultHeaders,
                    body: JSON.stringify(body),
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const msg = payload.message || (payload.errors ? JSON.stringify(payload.errors) : 'Save failed');
                    setFormMessage(errEl, msg, true);
                    return;
                }

                setFormMessage(statusEl, 'Pool created.', true);
                document.getElementById('d365_pool_id').value = '';
                document.getElementById('name').value = '';
                await loadPools();
            } catch {
                setFormMessage(errEl, 'Network error.', true);
            }
        });

        document.getElementById('apply-filter').addEventListener('click', loadPools);

        poolsTbody.addEventListener('click', async (event) => {
            if (!event.target.matches('.action-btn')) return;
            const poolId = event.target.getAttribute('data-id');
            if (!window.confirm('Delete this pool?')) return;

            try {
                const response = await fetch(`${poolsApiUrl}/${poolId}`, {
                    method: 'DELETE',
                    headers: defaultHeaders,
                });
                if (!response.ok) throw new Error('delete');
                await loadPools();
            } catch {
                window.alert('Failed to delete pool.');
            }
        });

        (async () => {
            await loadCompaniesForSelects();
            await loadPools();
        })();
    </script>
</body>
</html>
