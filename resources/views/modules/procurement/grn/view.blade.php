<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GRN View</title>
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
        .page-shell { border: 1px solid #edebe9; background: #fff; border-radius: 2px; overflow: hidden; }
        .command-bar { height: 44px; border-bottom: 1px solid #edebe9; display: flex; align-items: center; justify-content: space-between; padding: 0 12px; }
        .crumb { font-size: 12px; color: #605e5c; }
        .content { padding: 14px; }
        .top-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .title { margin: 0; font-size: 28px; font-weight: 700; }
        .vendor-sub { margin-top: 4px; font-size: 12px; color: #605e5c; }
        .btn { border: 1px solid #8a8886; background: #fff; color: #323130; border-radius: 2px; padding: 6px 12px; font-size: 12px; font-weight: 600; cursor: pointer; }
        .btn-primary { border-color: #106ebe; background: #106ebe; color: #fff; }
        .fields { display: grid; grid-template-columns: repeat(3, minmax(160px, 1fr)); gap: 10px; margin-bottom: 12px; }
        .field label { display: block; font-size: 12px; margin-bottom: 4px; color: #605e5c; font-weight: 600; }
        .field input { width: 100%; border: 1px solid #8a8886; border-radius: 2px; padding: 7px 8px; font-size: 13px; background: #fff; }
        .field input[readonly] { background: #f3f2f1; color: #605e5c; }
        .card { background: #fff; border: 1px solid #edebe9; border-radius: 2px; overflow: hidden; }
        .line-head { padding: 10px 12px; border-bottom: 1px solid #edebe9; display:flex; justify-content:space-between; align-items:center; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #edebe9; padding: 8px 10px; text-align: left; font-size: 13px; }
        th { color: #605e5c; font-weight: 600; background: #faf9f8; white-space: nowrap; }
        .qty-input { width: 100px; border: 1px solid #8a8886; border-radius: 2px; padding: 5px 7px; font-size: 12px; }
        .empty-note { text-align: center; color: #8a8886; padding: 22px 10px; font-size: 13px; }
        .status-box { margin-bottom: 10px; padding: 8px 10px; border-radius: 2px; font-size: 13px; display: none; }
        .status-box.success { display: block; background: #e8f6ee; color: #1f7a48; }
        .status-box.error { display: block; background: #fde7e9; color: #a4262c; }
    </style>
</head>
<body>
@include('partials.global-company-selector')
@php
    $companyCode = strtoupper((string) request()->query('company', ''));
    $companyQuery = $companyCode !== '' ? ['company' => $companyCode] : [];
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
            <a class="menu-link active" href="{{ route('modules.procurement.grn', $companyQuery) }}">Goods Receive Note</a>
        </div>
    </div>
    <a class="menu-link" href="{{ route('settings.index', $companyQuery) }}" style="display:flex;align-items:center;gap:6px;margin-top:8px;">Settings</a>
</aside>
<main class="main">
    <div class="page-shell">
        <div class="command-bar">
            <div class="crumb">Modules / Procurement &amp; Sourcing / Goods Receive Note / View</div>
            <button id="back-btn" class="btn" type="button">Back</button>
        </div>
        <div class="content">
            <div class="top-head">
                <div>
                    <div style="font-size:12px;color:#605e5c;">Purchase Order</div>
                    <h1 class="title" id="po-title">{{ $purchaseId }}</h1>
                    <div class="vendor-sub">Vendor: <span id="vendor-sub">{{ $vendorName ?: '-' }}</span></div>
                </div>
                <button id="post-btn" class="btn btn-primary {{ !empty($viewOnly) ? 'hidden' : '' }}" type="button">Post</button>
            </div>

            <div id="status-box" class="status-box"></div>

            <div class="fields">
                <div class="field"><label>PURCHASE ORDER</label><input id="purchase-order" value="{{ $purchaseId }}" readonly></div>
                <div class="field"><label>VENDOR</label><input id="vendor-name" value="{{ $vendorName }}" readonly></div>
                <div class="field"><label>PROJECT</label><input id="project-id" value="{{ $projectId }}" readonly></div>
                <div class="field"><label>PACKING SLIP ID</label><input id="packing-slip-id" {{ !empty($viewOnly) ? 'readonly' : '' }}></div>
                <div class="field"><label>DOCUMENT DATE</label><input id="document-date" type="date" {{ !empty($viewOnly) ? 'readonly' : '' }}></div>
            </div>

            <div class="card">
                <div class="line-head">
                    <span>Line Items</span>
                    <span id="line-count-note">0 line</span>
                </div>
                <div style="overflow:auto;">
                    <table>
                        <thead>
                        <tr>
                            <th>Line</th>
                            <th>Item</th>
                            <th>Name</th>
                            <th>Ordered Qty</th>
                            <th>Remaining Qty</th>
                            @if(empty($viewOnly))
                                <th>ReceiveNow</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody id="lines-body">
                        <tr><td colspan="{{ empty($viewOnly) ? 6 : 5 }}" class="empty-note">Loading line items...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    const company = @json($company);
    const purchaseId = @json($purchaseId);
    const vendorName = @json($vendorName);
    const projectId = @json($projectId);
    const isViewOnly = @json(!empty($viewOnly));

    const els = {
        backBtn: document.getElementById('back-btn'),
        postBtn: document.getElementById('post-btn'),
        statusBox: document.getElementById('status-box'),
        purchaseOrder: document.getElementById('purchase-order'),
        vendorName: document.getElementById('vendor-name'),
        projectId: document.getElementById('project-id'),
        packingSlipId: document.getElementById('packing-slip-id'),
        documentDate: document.getElementById('document-date'),
        linesBody: document.getElementById('lines-body'),
        lineCount: document.getElementById('line-count-note'),
    };

    function setStatus(type, text) {
        els.statusBox.className = 'status-box ' + (type || '');
        els.statusBox.textContent = text || '';
    }

    function renderLineRows(lines) {
        if (!Array.isArray(lines) || lines.length === 0) {
            els.lineCount.textContent = '0 line';
            els.linesBody.innerHTML = `<tr><td colspan="${isViewOnly ? 5 : 6}" class="empty-note">No line items found.</td></tr>`;
            return;
        }

        els.lineCount.textContent = `${lines.length} line${lines.length > 1 ? 's' : ''}`;
        els.linesBody.innerHTML = lines.map((line) => `
            <tr data-line-number="${line.line_number || ''}" data-line-rec-id="${line.line_rec_id || ''}">
                <td>${line.line_number || '-'}</td>
                <td>${line.item_id || '-'}</td>
                <td>${line.name || '-'}</td>
                <td>${line.ordered_qty || '0.00'}</td>
                <td>${line.remaining_qty || '0.00'}</td>
                ${isViewOnly ? '' : `<td><input class="qty-input" type="number" step="0.01" min="0" value="${line.receive_qty || ''}" oninput="if(Number(this.value)<0){this.value=0;}"></td>`}
            </tr>
        `).join('');
    }

    async function loadLines() {
        try {
            const resp = await fetch("{{ route('modules.procurement.grn.api.lines') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    company: company,
                    purchase_id: purchaseId,
                    vendor_name: vendorName,
                    project_id: projectId,
                }),
            });

            const data = await resp.json();
            if (!resp.ok || data.status === false) {
                throw new Error(data.message || data.error || 'Line item lookup failed.');
            }

            const header = data.header || {};
            els.purchaseOrder.value = header.purchase_order || purchaseId;
            els.vendorName.value = header.vendor_name || vendorName || '-';
            els.projectId.value = header.project_id || projectId || '-';
            els.packingSlipId.value = header.packing_slip_id || '';
            els.documentDate.value = header.document_date || '';

            renderLineRows(data.lines || []);
            setStatus('success', `Loaded ${Array.isArray(data.lines) ? data.lines.length : 0} line(s).`);
        } catch (e) {
            setStatus('error', e.message || 'Line item lookup failed.');
            els.linesBody.innerHTML = `<tr><td colspan="${isViewOnly ? 5 : 6}" class="empty-note">Unable to load line items.</td></tr>`;
        }
    }

    els.backBtn.addEventListener('click', () => {
        window.location.href = "{{ route('modules.procurement.grn', $companyQuery) }}";
    });
    if (!isViewOnly) {
    els.postBtn.addEventListener('click', () => {
        const packingSlipId = (els.packingSlipId.value || '').trim();
        const documentDate = (els.documentDate.value || '').trim();
        if (!packingSlipId) {
            setStatus('error', 'Packing Slip ID is required.');
            return;
        }
        if (!documentDate) {
            setStatus('error', 'Document Date is required.');
            return;
        }

        const lines = [];
        let hasNegativeQty = false;
        els.linesBody.querySelectorAll('tr[data-line-number]').forEach((tr) => {
            const lineNumber = Number(tr.getAttribute('data-line-number') || 0);
            const lineRecId = (tr.getAttribute('data-line-rec-id') || '').trim();
            const qtyInput = tr.querySelector('.qty-input');
            const receiveQty = Number((qtyInput?.value || '').trim());
            if (receiveQty < 0) {
                hasNegativeQty = true;
                return;
            }
            if (!lineNumber || !lineRecId || !receiveQty || receiveQty <= 0) {
                return;
            }
            lines.push({
                line_number: lineNumber,
                line_rec_id: lineRecId,
                receive_qty: receiveQty,
            });
        });

        if (hasNegativeQty) {
            setStatus('error', 'ReceiveNow cannot be negative.');
            return;
        }

        if (lines.length === 0) {
            setStatus('error', 'Enter Receive Qty greater than 0 for at least one line.');
            return;
        }

        els.postBtn.disabled = true;
        els.postBtn.textContent = 'Posting...';

        fetch("{{ route('modules.procurement.grn.api.post') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                company: company,
                purchase_id: (els.purchaseOrder.value || '').trim(),
                packing_slip_id: packingSlipId,
                document_date: documentDate,
                lines: lines,
            }),
        })
        .then(async (resp) => {
            const data = await resp.json();
            if (!resp.ok || data.status === false) {
                throw new Error(data.error || data.message || 'Posting failed.');
            }
            setStatus('success', `${data.message} Request ID: ${data.request_id}`);
        })
        .catch((err) => {
            setStatus('error', err.message || 'Posting failed.');
        })
        .finally(() => {
            els.postBtn.disabled = false;
            els.postBtn.textContent = 'Post';
        });
    });
    }

    loadLines();
</script>
</body>
</html>
