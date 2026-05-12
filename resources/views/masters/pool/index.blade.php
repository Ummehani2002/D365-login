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
        <p style="margin:0 0 8px;color:#605e5c;">Each pool records <strong>whether</strong> it uses a project, warehouse, attachment, item category, and/or item id — as <strong>yes/no</strong> flags for your manager. Send <code>true</code>/<code>false</code>, <code>1</code>/<code>0</code>, or <code>yes</code>/<code>no</code> (case-insensitive). Omitted flag keys are left unchanged on update/sync.</p>
        <p style="margin:0 0 8px;color:#605e5c;"><strong>Manager sync flags (optional on each request):</strong></p>
        <ul style="margin:0 0 8px;padding-left:18px;">
            <li><code>uses_project</code> — pool is tied to a project (yes/no)</li>
            <li><code>uses_warehouse</code> — pool is tied to a warehouse (yes/no)</li>
            <li><code>has_attachment</code> — pool has an attachment (yes/no)</li>
            <li><code>has_item_category</code> — pool uses item category (yes/no)</li>
            <li><code>has_item_id</code> — pool uses item id (yes/no)</li>
        </ul>
        <p style="margin:0 0 8px;color:#605e5c;"><strong>Optional D365 text fields</strong> (only if you store references: <code>project</code>, <code>warehouse</code>, <code>warehouse_company_id</code>, <code>attachment</code>, <code>item_category</code>, <code>item_id</code>, legacy <code>project_warehouse</code> / <code>category_item</code>) — same omit = no change rule.</p>
        <ul style="margin:0;padding-left:18px;">
            <li><strong>Bearer token</strong>: <code>POST {{ url('/api/pools/sync-d365') }}</code> — required: <code>pool_id</code>, <code>name</code>, <code>company_id</code>; optional flags + text fields as above.</li>
            <li><strong>Super admin session</strong>: <code>POST {{ url('/masters/api/pools/sync-d365') }}</code> with the same JSON + CSRF.</li>
            <li><strong>Update one row</strong>: <code>PATCH {{ url('/masters/api/pools') }}/{id}</code> (send only keys you want to change).</li>
        </ul>
    </div>

    <div class="card">
        <h2>Add pool</h2>
        <div id="form-status" class="status" style="display:none;"></div>
        <div id="form-errors" class="error" style="display:none;"></div>
        <form id="pool-form">
            <p style="margin:0 0 10px;font-size:13px;color:#605e5c;">Tick <strong>Yes</strong> for each dimension that applies to this pool. Unticked = <strong>No</strong>. Your manager can send the same fields via the sync API (<code>true</code>/<code>false</code> or <code>yes</code>/<code>no</code>).</p>
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
            </div>
            <div class="form-row" style="align-items:center;">
                <label style="display:flex;align-items:center;gap:8px;font-weight:600;cursor:pointer;">
                    <input id="uses_project" type="checkbox" style="width:auto;"> Uses project
                </label>
                <label style="display:flex;align-items:center;gap:8px;font-weight:600;cursor:pointer;">
                    <input id="uses_warehouse" type="checkbox" style="width:auto;"> Uses warehouse
                </label>
                <label style="display:flex;align-items:center;gap:8px;font-weight:600;cursor:pointer;">
                    <input id="has_attachment" type="checkbox" style="width:auto;"> Has attachment
                </label>
                <label style="display:flex;align-items:center;gap:8px;font-weight:600;cursor:pointer;">
                    <input id="has_item_category" type="checkbox" style="width:auto;"> Has item category
                </label>
                <label style="display:flex;align-items:center;gap:8px;font-weight:600;cursor:pointer;">
                    <input id="has_item_id" type="checkbox" style="width:auto;"> Has item ID
                </label>
            </div>
            <details style="margin:10px 0;font-size:13px;color:#323130;">
                <summary style="cursor:pointer;font-weight:600;">Optional D365 reference text (advanced)</summary>
                <div class="form-row" style="margin-top:10px;">
                    <div>
                        <label for="project">Project reference</label>
                        <input id="project" name="project" type="text" maxlength="500" placeholder="D365 project id or name">
                    </div>
                    <div>
                        <label for="warehouse">Warehouse reference</label>
                        <input id="warehouse" name="warehouse" type="text" maxlength="500" placeholder="Warehouse id / name">
                    </div>
                    <div>
                        <label for="warehouse_company_id">Warehouse company ID</label>
                        <input id="warehouse_company_id" name="warehouse_company_id" type="text" maxlength="100" placeholder="DataAreaId (e.g. PS)">
                    </div>
                    <div>
                        <label for="attachment">Attachment reference</label>
                        <textarea id="attachment" name="attachment" rows="2" maxlength="60000" style="width:100%;padding:8px;border:1px solid #8a8886;border-radius:2px;box-sizing:border-box;resize:vertical;" placeholder="Attachment reference in D365"></textarea>
                    </div>
                    <div>
                        <label for="item_category">Item category</label>
                        <input id="item_category" name="item_category" type="text" maxlength="500" placeholder="Category from D365">
                    </div>
                    <div>
                        <label for="item_id">Item ID</label>
                        <input id="item_id" name="item_id" type="text" maxlength="200" placeholder="Item / product id">
                    </div>
                </div>
            </details>
            <div style="margin-top:8px;">
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

        const yn = (v) => (v === true || v === 1 || v === '1')
            ? '<span style="color:#107c10;font-weight:600;">Yes</span>'
            : '<span style="color:#605e5c;">No</span>';

        const whCompanyYn = (pool) => {
            const t = (pool.warehouse_company_id ?? '').toString().trim();
            return t !== '' ? '<span style="color:#107c10;font-weight:600;">Yes</span>' : '<span style="color:#605e5c;">No</span>';
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
                        <td>${yn(pool.uses_project)}</td>
                        <td>${yn(pool.uses_warehouse)}</td>
                        <td>${whCompanyYn(pool)}</td>
                        <td>${yn(pool.has_attachment)}</td>
                        <td>${yn(pool.has_item_category)}</td>
                        <td>${yn(pool.has_item_id)}</td>
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
            const usesProject = document.getElementById('uses_project').checked;
            const usesWarehouse = document.getElementById('uses_warehouse').checked;
            const hasAttachment = document.getElementById('has_attachment').checked;
            const hasItemCategory = document.getElementById('has_item_category').checked;
            const hasItemId = document.getElementById('has_item_id').checked;
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
                        uses_project: usesProject,
                        uses_warehouse: usesWarehouse,
                        has_attachment: hasAttachment,
                        has_item_category: hasItemCategory,
                        has_item_id: hasItemId,
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
                document.getElementById('uses_project').checked = false;
                document.getElementById('uses_warehouse').checked = false;
                document.getElementById('has_attachment').checked = false;
                document.getElementById('has_item_category').checked = false;
                document.getElementById('has_item_id').checked = false;
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
