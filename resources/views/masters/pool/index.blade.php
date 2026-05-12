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

    <div class="card" style="font-size:13px;line-height:1.5;color:#323130;">
        <h2 style="margin-top:0;">D365 / manager sync API</h2>
        <p style="margin:0 0 8px;color:#605e5c;">Each pool can include <strong>only the fields D365 has</strong> for that row. Send any subset in the JSON body; keys you omit are left unchanged on update/sync.</p>
        <p style="margin:0 0 8px;color:#605e5c;"><strong>Optional keys:</strong> <code>project</code>, <code>warehouse</code>, <code>warehouse_company_id</code> (D365 data area / legal entity for that warehouse), <code>attachment</code>, <code>item_category</code>, <code>item_id</code> — plus legacy <code>project_warehouse</code> / <code>category_item</code>.</p>
        <ul style="margin:0;padding-left:18px;">
            <li><strong>Bearer token</strong>: <code>POST {{ url('/api/pools/sync-d365') }}</code> — required: <code>pool_id</code>, <code>name</code>, <code>company_id</code>; optional fields as above.</li>
            <li><strong>Super admin session</strong>: <code>POST {{ url('/masters/api/pools/sync-d365') }}</code> with the same JSON + CSRF.</li>
            <li><strong>Update one row</strong>: <code>PATCH {{ url('/masters/api/pools') }}/{id}</code> (send only keys you want to change).</li>
        </ul>
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
                <div>
                    <label for="project">Project <span style="font-weight:400;color:#605e5c;">(optional)</span></label>
                    <input id="project" name="project" type="text" maxlength="500" placeholder="Only if this pool has a project in D365">
                </div>
                <div>
                    <label for="warehouse">Warehouse <span style="font-weight:400;color:#605e5c;">(optional)</span></label>
                    <input id="warehouse" name="warehouse" type="text" maxlength="500" placeholder="Warehouse id / name from D365">
                </div>
                <div>
                    <label for="warehouse_company_id">Warehouse company ID <span style="font-weight:400;color:#605e5c;">(optional)</span></label>
                    <input id="warehouse_company_id" name="warehouse_company_id" type="text" maxlength="100" placeholder="DataAreaId / company for that warehouse (e.g. PS)">
                </div>
                <div>
                    <label for="attachment">Attachment <span style="font-weight:400;color:#605e5c;">(optional)</span></label>
                    <textarea id="attachment" name="attachment" rows="2" maxlength="60000" style="width:100%;padding:8px;border:1px solid #8a8886;border-radius:2px;box-sizing:border-box;resize:vertical;" placeholder="Only if this pool has an attachment reference in D365"></textarea>
                </div>
                <div>
                    <label for="item_category">Item category <span style="font-weight:400;color:#605e5c;">(optional)</span></label>
                    <input id="item_category" name="item_category" type="text" maxlength="500" placeholder="Category from D365, if any">
                </div>
                <div>
                    <label for="item_id">Item ID <span style="font-weight:400;color:#605e5c;">(optional)</span></label>
                    <input id="item_id" name="item_id" type="text" maxlength="200" placeholder="Item / product id from D365, if any">
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
                    <th>Project</th>
                    <th>Warehouse</th>
                    <th>Wh. company</th>
                    <th>Attachment</th>
                    <th>Item category</th>
                    <th>Item ID</th>
                    <th>Pool ID</th>
                    <th>Name</th>
                    <th>Company ID</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="12">Loading pools...</td>
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

        const escapeAttr = (s) => escapeHtml(s).replace(/"/g, '&quot;');

        const truncateCell = (value, maxLen) => {
            const t = (value ?? '').trim();
            if (!t) return '—';
            if (t.length <= maxLen) return escapeHtml(t);
            const short = t.slice(0, maxLen);
            return `<span title="${escapeAttr(t)}">${escapeHtml(short)}…</span>`;
        };

        const cellOrLegacy = (primary, legacy) => {
            const t = ((primary ?? '').trim() || (legacy ?? '').trim());
            return t;
        };

        const loadPools = async () => {
            syncCompanyFieldFromGlobal();
            const companyId = currentCompanyId();
            if (!companyId) {
                poolsTbody.innerHTML = '<tr><td colspan="12" class="empty-note">Select a company (top right) to view pools for that company.</td></tr>';
                return;
            }
            poolsTbody.innerHTML = '<tr><td colspan="12">Loading pools...</td></tr>';
            try {
                const response = await fetch(`${poolsApiUrl}?company_id=${encodeURIComponent(companyId)}`, { headers: defaultHeaders });
                if (!response.ok) throw new Error('Failed to load pools');
                const payload = await response.json();
                const pools = payload.data || [];

                if (!pools.length) {
                    poolsTbody.innerHTML = '<tr><td colspan="12">No pools found.</td></tr>';
                    return;
                }

                poolsTbody.innerHTML = pools.map((pool, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${truncateCell(cellOrLegacy(pool.project, pool.project_warehouse), 32)}</td>
                        <td>${truncateCell(pool.warehouse ?? '', 28)}</td>
                        <td>${truncateCell((pool.warehouse_company_id ?? '').toUpperCase(), 12)}</td>
                        <td>${truncateCell(pool.attachment ?? '', 28)}</td>
                        <td>${truncateCell(cellOrLegacy(pool.item_category, pool.category_item), 28)}</td>
                        <td>${truncateCell(pool.item_id ?? '', 20)}</td>
                        <td>${escapeHtml(pool.pool_id ?? '-')}</td>
                        <td>${escapeHtml(pool.name ?? '-')}</td>
                        <td>${escapeHtml(pool.company_id ?? '-')}</td>
                        <td>${formatDate(pool.created_at)}</td>
                        <td><button class="danger" data-id="${pool.id}">Delete</button></td>
                    </tr>
                `).join('');
            } catch {
                poolsTbody.innerHTML = '<tr><td colspan="12">Failed to load pools.</td></tr>';
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
            const project = document.getElementById('project').value.trim();
            const warehouse = document.getElementById('warehouse').value.trim();
            const warehouseCompanyId = document.getElementById('warehouse_company_id').value.trim();
            const attachment = document.getElementById('attachment').value.trim();
            const itemCategory = document.getElementById('item_category').value.trim();
            const itemId = document.getElementById('item_id').value.trim();
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
                        ...(project !== '' ? { project } : {}),
                        ...(warehouse !== '' ? { warehouse } : {}),
                        ...(warehouseCompanyId !== '' ? { warehouse_company_id: warehouseCompanyId.toUpperCase() } : {}),
                        ...(attachment !== '' ? { attachment } : {}),
                        ...(itemCategory !== '' ? { item_category: itemCategory } : {}),
                        ...(itemId !== '' ? { item_id: itemId } : {}),
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
                document.getElementById('project').value = '';
                document.getElementById('warehouse').value = '';
                document.getElementById('warehouse_company_id').value = '';
                document.getElementById('attachment').value = '';
                document.getElementById('item_category').value = '';
                document.getElementById('item_id').value = '';
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
