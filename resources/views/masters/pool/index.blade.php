<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pool Master</title>
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
        .empty-note {
            text-align: center;
            color: #8a8886;
            padding: 16px 10px;
            font-size: 13px;
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
                    <label for="pool_id">Pool ID</label>
                    <input id="pool_id" name="pool_id" type="text" maxlength="100" required placeholder="e.g. POOL001">
                </div>
                <div>
                    <label for="name">Pool Name</label>
                    <input id="name" name="name" type="text" maxlength="255" required placeholder="e.g. Main Consumption Pool">
                </div>
                <div>
                    <label for="company_id">Company ID</label>
                    <input id="company_id" name="company_id" type="text" maxlength="100" required value="{{ strtoupper((string) request('company', '')) }}" placeholder="e.g. USMF">
                </div>
                <button type="submit">Save pool</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Pools</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pool ID</th>
                    <th>Name</th>
                    <th>Company ID</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6">Loading pools...</td>
                </tr>
            </tbody>
        </table>
        <a class="back-link" href="{{ route('dashboard') }}">Back to Dashboard</a>
    </div>

    <script>
        const poolsTbody = document.querySelector('tbody');
        const poolsApiUrl = "{{ url('/masters/api/pools') }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const globalCompanySelect = document.getElementById('global-company-select');
        const companyIdInput = document.getElementById('company_id');

        const defaultHeaders = {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        };

        const currentCompanyId = () => {
            const fromGlobal = (globalCompanySelect?.value || '').trim().toUpperCase();
            if (fromGlobal) return fromGlobal;
            const fromUrl = new URLSearchParams(window.location.search).get('company');
            if (fromUrl) return fromUrl.trim().toUpperCase();
            return (companyIdInput?.value || '').trim().toUpperCase();
        };

        const syncCompanyFieldFromGlobal = () => {
            const code = currentCompanyId();
            if (code && companyIdInput) {
                companyIdInput.value = code;
            }
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

        const loadPools = async () => {
            syncCompanyFieldFromGlobal();
            const companyId = currentCompanyId();
            if (!companyId) {
                poolsTbody.innerHTML = '<tr><td colspan="6" class="empty-note">Select a company (top right) to view pools for that company.</td></tr>';
                return;
            }
            poolsTbody.innerHTML = '<tr><td colspan="6">Loading pools...</td></tr>';
            try {
                const response = await fetch(`${poolsApiUrl}?company_id=${encodeURIComponent(companyId)}`, { headers: defaultHeaders });
                if (!response.ok) throw new Error('Failed to load pools');
                const payload = await response.json();
                const pools = payload.data || [];

                if (!pools.length) {
                    poolsTbody.innerHTML = '<tr><td colspan="6">No pools found.</td></tr>';
                    return;
                }

                poolsTbody.innerHTML = pools.map((pool, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${escapeHtml(pool.pool_id ?? '-')}</td>
                        <td>${escapeHtml(pool.name ?? '-')}</td>
                        <td>${escapeHtml(pool.company_id ?? '-')}</td>
                        <td>${formatDate(pool.created_at)}</td>
                        <td><button class="danger" data-id="${pool.id}">Delete</button></td>
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

            const poolId = document.getElementById('pool_id').value.trim();
            const poolName = document.getElementById('name').value.trim();
            const companyId = currentCompanyId();
            if (!companyId) {
                setFormMessage(errEl, 'Select a company before saving a pool.', true);
                return;
            }

            try {
                const response = await fetch(poolsApiUrl, {
                    method: 'POST',
                    headers: defaultHeaders,
                    body: JSON.stringify({
                        pool_id: poolId,
                        name: poolName,
                        company_id: companyId,
                    }),
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok || payload.status === false) {
                    const msg = payload.message || (payload.errors ? JSON.stringify(payload.errors) : 'Save failed');
                    setFormMessage(errEl, msg, true);
                    return;
                }

                document.getElementById('pool_id').value = '';
                document.getElementById('name').value = '';
                setFormMessage(statusEl, payload.message || 'Pool created.', true);
                await loadPools();
            } catch {
                setFormMessage(errEl, 'Network error.', true);
            }
        });

        poolsTbody.addEventListener('click', async (event) => {
            if (!event.target.matches('.danger')) return;
            const id = event.target.getAttribute('data-id');
            if (!window.confirm('Delete this pool?')) return;

            try {
                const response = await fetch(`${poolsApiUrl}/${id}`, {
                    method: 'DELETE',
                    headers: defaultHeaders,
                });

                if (!response.ok) throw new Error('Delete failed');
                await loadPools();
            } catch {
                window.alert('Failed to delete pool.');
            }
        });

        if (globalCompanySelect) {
            globalCompanySelect.addEventListener('change', () => {
                syncCompanyFieldFromGlobal();
                loadPools();
            });
        }

        syncCompanyFieldFromGlobal();
        loadPools();
    </script>
</body>
</html>
