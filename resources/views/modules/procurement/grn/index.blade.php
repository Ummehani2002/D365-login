<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GRN</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: "Segoe UI", Arial, sans-serif; background: #f3f2f1; color: #323130; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #fff; border-right: 1px solid #edebe9; padding: 12px 0; flex-shrink: 0; }
        .logo { padding: 10px 16px 18px; border-bottom: 1px solid #edebe9; margin-bottom: 8px; font-weight: 700; }
        .label { padding: 10px 16px 4px; color: #8a8886; font-size: 11px; text-transform: uppercase; }
        .menu-link { display: block; padding: 10px 16px; color: #323130; text-decoration: none; border-radius: 8px; margin: 2px 8px; font-size: 14px; }
        .menu-link:hover { background: #f3f2f1; }
        .menu-link.active { background: #deecf9; color: #005a9e; }
        .sub { margin-left: 16px; padding-left: 8px; border-left: 2px solid #edebe9; }
        .main { flex: 1; padding: 12px 16px; overflow: auto; }
        .card { background: #fff; border: 1px solid #edebe9; border-radius: 8px; overflow: hidden; margin-bottom: 14px; }
        .card-head { padding: 14px 16px; border-bottom: 1px solid #edebe9; display: flex; justify-content: space-between; align-items: center; gap: 8px; }
        .title { margin: 0; font-size: 30px; font-weight: 700; }
        .subtitle { margin: 2px 0 0; font-size: 13px; color: #605e5c; }
        .btn { border: 1px solid #8a8886; background: #fff; color: #323130; border-radius: 6px; padding: 8px 14px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; cursor: pointer; }
        .btn-primary { border-color: #4f6bed; background: #4f6bed; color: #fff; }
        .body { padding: 14px 16px; }
        .toolbar { margin-bottom: 10px; display: flex; justify-content: flex-start; gap: 8px; }
        .filters { display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 10px; margin-bottom: 12px; }
        .field label { display: block; margin-bottom: 5px; font-size: 12px; color: #605e5c; font-weight: 600; text-transform: uppercase; }
        .field input { width: 100%; border: 1px solid #c8c6c4; border-radius: 6px; padding: 10px 12px; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #edebe9; padding: 10px 10px; text-align: left; font-size: 13px; }
        th { background: #faf9f8; color: #605e5c; font-weight: 600; white-space: nowrap; }
        .empty { text-align: center; color: #8a8886; padding: 24px 10px; }
        .badge { background: #e7f7ed; color: #0b6a3e; border-radius: 10px; padding: 2px 8px; font-size: 11px; font-weight: 700; }
        .hidden { display: none !important; }
        .status { margin-bottom: 10px; padding: 10px 12px; border-radius: 8px; display: none; }
        .status.error { display: block; background: #fde7e9; color: #a4262c; }
        .status.success { display: block; background: #e7f7ed; color: #0b6a3e; }
    </style>
</head>
<body>
    @include('partials.global-company-selector')
    @php
        $companyQuery = !empty($currentCompanyCode) ? ['company' => strtoupper((string) $currentCompanyCode)] : [];
    @endphp
    <aside class="sidebar">
        <div class="logo">Logo</div>
        <div class="label">Menu</div>
        <a class="menu-link" href="{{ route('dashboard', $companyQuery) }}">Dashboard</a>
        <a class="menu-link" href="{{ route('masters.company.index', $companyQuery) }}">Masters</a>
        <a class="menu-link" href="#">Modules</a>
        <div class="sub">
            <a class="menu-link" href="#">Project Management</a>
            <a class="menu-link" href="{{ route('modules.project-management.item-issue', $companyQuery) }}">Item Issue</a>
            <a class="menu-link active" href="#">Procurement &amp; Sourcing</a>
            <div class="sub">
                <a class="menu-link" href="{{ route('modules.procurement.purch-req', $companyQuery) }}">Purchase Requisition</a>
                <a class="menu-link active" href="{{ route('grns.index', $companyQuery) }}">GRN</a>
            </div>
        </div>
        <a class="menu-link" href="{{ route('settings.index', $companyQuery) }}">Settings</a>
    </aside>
    <main class="main">
        <div id="status-box" class="status"></div>

        <section id="index-card" class="card">
            <div class="card-head">
                <div>
                    <h1 class="title">GRN</h1>
                    <p class="subtitle">Only added/posted GRNs are listed here.</p>
                </div>
            </div>
            <div class="body">
                <div class="toolbar">
                    <button id="new-grn-btn" type="button" class="btn btn-primary">+ Create New GRN</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Packing Slip ID</th>
                            <th>Purch ID</th>
                            <th>Vendor</th>
                            <th>Project</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($journals as $j)
                            <tr>
                                <td>{{ $j->request_id ?: '—' }}</td>
                                <td>{{ $j->packing_slip_id ?: '—' }}</td>
                                <td>{{ $j->purch_id ?: '—' }}</td>
                                <td>{{ $j->vendor_name ?: '—' }}</td>
                                <td>{{ $j->project_id ?: '—' }}</td>
                                <td><span class="badge">Posted</span></td>
                                <td>{{ optional($j->created_at)->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="empty">No added GRN records.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section id="search-card" class="card hidden">
            <div class="card-head">
                <div>
                    <h2 style="margin:0;font-size:24px;">Purchase Orders</h2>
                    <p class="subtitle">Fill any one, two, or all fields and search.</p>
                </div>
                <button id="back-to-index-btn" class="btn" type="button">Back</button>
            </div>
            <div class="body">
                <div class="filters">
                    <div class="field">
                        <label>Purch ID</label>
                        <input id="search-purch-id" type="text" placeholder="PS-PO25-005298">
                    </div>
                    <div class="field">
                        <label>Vendor Name</label>
                        <input id="search-vendor-name" type="text" placeholder="Vendor name">
                    </div>
                    <div class="field">
                        <label>Project ID</label>
                        <input id="search-project-id" type="text" placeholder="PIE20241002">
                    </div>
                    <div class="field" style="display:flex; align-items:flex-end;">
                        <button id="search-btn" type="button" class="btn btn-primary">Search</button>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Purchase Order</th>
                            <th>Project ID</th>
                            <th>Vendor Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="search-results-body">
                        <tr><td colspan="5" class="empty">Search to load records.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="detail-card" class="hidden">
            <div class="toolbar" style="margin-bottom:12px;">
                <button id="post-btn" type="button" class="btn btn-primary">Post</button>
                <button id="back-to-search-btn" type="button" class="btn">Back</button>
            </div>

            <div class="card">
                <div class="body">
                    <div style="margin-bottom:12px;">
                        <div style="font-size:12px;color:#605e5c;">Purchase Order</div>
                        <div id="detail-po-title" style="font-size:36px;font-weight:700;">-</div>
                        <div style="font-size:12px;color:#605e5c;">Vendor: <span id="detail-vendor-title">-</span></div>
                    </div>
                    <div class="filters" style="grid-template-columns:1fr 1fr 1fr;">
                        <div class="field"><label>Purchase Order</label><input id="detail-purch-id" type="text" readonly></div>
                        <div class="field"><label>Vendor</label><input id="detail-vendor-name" type="text" readonly></div>
                        <div class="field"><label>Project</label><input id="detail-project-id" type="text" readonly></div>
                    </div>
                    <div class="filters" style="grid-template-columns:1fr 1fr;">
                        <div class="field"><label>Packing Slip ID</label><input id="detail-packing-slip-id" type="text"></div>
                        <div class="field"><label>Document Date</label><input id="detail-document-date" type="date"></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head"><h3 style="margin:0;font-size:30px;">Line Items</h3></div>
                <div class="body">
                    <table>
                        <thead>
                            <tr>
                                <th>Line</th>
                                <th>Item</th>
                                <th>Name</th>
                                <th>Ordered Qty</th>
                                <th>Remaining Qty</th>
                                <th>Receive Qty</th>
                            </tr>
                        </thead>
                        <tbody id="detail-lines-body">
                            <tr><td colspan="6" class="empty">No lines loaded.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script>
    (() => {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const selectedCompany = () => document.getElementById('global-company-select')?.value || '{{ $currentCompanyCode ?? "" }}';

        const statusBox = document.getElementById('status-box');
        const indexCard = document.getElementById('index-card');
        const searchCard = document.getElementById('search-card');
        const detailCard = document.getElementById('detail-card');

        const searchPurchIdEl = document.getElementById('search-purch-id');
        const searchVendorNameEl = document.getElementById('search-vendor-name');
        const searchProjectIdEl = document.getElementById('search-project-id');
        const searchResultsBody = document.getElementById('search-results-body');

        const detailPoTitleEl = document.getElementById('detail-po-title');
        const detailVendorTitleEl = document.getElementById('detail-vendor-title');
        const detailPurchIdEl = document.getElementById('detail-purch-id');
        const detailVendorNameEl = document.getElementById('detail-vendor-name');
        const detailProjectIdEl = document.getElementById('detail-project-id');
        const detailPackingSlipIdEl = document.getElementById('detail-packing-slip-id');
        const detailDocumentDateEl = document.getElementById('detail-document-date');
        const detailLinesBody = document.getElementById('detail-lines-body');

        let selectedHeader = null;

        function showStatus(message, type) {
            statusBox.className = 'status ' + type;
            statusBox.textContent = message;
        }
        function clearStatus() {
            statusBox.className = 'status';
            statusBox.textContent = '';
        }
        function esc(v) {
            return String(v ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
        function showIndex() {
            clearStatus();
            indexCard.classList.remove('hidden');
            searchCard.classList.add('hidden');
            detailCard.classList.add('hidden');
        }
        function showSearch() {
            clearStatus();
            indexCard.classList.add('hidden');
            searchCard.classList.remove('hidden');
            detailCard.classList.add('hidden');
        }
        function showDetail() {
            clearStatus();
            indexCard.classList.add('hidden');
            searchCard.classList.add('hidden');
            detailCard.classList.remove('hidden');
        }

        async function postJson(url, payload) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (!res.ok || !data.status) throw new Error(data.error || data.message || 'Request failed.');
            return data;
        }

        async function searchHeaders() {
            clearStatus();
            const purchId = searchPurchIdEl.value.trim();
            const vendorName = searchVendorNameEl.value.trim();
            const projectId = searchProjectIdEl.value.trim();

            if (!purchId && !vendorName && !projectId) {
                showStatus('Please fill at least one search field.', 'error');
                return;
            }

            try {
                const data = await postJson('{{ route("modules.procurement.grn.api.headers") }}', {
                    company: selectedCompany(),
                    purch_id: purchId,
                    vendor_name: vendorName,
                    project_id: projectId,
                });

                const rows = Array.isArray(data.rows) ? data.rows : [];
                if (!rows.length) {
                    searchResultsBody.innerHTML = '<tr><td colspan="5" class="empty">No purchase orders found.</td></tr>';
                    return;
                }

                searchResultsBody.innerHTML = rows.map((r, i) => `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${esc(r.purch_id)}</td>
                        <td>${esc(r.project_id)}</td>
                        <td>${esc(r.vendor_name)}</td>
                        <td><button class="btn btn-primary view-po-btn" type="button" data-row="${esc(encodeURIComponent(JSON.stringify(r)))}">View</button></td>
                    </tr>
                `).join('');
            } catch (err) {
                showStatus(err.message, 'error');
            }
        }

        async function openDetail(row) {
            selectedHeader = row;
            detailPoTitleEl.textContent = row.purch_id || '-';
            detailVendorTitleEl.textContent = row.vendor_name || '-';
            detailPurchIdEl.value = row.purch_id || '';
            detailVendorNameEl.value = row.vendor_name || '';
            detailProjectIdEl.value = row.project_id || '';
            detailPackingSlipIdEl.value = row.packing_slip_id || '';
            detailDocumentDateEl.value = row.packing_slip_date || new Date().toISOString().slice(0, 10);

            const data = await postJson('{{ route("modules.procurement.grn.api.lines") }}', {
                company: selectedCompany(),
                purch_id: row.purch_id || '',
                request_id: row.request_id || '',
                vendor_name: row.vendor_name || '',
                project_id: row.project_id || '',
            });
            const rows = Array.isArray(data.rows) ? data.rows : [];
            if (!rows.length) {
                detailLinesBody.innerHTML = '<tr><td colspan="6" class="empty">No lines found.</td></tr>';
            } else {
                detailLinesBody.innerHTML = rows.map((l) => `
                    <tr>
                        <td>${esc(l.line_number)}</td>
                        <td>${esc(l.item_id)}</td>
                        <td>${esc(l.item_name)}</td>
                        <td>${esc(l.ordered_qty)}</td>
                        <td>${esc(l.remaining_qty)}</td>
                        <td><input class="line-receive" type="number" min="0" step="any" value="${esc(l.receive_now)}" data-line="${esc(l.line_number)}" data-rec="${esc(l.purch_line_rec_id)}" style="width:100%;border:1px solid #c8c6c4;border-radius:6px;padding:8px 10px;"></td>
                    </tr>
                `).join('');
            }
            showDetail();
        }

        async function postGrn() {
            clearStatus();
            if (!selectedHeader) return showStatus('No selected PO.', 'error');
            if (!detailPackingSlipIdEl.value.trim()) return showStatus('Packing Slip ID is required.', 'error');
            if (!detailDocumentDateEl.value) return showStatus('Document Date is required.', 'error');

            const lines = Array.from(detailLinesBody.querySelectorAll('.line-receive')).map((el) => ({
                line_number: Number(el.dataset.line || 0),
                purch_line_rec_id: Number(el.dataset.rec || 0),
                receive_now: Number(el.value || 0),
            }));
            if (!lines.length) return showStatus('No lines to post.', 'error');

            try {
                await postJson('{{ route("modules.procurement.grn.api.post") }}', {
                    company: selectedCompany(),
                    request_id: selectedHeader.request_id || `${selectedHeader.purch_id || 'REQ'}-${Date.now()}`,
                    packing_slip_date: detailDocumentDateEl.value,
                    purch_id: selectedHeader.purch_id || '',
                    project_id: selectedHeader.project_id || '',
                    vendor_name: selectedHeader.vendor_name || '',
                    packing_slip_id: detailPackingSlipIdEl.value.trim(),
                    lines,
                });
                showStatus('GRN posted successfully.', 'success');
                window.location.reload();
            } catch (err) {
                showStatus(err.message, 'error');
            }
        }

        document.getElementById('new-grn-btn').addEventListener('click', showSearch);
        document.getElementById('back-to-index-btn').addEventListener('click', showIndex);
        document.getElementById('back-to-search-btn').addEventListener('click', showSearch);
        document.getElementById('search-btn').addEventListener('click', searchHeaders);
        document.getElementById('post-btn').addEventListener('click', postGrn);
        searchResultsBody.addEventListener('click', async (e) => {
            const btn = e.target.closest('.view-po-btn');
            if (!btn) return;
            try {
                const row = JSON.parse(decodeURIComponent(btn.dataset.row || ''));
                await openDetail(row);
            } catch (err) {
                showStatus('Unable to open selected row.', 'error');
            }
        });

        if (window.location.hash === '#new') {
            showSearch();
        }
    })();
    </script>
</body>
</html>
