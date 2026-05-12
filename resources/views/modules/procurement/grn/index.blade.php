<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Goods Receive Note</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f3f2f1; color: #323130; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #fff; border-right: 1px solid #edebe9; padding: 12px 0; flex-shrink: 0; }
        .logo { padding: 10px 16px 18px; border-bottom: 1px solid #edebe9; margin-bottom: 8px; font-weight: 700; }
        .label { padding: 10px 16px 4px; color: #8a8886; font-size: 11px; text-transform: uppercase; }
        .menu-link { display: block; padding: 10px 16px; color: #323130; text-decoration: none; border-radius: 8px; margin: 2px 8px; font-size: 14px; }
        .menu-link:hover { background: #f3f2f1; }
        .menu-link.active { background: #deecf9; color: #005a9e; }
        .sub { margin-left: 16px; padding-left: 8px; border-left: 2px solid #edebe9; }
        .main { flex: 1; padding: 12px 16px; overflow: auto; }
        .page-shell { border: 1px solid #edebe9; background: #fff; border-radius: 2px; overflow: visible; }
        .command-bar { height: 44px; border-bottom: 1px solid #edebe9; background: #fff; display: flex; align-items: center; padding: 0 12px; }
        .crumb { font-size: 12px; color: #605e5c; }
        .card { background: #fff; border: 1px solid #edebe9; border-radius: 2px; }
        .hidden { display: none !important; }
        .card-head { padding: 12px 14px; border-bottom: 1px solid #edebe9; font-size: 20px; font-weight: 600; }
        .toolbar-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 12px; }
        .toolbar-actions { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 12px; }
        .title { margin: 0; font-size: 24px; font-weight: 600; }
        .search { width: 280px; border: 1px solid #8a8886; border-radius: 2px; padding: 7px 10px; font-size: 13px; }
        .btn { border: 1px solid #8a8886; background: #fff; color: #323130; border-radius: 2px; padding: 6px 12px; font-size: 12px; font-weight: 600; cursor: pointer; }
        .btn-primary { border-color: #106ebe; background: #106ebe; color: #fff; }
        .btn-view { border-color: #4f46e5; background: #4f46e5; color: #fff; padding: 4px 10px; font-size: 11px; }
        .filter-grid { display: grid; grid-template-columns: repeat(4, minmax(160px, 1fr)); gap: 10px; margin-bottom: 12px; }
        .field label { display: block; font-size: 12px; margin-bottom: 4px; color: #605e5c; font-weight: 500; }
        .field input, .field select { width: 100%; border: 1px solid #8a8886; border-radius: 2px; padding: 7px 8px; font-size: 13px; background: #fff; }
        .po-picker { position: relative; }
        .po-picker-menu {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #d2d0ce;
            border-radius: 2px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.16);
            max-height: 300px;
            overflow-y: auto;
            z-index: 1100;
        }
        .po-picker-head,
        .po-picker-option {
            display: grid;
            grid-template-columns: 190px 1fr;
            gap: 10px;
            align-items: center;
        }
        .po-picker-head {
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 700;
            color: #605e5c;
            border-bottom: 1px solid #edebe9;
            background: #faf9f8;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        .po-picker-option {
            width: 100%;
            border: 0;
            border-bottom: 1px solid #edebe9;
            background: #fff;
            padding: 7px 10px;
            text-align: left;
            cursor: pointer;
            font-size: 13px;
            color: #323130;
        }
        .po-picker-option:hover { background: #f3f2f1; }
        .po-picker-empty { padding: 8px 10px; font-size: 12px; color: #8a8886; }
        .status-box { margin-bottom: 10px; padding: 8px 10px; border-radius: 2px; font-size: 13px; display: none; }
        .status-box.success { display: block; background: #e8f6ee; color: #1f7a48; }
        .status-box.error { display: block; background: #fde7e9; color: #a4262c; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #edebe9; padding: 8px 10px; text-align: left; font-size: 13px; }
        td.request-id-cell { max-width: 11rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; vertical-align: middle; }
        th { color: #605e5c; font-weight: 600; background: #faf9f8; white-space: nowrap; }
        .empty-note { text-align: center; color: #8a8886; padding: 22px 10px; font-size: 13px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; background: #deecf9; color: #005a9e; }
    </style>
    @include('settings.rbac.partials.styles')
</head>
<body>
    @include('partials.global-company-selector')
    @php
        $companyCode = strtoupper((string) ($currentCompanyCode ?? $globalSelectedCompany ?? request()->query('company', '')));
        $companyQuery = $companyCode !== '' ? ['company' => $companyCode] : [];
    @endphp
    @include('settings.rbac.partials.sidebar')

    <main class="main">
        <div class="page-shell">
            <div class="command-bar">
                <div class="crumb">Modules / Procurement &amp; Sourcing / Goods Receive Note</div>
            </div>
            <div style="padding:12px;">
                <div class="toolbar-row">
                    <h1 class="title">Goods Receive Note</h1>
                    <div style="display:flex;gap:8px;">
                        <button id="create-grn-btn" type="button" class="btn btn-primary">+ Create New GRN</button>
                        <button id="back-to-list-btn" type="button" class="btn hidden">Back to List</button>
                        <button id="search-btn" type="button" class="btn btn-primary hidden">Search from D365</button>
                    </div>
                </div>

                <div id="history-search-wrap" class="toolbar-actions">
                    <input id="history-search-input" class="search" type="text" placeholder="Search GRN by request, purchase, company, vendor..." autocomplete="off">
                </div>

                <div id="status-box" class="status-box"></div>

                <div id="grn-history-shell">
                    <div class="card">
                        <div class="card-head">Recently Added GRN</div>
                        <p class="card-hint" style="margin:0 14px 10px;font-size:12px;color:#605e5c;line-height:1.45;">System <strong>Request ID</strong> is generated only when you post (not from Packing Slip ID). Packing Slip ID is your own free-text reference.</p>
                        <div style="overflow:auto;">
                            <table>
                                <thead>
                                    <tr>
                                        <th title="Assigned by this app when you post — independent of Packing Slip ID">System request ID</th>
                                        <th>Purchase ID</th>
                                        <th>Company</th>
                                        <th title="What you typed — sent to D365 as packing slip reference">Packing Slip ID</th>
                                        <th>Lines</th>
                                        <th>Created By</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($journals as $j)
                                        <tr>
                                            <td class="request-id-cell" title="{{ $j->request_id ? e($j->request_id) : '' }}">{{ $j->request_id ?: '—' }}</td>
                                            <td>{{ $j->purch_id ?: '—' }}</td>
                                            <td>{{ $j->company ?: '—' }}</td>
                                            <td>{{ $j->packing_slip_id ?: '—' }}</td>
                                            <td><span class="badge">{{ is_array($j->lines) ? count($j->lines) : 0 }}</span></td>
                                            <td>{{ $j->postedBy?->name ?: '—' }}</td>
                                            <td>{{ $j->created_at?->format('d M Y H:i') ?: '—' }}</td>
                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn btn-view open-grn-btn"
                                                    data-journal-id="{{ $j->id }}"
                                                    data-company="{{ $j->company }}"
                                                    data-purchase-id="{{ $j->purch_id }}"
                                                    data-vendor-name="{{ $j->vendor_name }}"
                                                    data-project-id="{{ $j->project_id }}"
                                                >
                                                    Open
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="empty-note">No GRN records yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="grn-search-shell" class="hidden">
                    <input id="company" type="hidden" value="{{ strtoupper((string) ($currentCompanyCode ?? $companyCode ?? $globalSelectedCompany ?? request()->query('company', ''))) }}">
                    <div class="filter-grid">
                        <div class="field">
                            <label>Purchase ID</label>
                            <div class="po-picker">
                                <input id="purch-id" type="text" placeholder="Search purchase order..." autocomplete="off">
                                <div id="po-picker-menu" class="po-picker-menu hidden"></div>
                            </div>
                        </div>
                        <div class="field">
                            <label>Vendor Name</label>
                            <input id="vend-name" type="text" placeholder="e.g. Gulf Supplies">
                        </div>
                        <div class="field">
                            <label>Project ID</label>
                            <input id="proj-id" type="text" placeholder="e.g. PRJ-001">
                        </div>
                    </div>

                    <div id="results-card" class="card hidden">
                        <div class="card-head">GRN Results</div>
                        <div style="overflow:auto;">
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
                                <tbody id="results-body">
                                    <tr><td colspan="5" class="empty-note">Search to load records from D365.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script>
        const els = {
            company: document.getElementById('company'),
            purchId: document.getElementById('purch-id'),
            poPickerMenu: document.getElementById('po-picker-menu'),
            vendName: document.getElementById('vend-name'),
            projId: document.getElementById('proj-id'),
            searchBtn: document.getElementById('search-btn'),
            createGrnBtn: document.getElementById('create-grn-btn'),
            backToListBtn: document.getElementById('back-to-list-btn'),
            statusBox: document.getElementById('status-box'),
            resultsBody: document.getElementById('results-body'),
            resultsCard: document.getElementById('results-card'),
            historyShell: document.getElementById('grn-history-shell'),
            searchShell: document.getElementById('grn-search-shell'),
            historySearchWrap: document.getElementById('history-search-wrap'),
            historySearchInput: document.getElementById('history-search-input'),
        };

        function showHistory() {
            els.historyShell.classList.remove('hidden');
            els.searchShell.classList.add('hidden');
            els.createGrnBtn.classList.remove('hidden');
            els.searchBtn.classList.add('hidden');
            els.backToListBtn.classList.add('hidden');
            els.historySearchWrap.classList.remove('hidden');
            setStatus('', '');
        }

        function showSearch() {
            els.historyShell.classList.add('hidden');
            els.searchShell.classList.remove('hidden');
            els.createGrnBtn.classList.add('hidden');
            els.searchBtn.classList.remove('hidden');
            els.backToListBtn.classList.remove('hidden');
            els.historySearchWrap.classList.add('hidden');
            setStatus('', '');
        }

        function applyHistorySearch() {
            const q = (els.historySearchInput.value || '').trim().toLowerCase();
            const tbody = document.querySelector('#grn-history-shell tbody');
            if (!tbody) return;

            tbody.querySelector('tr.grn-no-results-msg')?.remove();
            const rows = [...tbody.querySelectorAll('tr')].filter((tr) => tr.querySelector('.open-grn-btn'));
            if (!rows.length) return;

            if (!q) {
                rows.forEach((tr) => tr.style.removeProperty('display'));
                return;
            }

            let anyVisible = false;
            rows.forEach((tr) => {
                const text = tr.textContent.replace(/\s+/g, ' ').trim().toLowerCase();
                const show = text.includes(q);
                tr.style.display = show ? '' : 'none';
                if (show) anyVisible = true;
            });

            if (!anyVisible) {
                const tr = document.createElement('tr');
                tr.className = 'grn-no-results-msg';
                tr.innerHTML = '<td colspan="8" class="empty-note">No GRN records match your search.</td>';
                tbody.appendChild(tr);
            }
        }

        function setStatus(type, text) {
            els.statusBox.className = 'status-box ' + (type || '');
            els.statusBox.textContent = text || '';
        }

        function escapeHtml(v) {
            return String(v ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function hidePoPickerMenu() {
            els.poPickerMenu.classList.add('hidden');
        }

        function renderPoPickerMenu(rows) {
            if (!Array.isArray(rows) || rows.length === 0) {
                els.poPickerMenu.innerHTML = '<div class="po-picker-empty">No matching purchase order.</div>';
                els.poPickerMenu.classList.remove('hidden');
                return;
            }

            const uniqueRows = [];
            const seen = new Set();
            rows.forEach((row) => {
                const purchaseOrder = String(row.purchase_order || '').trim();
                if (!purchaseOrder || seen.has(purchaseOrder)) return;
                seen.add(purchaseOrder);
                uniqueRows.push({
                    purchase_order: purchaseOrder,
                    vendor_name: String(row.vendor_name || '-').trim() || '-',
                    project_id: String(row.project_id || '').trim(),
                });
            });

            if (!uniqueRows.length) {
                els.poPickerMenu.innerHTML = '<div class="po-picker-empty">No matching purchase order.</div>';
                els.poPickerMenu.classList.remove('hidden');
                return;
            }

            els.poPickerMenu.innerHTML = `
                <div class="po-picker-head"><span>Purchase order</span><span>Vendor name</span></div>
                ${uniqueRows.map((row) => `
                    <button
                        type="button"
                        class="po-picker-option"
                        data-po="${escapeHtml(row.purchase_order)}"
                        data-vendor="${escapeHtml(row.vendor_name)}"
                        data-project="${escapeHtml(row.project_id)}"
                    >
                        <span>${escapeHtml(row.purchase_order)}</span>
                        <span>${escapeHtml(row.vendor_name)}</span>
                    </button>
                `).join('')}
            `;
            els.poPickerMenu.classList.remove('hidden');
        }

        async function fetchPurchaseOrderSuggestions() {
            const company = (els.company.value || '').trim();
            const purchId = (els.purchId.value || '').trim();
            if (!company) {
                hidePoPickerMenu();
                return;
            }
            try {
                const resp = await fetch("{{ route('modules.procurement.grn.api.search') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        company,
                        purch_id: purchId,
                        vend_name: '',
                        proj_id: '',
                    }),
                });
                const data = await resp.json();
                if (!resp.ok || data.status === false) {
                    hidePoPickerMenu();
                    return;
                }
                renderPoPickerMenu(data.rows || []);
            } catch (_) {
                hidePoPickerMenu();
            }
        }

        function renderRows(rows) {
            if (!Array.isArray(rows) || rows.length === 0) {
                els.resultsBody.innerHTML = '<tr><td colspan="5" class="empty-note">No records found.</td></tr>';
                return;
            }

            els.resultsBody.innerHTML = rows.map((row, i) => {
                const purchaseOrder = row.purchase_order || '-';
                const projectId = row.project_id || '-';
                const vendorName = row.vendor_name || '-';
                const rowPayload = encodeURIComponent(JSON.stringify({
                    purchase_order: purchaseOrder,
                    project_id: projectId,
                    vendor_name: vendorName
                }));

                return `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${purchaseOrder}</td>
                        <td>${projectId}</td>
                        <td>${vendorName}</td>
                        <td><button type="button" class="btn btn-view" data-row="${rowPayload}">View</button></td>
                    </tr>
                `;
            }).join('');

            els.resultsBody.querySelectorAll('.btn-view').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const rowPayload = btn.getAttribute('data-row') || '';
                    if (!rowPayload) return;
                    let row;
                    try {
                        row = JSON.parse(decodeURIComponent(rowPayload));
                    } catch (_) {
                        setStatus('error', 'Unable to read selected row.');
                        return;
                    }
                    const params = new URLSearchParams({
                        company: (els.company.value || '').trim(),
                        purchase_id: row.purchase_order || '',
                        vendor_name: row.vendor_name || '',
                        project_id: row.project_id || '',
                    });
                    window.location.href = `{{ route('modules.procurement.grn.view') }}?${params.toString()}`;
                });
            });
        }

        async function searchGrn() {
            const company = (els.company.value || '').trim();
            if (!company) {
                setStatus('error', 'Please select company.');
                return;
            }

            els.searchBtn.disabled = true;
            els.searchBtn.textContent = 'Searching...';
            setStatus('', '');
            els.resultsCard.classList.remove('hidden');
            els.resultsBody.innerHTML = '<tr><td colspan="5" class="empty-note">Loading...</td></tr>';

            try {
                const resp = await fetch("{{ route('modules.procurement.grn.api.search') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        company,
                        purch_id: (els.purchId.value || '').trim(),
                        vend_name: (els.vendName.value || '').trim(),
                        proj_id: (els.projId.value || '').trim(),
                    }),
                });

                const data = await resp.json();
                if (!resp.ok || data.status === false) {
                    throw new Error(data.message || data.error || 'GRN lookup failed.');
                }

                renderRows(data.rows || []);
                setStatus('success', `Loaded ${Array.isArray(data.rows) ? data.rows.length : 0} record(s).`);
            } catch (e) {
                setStatus('error', e.message || 'GRN lookup failed.');
                els.resultsBody.innerHTML = '<tr><td colspan="5" class="empty-note">Unable to load data.</td></tr>';
            } finally {
                els.searchBtn.disabled = false;
                els.searchBtn.textContent = 'Search from D365';
            }
        }

        els.searchBtn.addEventListener('click', searchGrn);
        els.createGrnBtn.addEventListener('click', showSearch);
        els.backToListBtn.addEventListener('click', showHistory);
        els.historySearchInput.addEventListener('input', applyHistorySearch);
        let poSuggestTimer = null;
        els.purchId.addEventListener('focus', () => {
            fetchPurchaseOrderSuggestions();
        });
        els.purchId.addEventListener('input', () => {
            if (poSuggestTimer) window.clearTimeout(poSuggestTimer);
            poSuggestTimer = window.setTimeout(fetchPurchaseOrderSuggestions, 180);
        });
        els.poPickerMenu.addEventListener('click', (event) => {
            const option = event.target.closest('.po-picker-option');
            if (!option) return;
            els.purchId.value = option.getAttribute('data-po') || '';
            if (!els.vendName.value.trim()) {
                els.vendName.value = option.getAttribute('data-vendor') || '';
            }
            if (!els.projId.value.trim()) {
                els.projId.value = option.getAttribute('data-project') || '';
            }
            hidePoPickerMenu();
        });
        document.addEventListener('click', (event) => {
            const picker = event.target.closest('.po-picker');
            if (!picker) hidePoPickerMenu();
        });
        [els.purchId, els.vendName, els.projId].forEach((input) => {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchGrn();
                }
            });
        });

        document.querySelectorAll('.open-grn-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                const params = new URLSearchParams({
                    company: btn.getAttribute('data-company') || '',
                    purchase_id: btn.getAttribute('data-purchase-id') || '',
                    vendor_name: btn.getAttribute('data-vendor-name') || '',
                    project_id: btn.getAttribute('data-project-id') || '',
                    view_only: '1',
                });
                const jid = btn.getAttribute('data-journal-id');
                if (jid) {
                    params.set('journal_id', jid);
                }
                window.location.href = `{{ route('modules.procurement.grn.view') }}?${params.toString()}`;
            });
        });
    </script>
</body>
</html>
