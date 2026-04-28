<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Purchase Requisition</title>
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
        .pr-layout { display: flex; flex-direction: column; gap: 12px; }
        .page-shell { border: 1px solid #edebe9; background: #fff; border-radius: 2px; overflow: hidden; }
        .command-bar { height: 44px; border-bottom: 1px solid #edebe9; background: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 12px; }
        .crumb { font-size: 12px; color: #605e5c; }
        .btn { border: 1px solid #8a8886; background: #fff; color: #323130; border-radius: 2px; padding: 6px 12px; font-size: 12px; font-weight: 600; cursor: pointer; }
        .btn-primary { border-color: #106ebe; background: #106ebe; color: #fff; }
        .btn-danger { border-color: #a4262c; background: #a4262c; color: #fff; }
        .btn-sm { padding: 4px 10px; font-size: 11px; }
        .btn:disabled { border-color: #edebe9; background: #f3f2f1; color: #a19f9d; cursor: not-allowed; }
        .hidden { display: none !important; }
        .card { background: #fff; border: 1px solid #edebe9; border-radius: 2px; }
        .card-head { padding: 12px 14px; border-bottom: 1px solid #edebe9; font-size: 20px; font-weight: 600; }
        .toolbar-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
        .title { margin: 0 0 4px; font-size: 24px; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #edebe9; padding: 7px 9px; text-align: left; font-size: 12px; }
        th { color: #605e5c; font-weight: 600; background: #faf9f8; white-space: nowrap; }
        .empty-note { text-align: center; color: #8a8886; padding: 22px 10px; font-size: 13px; }
        .form-wrap { background: #fff; border: 1px solid #edebe9; border-radius: 2px; padding: 14px; }
        .form-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; gap: 10px; }
        .form-header-left, .form-header-right { display: flex; align-items: center; gap: 8px; }
        .form-title { margin: 0; font-size: 22px; font-weight: 600; }
        .fields { display: grid; grid-template-columns: repeat(3, minmax(160px, 1fr)); gap: 12px; margin-bottom: 14px; }
        .field label { display: block; font-size: 12px; margin-bottom: 4px; color: #605e5c; font-weight: 500; }
        .field input, .field select, .field textarea { width: 100%; border: 1px solid #8a8886; border-radius: 2px; padding: 6px 8px; font-size: 13px; background: #fff; font-family: inherit; }
        .field textarea { resize: vertical; min-height: 60px; }
        .field input[readonly] { background: #f3f2f1; color: #605e5c; cursor: not-allowed; }
        .span-3 { grid-column: 1 / -1; }
        .span-2 { grid-column: span 2; }
        .status-box { margin-bottom: 10px; padding: 8px 10px; border-radius: 2px; font-size: 13px; display: none; }
        .status-box.success { display: block; background: #e8f6ee; color: #1f7a48; }
        .status-box.error   { display: block; background: #fde7e9; color: #a4262c; }
        .section-title { font-size: 13px; font-weight: 600; color: #323130; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center; }
        .lines-wrap { border: 1px solid #edebe9; border-radius: 2px; overflow: auto; margin-bottom: 14px; }
        .line-input { width: 90px; border: 1px solid #8a8886; border-radius: 2px; padding: 4px 6px; font-size: 12px; }
        .line-input.wide { width: 320px; }
        .line-input.item-id { width: 180px; min-width: 180px; }
        .line-input.req-date { width: 140px; min-width: 140px; }
        .line-input.narrow { width: 60px; }
        .line-select { width: 140px; border: 1px solid #8a8886; border-radius: 2px; padding: 4px 6px; font-size: 12px; background: #fff; }
        .unit-select { width: 150px; min-width: 150px; }
        .unit-note { font-size: 10px; color: #8a8886; margin-top: 2px; }
        .line-toggle-btn {
            border: 0;
            background: transparent;
            color: #605e5c;
            cursor: pointer;
            padding: 2px 4px;
            width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .line-toggle-btn .chev {
            width: 8px;
            height: 8px;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            transform: rotate(-45deg);
            transition: transform .15s ease;
        }
        .line-toggle-btn[aria-expanded="true"] .chev {
            transform: rotate(45deg);
        }
        .line-serial { font-size: 11px; color: #605e5c; min-width: 24px; text-align: center; font-weight: 600; }
        .icon-btn-danger {
            border: 1px solid #a4262c;
            background: #a4262c;
            color: #fff;
            border-radius: 4px;
            width: 28px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .icon-btn-danger:hover { background: #8f1d22; border-color: #8f1d22; }
        .line-details-row td { background: #faf9f8; border-bottom: 1px solid #edebe9; }
        .line-details-shell { padding: 8px 4px; }
        .line-details-title { font-size: 12px; font-weight: 600; color: #605e5c; margin: 0 0 8px; }
        .line-details-grid { display: grid; grid-template-columns: repeat(2, minmax(160px, 1fr)); gap: 10px 12px; }
        .line-details-field label { display: block; font-size: 11px; color: #605e5c; margin-bottom: 3px; font-weight: 500; }
        .line-details-field input, .line-details-field select { width: 100%; border: 1px solid #8a8886; border-radius: 2px; padding: 6px 8px; font-size: 12px; background: #fff; }
        .attach-zone { border: 2px dashed #c8c6c4; border-radius: 4px; padding: 20px 16px; text-align: center; cursor: pointer; color: #605e5c; font-size: 13px; margin-bottom: 10px; transition: border-color .2s, background .2s; }
        .attach-zone:hover, .attach-zone.drag-over { border-color: #106ebe; background: #f0f7ff; color: #106ebe; }
        .attach-list { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
        .attach-chip { display: flex; align-items: center; gap: 6px; background: #f3f2f1; border: 1px solid #edebe9; border-radius: 14px; padding: 5px 12px; font-size: 12px; }
        .attach-chip .file-icon { font-size: 14px; }
        .attach-chip .file-info { display: flex; flex-direction: column; line-height: 1.3; }
        .attach-chip .file-name { font-weight: 500; }
        .attach-chip .file-size { font-size: 10px; color: #8a8886; }
        .attach-chip .remove { cursor: pointer; color: #a4262c; font-weight: 700; line-height: 1; margin-left: 2px; }
        .history-wrap { margin-top: 16px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; background: #dff6dd; color: #107c10; }
        .badge-count { background: #deecf9; color: #005a9e; }
        .att-link { display: inline-flex; align-items: center; gap: 3px; color: #106ebe; text-decoration: none; font-size: 11px; padding: 2px 6px; border-radius: 10px; background: #deecf9; margin: 1px; white-space: nowrap; }
        .att-link:hover { background: #c7e0f4; }
    </style>
</head>
<body>
    @include('partials.global-company-selector')
    @php
        $buyingLegalEntities = collect($companies)
            ->filter(fn ($c) => in_array(strtoupper((string) $c->d365_id), ['TM', 'PS'], true))
            ->map(fn ($c) => ['code' => strtoupper((string) $c->d365_id), 'name' => $c->name])
            ->unique('code')
            ->values();
    @endphp
    <aside class="sidebar">
        <div class="logo">Logo</div>
        <div class="label">Menu</div>
        <a class="menu-link" href="{{ route('dashboard') }}">Dashboard</a>
        <a class="menu-link" href="{{ route('masters.company.index') }}">Masters</a>
        <a class="menu-link" href="#">Modules</a>
        <div class="sub">
            <a class="menu-link" href="#">Project Management</a>
            <a class="menu-link" href="{{ route('modules.project-management.item-issue') }}">Item Issue</a>
            <a class="menu-link active" href="#">Procurement &amp; Sourcing</a>
            <div class="sub">
                <a class="menu-link active" href="{{ route('modules.procurement.purch-req') }}">Purchase Requisition</a>
            </div>
        </div>
        <a class="menu-link" href="{{ route('settings.index') }}" style="display:flex;align-items:center;gap:6px;margin-top:8px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
            </svg>
            Settings
        </a>
    </aside>

    <main class="main pr-layout">
        <div id="pr-form-shell" class="page-shell hidden" style="order:2;display:none;">
            <div class="command-bar">
                <div class="crumb">Modules / Procurement &amp; Sourcing / Purchase Requisition</div>
            </div>
            <div style="padding:14px;">
                <div class="form-wrap">
                    <div class="form-header">
                        <div class="form-header-left">
                            <button id="back-to-list-btn" class="btn btn-sm" type="button">← Back to List</button>
                            <h2 class="form-title">Purchase Requisition</h2>
                        </div>
                        <div class="form-header-right">
                            <button id="reset-btn" class="btn btn-sm" type="button">Reset</button>
                            <button id="save-btn" class="btn btn-sm" type="button">Save Draft</button>
                            <button id="post-btn" class="btn btn-primary" type="button">Submit PR to D365</button>
                        </div>
                    </div>

                    <div id="status-box" class="status-box"></div>

                    <div class="fields">
                        <div class="field">
                            <label>Company <span style="color:#a4262c">*</span></label>
                            <select id="company">
                                <option value="">— select —</option>
                                @foreach($companies as $c)
                                    <option value="{{ $c->d365_id }}" {{ strtoupper((string) ($currentCompanyCode ?? '')) === strtoupper((string) $c->d365_id) ? 'selected' : '' }}>{{ $c->d365_id }} – {{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Buying Legal Entity</label>
                            <select id="buying-legal-entity">
                                @if($buyingLegalEntities->isEmpty())
                                    <option value="">— select company first —</option>
                                @endif
                                @foreach($buyingLegalEntities as $entity)
                                    <option value="{{ $entity['code'] }}">{{ $entity['code'] }} - {{ $entity['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Request ID</label>
                            <input id="request-id" type="text" readonly placeholder="Auto-assigned on submit">
                        </div>
                        <div class="field">
                            <label>PR No</label>
                            <input id="pr-no" type="text" readonly placeholder="Auto-assigned on submit">
                        </div>
                        <div class="field">
                            <label>PR Date <span style="color:#a4262c">*</span></label>
                            <input id="pr-date" type="date">
                        </div>
                        <div class="field">
                            <label>Warehouse <span style="color:#a4262c">*</span></label>
                            <input id="warehouse" type="text" placeholder="e.g. PSE20251008">
                        </div>
                        <div class="field">
                            <label>Pool ID <span style="color:#a4262c">*</span></label>
                            <input id="pool-id" type="text" placeholder="e.g. P_LPO">
                        </div>
                        <div class="field">
                            <label>Contact Name <span style="color:#a4262c">*</span></label>
                            <input id="contact-name" type="text" placeholder="e.g. Murugan">
                        </div>
                        <div class="field">
                            <label>Department <span style="color:#a4262c">*</span></label>
                            <input id="department" type="text" placeholder="e.g. Procurement">
                        </div>
                        <div class="field span-2">
                            <label>Remarks</label>
                            <textarea id="remarks" placeholder="Optional remarks..."></textarea>
                        </div>
                    </div>

                    <div class="section-title">
                        <span>PR Lines</span>
                        <div style="display:flex; gap:6px;">
                            <button id="add-line-btn" class="btn btn-sm" type="button">+ Add Line</button>
                        </div>
                    </div>
                    <div class="lines-wrap">
                        <table id="lines-table">
                            <thead>
                                <tr>
                                    <th style="width:28px;"></th>
                                    <th style="width:50px;">Line</th>
                                    <th>Item Category</th>
                                    <th>Item ID</th>
                                    <th>Description</th>
                                    <th>Required Date</th>
                                    <th>Unit</th>
                                    <th>Qty</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="lines-body">
                                <tr id="no-lines-row">
                                    <td colspan="9" class="empty-note">No lines yet — click <strong>+ Add Line</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="section-title" style="margin-top:4px;">
                        <span>Attachments</span>
                        <small style="font-weight:400;color:#8a8886;">PDF · DOC · DOCX · XLS · XLSX</small>
                    </div>
                    <div class="attach-zone" id="attach-zone">
                        📎 &nbsp;Click or drag &amp; drop files here
                        <div style="font-size:11px;margin-top:4px;color:#8a8886;">Supported: PDF, DOC, DOCX, XLS, XLSX</div>
                    </div>
                    <input type="file" id="file-input"
                        accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                        multiple style="display:none">
                    <div class="attach-list" id="attach-list"></div>
                </div>
            </div>
        </div>

        <div id="pr-history-shell" class="page-shell" style="order:1;">
            <div class="command-bar">
                <div class="crumb">Modules / Procurement &amp; Sourcing / Purchase Requisition</div>
            </div>
            <div style="padding:14px;">
                <div class="toolbar" style="margin-bottom:10px;">
                    <div class="toolbar-row">
                        <div><h1 class="title" style="margin:0;">Purchase Requisition</h1></div>
                        <button id="create-pr-btn" class="btn btn-primary" type="button">+ Create New PR</button>
                    </div>
                </div>
                <div class="card">
                    <div class="card-head">Submitted Requisitions</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>PR No</th>
                                <th>Company</th>
                                <th>Warehouse</th>
                                <th>Contact</th>
                                <th>Lines</th>
                                <th>Attachments</th>
                                <th>Status</th>
                                <th>Submitted By</th>
                                <th>Submitted At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="history-body">
                            @php
                                $attIcon = function (string $type): string {
                                    $t = strtolower($type);
                                    if ($t === 'pdf') return '📄';
                                    if (in_array($t, ['doc', 'docx'])) return '📝';
                                    if (in_array($t, ['xls', 'xlsx'])) return '📊';
                                    return '📎';
                                };
                            @endphp
                            @forelse($journals as $j)
                            <tr>
                                <td><strong>{{ $j->request_id }}</strong></td>
                                <td>{{ $j->pr_no }}</td>
                                <td>{{ $j->company }}</td>
                                <td>{{ $j->warehouse }}</td>
                                <td>{{ $j->contact_name }}</td>
                                <td><span class="badge badge-count">{{ is_array($j->lines) ? count($j->lines) : 0 }}</span></td>
                                <td>
                                    @if(is_array($j->attachments) && count($j->attachments))
                                        @foreach($j->attachments as $idx => $att)
                                            @php
                                                $sizeLabel = isset($att['size_bytes']) ? number_format($att['size_bytes'] / 1024, 1) . ' KB' : '';
                                            @endphp
                                            <a class="att-link"
                                               href="{{ route('modules.procurement.purch-req.attachment', [$j->id, $idx]) }}"
                                               target="_blank"
                                               title="{{ $att['file_name'] }} ({{ $sizeLabel }})">
                                                {{ $attIcon($att['file_type'] ?? '') }}
                                                {{ $att['file_name'] }}
                                            </a>
                                            <a class="att-link"
                                               href="{{ route('modules.procurement.purch-req.attachment.base64', [$j->id, $idx]) }}"
                                               target="_blank"
                                               title="View raw Base64 for {{ $att['file_name'] }}"
                                               style="background:#fff4ce;color:#8a6914;">
                                                B64
                                            </a>
                                        @endforeach
                                    @else
                                        <span style="color:#8a8886;font-size:11px;">—</span>
                                    @endif
                                </td>
                                @php($isDraft = empty($j->request_id) && empty($j->pr_no))
                                <td><span class="badge {{ $isDraft ? '' : '' }}" style="{{ $isDraft ? 'background:#fff4ce;color:#8a6914;' : '' }}">{{ $isDraft ? 'Draft' : 'Submitted' }}</span></td>
                                <td>{{ $j->postedBy?->name ?? '—' }}</td>
                                <td>{{ $j->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm pr-view-btn" data-id="{{ $j->id }}">View</button>
                                    @if($isDraft)
                                        <button type="button" class="btn btn-sm pr-edit-btn" data-id="{{ $j->id }}">Edit</button>
                                    @endif
                                    <button type="button" class="btn btn-danger btn-sm pr-delete-btn" data-id="{{ $j->id }}">Delete</button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="12" class="empty-note">No requisitions submitted yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
    (() => {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        const statusBox     = document.getElementById('status-box');
        const companyEl     = document.getElementById('company');
        const buyingLegalEntityEl = document.getElementById('buying-legal-entity');
        const requestIdEl   = document.getElementById('request-id');
        const prNoEl        = document.getElementById('pr-no');
        const prDateEl      = document.getElementById('pr-date');
        const warehouseEl   = document.getElementById('warehouse');
        const poolIdEl      = document.getElementById('pool-id');
        const contactEl     = document.getElementById('contact-name');
        const remarksEl     = document.getElementById('remarks');
        const departmentEl  = document.getElementById('department');
        const linesBody     = document.getElementById('lines-body');
        const noLinesRow    = document.getElementById('no-lines-row');
        const historyBody   = document.getElementById('history-body');
        const postBtn       = document.getElementById('post-btn');
        const saveBtn       = document.getElementById('save-btn');
        const fileInput     = document.getElementById('file-input');
        const attachList    = document.getElementById('attach-list');
        const createPrBtn   = document.getElementById('create-pr-btn');
        const backToListBtn = document.getElementById('back-to-list-btn');
        const formShell     = document.getElementById('pr-form-shell');
        const historyShell  = document.getElementById('pr-history-shell');

        let attachments = [];
        let currentDraftId = null;
        let currentViewOnly = false;

        const showStatus = (msg, type) => {
            statusBox.textContent   = msg;
            statusBox.className     = `status-box ${type}`;
            statusBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        };
        const clearStatus = () => { statusBox.textContent = ''; statusBox.className = 'status-box'; };

        const todayStr = () => new Date().toISOString().slice(0, 10);
        prDateEl.value = todayStr();

        let lineCount = 0;

        const ITEM_CATEGORIES = [
            'Inventory Items',
            'Service Items',
            'Non-stock Items',
        ];

        function getLineDetailsRow(lineId) {
            return linesBody.querySelector(`tr[data-line-detail="${lineId}"]`);
        }

        function renumberLines() {
            const rows = Array.from(linesBody.querySelectorAll('tr[data-line]'));

            rows.forEach((row, idx) => {
                const lineNo = idx + 1;
                row.dataset.lineNo = String(lineNo);

                const serialEl = row.querySelector('.line-serial');
                if (serialEl) {
                    serialEl.textContent = String(lineNo);
                }

                const details = getLineDetailsRow(row.dataset.line);
                const titleEl = details?.querySelector('.line-details-title');
                if (titleEl) {
                    titleEl.textContent = `Additional Details (Line ${lineNo})`;
                }
            });

            noLinesRow.style.display = rows.length === 0 ? '' : 'none';
        }

        function addLine(line = {}) {
            noLinesRow.style.display = 'none';
            lineCount++;
            const lineId = lineCount;

            const catOptions = ITEM_CATEGORIES.map(c =>
                `<option value="${c}">${c}</option>`
            ).join('');

            const row = document.createElement('tr');
            row.dataset.line = lineId;
            row.innerHTML = `
                <td style="text-align:center;">
                    <button type="button" class="line-toggle-btn toggle-line" data-line="${lineId}" aria-expanded="false" title="Expand details">
                        <span class="chev"></span>
                    </button>
                </td>
                <td style="text-align:center;"><span class="line-serial"></span></td>
                <td><select class="line-select lf-category"><option value="">—</option>${catOptions}</select></td>
                <td><input class="line-input item-id lf-item-id" type="text" maxlength="100" placeholder="Item ID" value="${line.item_id ?? ''}"></td>
                <td><input class="line-input wide lf-desc" type="text" maxlength="255" placeholder="Description (up to 255 characters)" value="${line.item_description ?? ''}"></td>
                <td><input class="line-input req-date lf-req-date" type="date" value="${line.required_date ?? todayStr()}"></td>
                <td>
                    <select class="line-select unit-select lf-unit">
                        <option value="${line.unit ?? ''}">${line.unit ? line.unit : 'Select item first'}</option>
                    </select>
                    <div class="unit-note lf-unit-note"></div>
                </td>
                <td><input class="line-input narrow lf-qty" type="number" min="0.001" step="any" value="${line.qty ?? 1}"></td>
                <td>
                    <button class="icon-btn-danger remove-line" type="button" title="Delete line" aria-label="Delete line">
                        🗑
                    </button>
                </td>
            `;

            const details = document.createElement('tr');
            details.dataset.lineDetail = lineId;
            details.className = 'line-details-row hidden';
            details.innerHTML = `
                <td colspan="9">
                    <div class="line-details-shell">
                        <div class="line-details-title">Additional Details</div>
                        <div class="line-details-grid">
                            <div class="line-details-field">
                                <label>Currency</label>
                                <input class="lf-currency" type="text" value="${line.currency ?? 'AED'}" placeholder="Currency">
                            </div>
                            <div class="line-details-field">
                                <label>Rate</label>
                                <input class="lf-rate" type="number" min="0" step="any" value="${line.rate ?? 0}" placeholder="Rate">
                            </div>
                            <div class="line-details-field">
                                <label>Candy Budget</label>
                                <input class="lf-budget" type="number" min="0" step="any" value="${line.candy_budget ?? 0}" placeholder="Candy Budget">
                            </div>
                            <div class="line-details-field">
                                <label>Budget Resource</label>
                                <input class="lf-budget-res" type="text" placeholder="Budget resource code" value="${line.budget_resource_id ?? ''}">
                            </div>
                            <div class="line-details-field">
                                <label>Warranty</label>
                                <input class="lf-warranty" type="text" value="${line.warranty ?? 'N/A'}" placeholder="Warranty">
                            </div>
                        </div>
                    </div>
                </td>
            `;

            linesBody.appendChild(row);
            linesBody.appendChild(details);
            renumberLines();
        }

        document.getElementById('add-line-btn').addEventListener('click', () => addLine());

        linesBody.addEventListener('click', (e) => {
            const toggleBtn = e.target.closest('.toggle-line');
            if (toggleBtn) {
                const lineId = toggleBtn.dataset.line;
                const details = getLineDetailsRow(lineId);
                if (!details) return;
                const opening = details.classList.contains('hidden');
                details.classList.toggle('hidden', !opening);
                toggleBtn.setAttribute('aria-expanded', String(opening));
                toggleBtn.setAttribute('title', opening ? 'Collapse details' : 'Expand details');
                return;
            }

            const removeBtn = e.target.closest('.remove-line');
            if (!removeBtn) return;
            if (currentViewOnly) return;
            const mainRow = removeBtn.closest('tr[data-line]');
            if (!mainRow) return;
            const lineId = mainRow.dataset.line;
            const details = getLineDetailsRow(lineId);
            mainRow.remove();
            if (details) details.remove();
            renumberLines();
        });

        async function loadUnitsForRow(tr) {
            const itemId = tr.querySelector('.lf-item-id')?.value?.trim() ?? '';
            const unitSelect = tr.querySelector('.lf-unit');
            const unitNote = tr.querySelector('.lf-unit-note');
            const company = (buyingLegalEntityEl?.value || companyEl.value || '').trim();

            if (!unitSelect || !unitNote) return;

            if (!company) {
                unitSelect.innerHTML = '<option value="">Select company first</option>';
                unitNote.textContent = '';
                return;
            }

            if (!itemId) {
                unitSelect.innerHTML = '<option value="">Enter item ID</option>';
                unitNote.textContent = '';
                return;
            }

            unitSelect.disabled = true;
            unitSelect.innerHTML = '<option value="">Loading...</option>';
            unitNote.textContent = 'Fetching units from D365...';

            try {
                const res = await fetch('{{ route("modules.procurement.purch-req.api.units") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        _token: csrf,
                        company,
                        item_id: itemId,
                    }),
                });

                const payload = await res.json();

                if (!res.ok || !payload.status) {
                    throw new Error(payload.error || payload.message || 'Unit lookup failed.');
                }

                const units = Array.isArray(payload.units) ? payload.units : [];

                if (units.length === 0) {
                    unitSelect.innerHTML = '<option value="">No units found</option>';
                    unitNote.textContent = 'No unit returned by D365 for this item.';
                    return;
                }

                unitSelect.innerHTML = units
                    .map(u => `<option value="${u.id}">${u.id}${u.name && u.name !== u.id ? ' - ' + u.name : ''}</option>`)
                    .join('');

                if (units.length === 1) {
                    unitSelect.value = units[0].id;
                    unitNote.textContent = `Auto-selected: ${units[0].id}`;
                } else {
                    unitSelect.selectedIndex = 0;
                    unitNote.textContent = `${units.length} units found.`;
                }
            } catch (err) {
                unitSelect.innerHTML = '<option value="">Unit lookup failed</option>';
                unitNote.textContent = err.message || 'Unit lookup failed.';
            } finally {
                unitSelect.disabled = false;
            }
        }

        linesBody.addEventListener('change', (e) => {
            if (e.target.classList.contains('lf-item-id')) {
                const tr = e.target.closest('tr');
                if (tr) loadUnitsForRow(tr);
            }
        });

        linesBody.addEventListener('blur', (e) => {
            if (e.target.classList.contains('lf-item-id')) {
                const tr = e.target.closest('tr');
                if (tr) loadUnitsForRow(tr);
            }
        }, true);

        companyEl.addEventListener('change', () => {
            const companyCode = (companyEl.value || '').trim().toUpperCase();
            if (buyingLegalEntityEl && ['TM', 'PS'].includes(companyCode)) {
                buyingLegalEntityEl.value = companyCode;
            }
            linesBody.querySelectorAll('tr[data-line]').forEach((tr) => {
                const itemId = tr.querySelector('.lf-item-id')?.value?.trim() ?? '';
                const unitSelect = tr.querySelector('.lf-unit');
                const unitNote = tr.querySelector('.lf-unit-note');
                if (!unitSelect || !unitNote) return;

                if (!itemId) {
                    unitSelect.innerHTML = '<option value="">Enter item ID</option>';
                    unitNote.textContent = '';
                    return;
                }

                loadUnitsForRow(tr);
            });
        });
        buyingLegalEntityEl.addEventListener('change', () => {
            linesBody.querySelectorAll('tr[data-line]').forEach((tr) => {
                const itemId = tr.querySelector('.lf-item-id')?.value?.trim() ?? '';
                const unitSelect = tr.querySelector('.lf-unit');
                const unitNote = tr.querySelector('.lf-unit-note');
                if (!unitSelect || !unitNote) return;

                if (!itemId) {
                    unitSelect.innerHTML = '<option value="">Enter item ID</option>';
                    unitNote.textContent = '';
                    return;
                }

                loadUnitsForRow(tr);
            });
        });

        const attachZone = document.getElementById('attach-zone');
        attachZone.addEventListener('click', () => fileInput.click());
        attachZone.addEventListener('dragover', (e) => { e.preventDefault(); attachZone.classList.add('drag-over'); });
        attachZone.addEventListener('dragleave', () => attachZone.classList.remove('drag-over'));
        attachZone.addEventListener('drop', async (e) => {
            e.preventDefault();
            attachZone.classList.remove('drag-over');
            await processFiles(e.dataTransfer.files);
        });

        fileInput.addEventListener('change', async () => {
            await processFiles(fileInput.files);
            fileInput.value = '';
        });

        async function processFiles(fileList) {
            const rejected = [];
            for (const file of fileList) {
                const ext = file.name.split('.').pop().toLowerCase();
                if (!ALLOWED_EXTS.includes(ext)) { rejected.push(file.name); continue; }
                const b64 = await fileToBase64(file);
                attachments.push({
                    fileName:    file.name,
                    fileType:    ext,
                    mimeType:    file.type || 'application/octet-stream',
                    sizeBytes:   file.size,
                    fileContent: b64,
                    purchId:     '',
                });
            }
            if (rejected.length) {
                showStatus(`⚠ Unsupported file type(s) skipped: ${rejected.join(', ')} — only PDF, DOC, DOCX, XLS, XLSX allowed.`, 'error');
            }
            renderAttachments();
        }

        function fileToBase64(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload  = () => resolve(reader.result.split(',')[1]);
                reader.onerror = reject;
                reader.readAsDataURL(file);
            });
        }

        const ALLOWED_EXTS = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];

        function fileIcon(ext) {
            if (ext === 'pdf') return '📄';
            if (['doc', 'docx'].includes(ext)) return '📝';
            if (['xls', 'xlsx'].includes(ext)) return '📊';
            return '📎';
        }

        function fmtSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }

        function renderAttachments() {
            attachList.innerHTML = attachments.map((a, i) => `
                <div class="attach-chip">
                    <span class="file-icon">${fileIcon(a.fileType)}</span>
                    <div class="file-info">
                        <span class="file-name">${a.fileName}</span>
                        <span class="file-size">${fmtSize(a.sizeBytes)}</span>
                    </div>
                    <span class="remove" data-idx="${i}" title="Remove">✕</span>
                </div>
            `).join('');
        }

        attachList.addEventListener('click', (e) => {
            const rem = e.target.closest('.remove');
            if (!rem) return;
            attachments.splice(+rem.dataset.idx, 1);
            renderAttachments();
        });

        function collectLines() {
            const rows = linesBody.querySelectorAll('tr[data-line]');
            const lines = [];
            for (const tr of rows) {
                const lineId = tr.dataset.line;
                const details = getLineDetailsRow(lineId);
                lines.push({
                    item_category:      tr.querySelector('.lf-category').value,
                    item_id:            tr.querySelector('.lf-item-id').value.trim(),
                    item_description:   tr.querySelector('.lf-desc').value.trim(),
                    required_date:      tr.querySelector('.lf-req-date').value,
                    unit:               tr.querySelector('.lf-unit').value.trim(),
                    qty:                tr.querySelector('.lf-qty').value,
                    currency:           details?.querySelector('.lf-currency')?.value?.trim() ?? 'AED',
                    rate:               details?.querySelector('.lf-rate')?.value ?? 0,
                    candy_budget:       details?.querySelector('.lf-budget')?.value ?? 0,
                    budget_resource_id: details?.querySelector('.lf-budget-res')?.value?.trim() ?? '',
                    warranty:           details?.querySelector('.lf-warranty')?.value?.trim() ?? 'N/A',
                });
            }
            return lines;
        }

        const setFormViewMode = (viewOnly) => {
            currentViewOnly = viewOnly;
            const fields = formShell.querySelectorAll('input, select, textarea, button');
            fields.forEach((el) => {
                if (['back-to-list-btn'].includes(el.id)) return;
                if (viewOnly) {
                    if (el.id === 'save-btn' || el.id === 'post-btn' || el.id === 'reset-btn' || el.id === 'add-line-btn') {
                        el.classList.add('hidden');
                    }
                    if (el.matches('input, select, textarea')) {
                        el.disabled = true;
                    }
                } else {
                    if (el.id === 'save-btn' || el.id === 'post-btn' || el.id === 'reset-btn' || el.id === 'add-line-btn') {
                        el.classList.remove('hidden');
                    }
                    if (el.matches('input, select, textarea')) {
                        el.disabled = false;
                    }
                }
            });
        };

        postBtn.addEventListener('click', async () => {
            if (currentViewOnly) return;
            clearStatus();

            const company = (buyingLegalEntityEl?.value || companyEl.value || '').trim();
            if (!company) { showStatus('Please select a company.', 'error'); return; }
            if (!prDateEl.value) { showStatus('PR Date is required.', 'error'); return; }
            if (!warehouseEl.value.trim()) { showStatus('Warehouse is required.', 'error'); return; }
            if (!poolIdEl.value.trim()) { showStatus('Pool ID is required.', 'error'); return; }
            if (!contactEl.value.trim()) { showStatus('Contact Name is required.', 'error'); return; }
            if (!departmentEl.value.trim()) { showStatus('Department is required.', 'error'); return; }

            const lines = collectLines();
            if (lines.length === 0) { showStatus('Add at least one line.', 'error'); return; }

            for (let i = 0; i < lines.length; i++) {
                const ln = lines[i];
                if (!ln.item_category) { showStatus(`Line ${i + 1}: Item Category is required.`, 'error'); return; }
                if (!ln.item_id) { showStatus(`Line ${i + 1}: Item ID is required.`, 'error'); return; }
                if (!ln.unit) { showStatus(`Line ${i + 1}: Unit is required.`, 'error'); return; }
                if (!ln.required_date) { showStatus(`Line ${i + 1}: Required Date is required.`, 'error'); return; }
                if (parseFloat(ln.qty) <= 0) { showStatus(`Line ${i + 1}: Qty must be > 0.`, 'error'); return; }
            }

            postBtn.disabled = true;
            postBtn.textContent = 'Submitting…';

            const payload = {
                _token:       csrf,
                company:      company,
                buying_legal_entity: (buyingLegalEntityEl?.value || company),
                pr_date:      prDateEl.value,
                warehouse:    warehouseEl.value.trim(),
                pool_id:      poolIdEl.value.trim(),
                contact_name: contactEl.value.trim(),
                remarks:      remarksEl.value.trim(),
                department:   departmentEl.value.trim(),
                lines:        lines,
                attachments:  attachments.map(a => ({
                    file_name:    a.fileName,
                    file_type:    a.fileType,
                    mime_type:    a.mimeType,
                    size_bytes:   a.sizeBytes,
                    file_content: a.fileContent,
                    purch_id:     a.purchId,
                })),
            };
            if (currentDraftId) {
                payload.draft_id = currentDraftId;
            }

            try {
                const res  = await fetch('{{ route("modules.procurement.purch-req.post") }}', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body:    JSON.stringify(payload),
                });

                const rawText = await res.text();
                let data;
                try {
                    data = JSON.parse(rawText);
                } catch (_) {
                    const plain = rawText.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim().substring(0, 500);
                    showStatus('✗ Server error (HTTP ' + res.status + '): ' + plain, 'error');
                    postBtn.disabled    = false;
                    postBtn.textContent = 'Submit PR to D365';
                    return;
                }

                if (data.status) {
                    requestIdEl.value = data.request_id ?? '';
                    prNoEl.value      = data.pr_no ?? '';
                    showStatus(`✓ PR submitted. Request ID: ${data.request_id}  |  PR No: ${data.pr_no}`, 'success');
                    addToHistory(data, payload);
                    resetForm();
                    currentDraftId = null;
                    setFormViewMode(false);
                    formShell.classList.add('hidden');
                    historyShell.classList.remove('hidden');
                    formShell.style.display = 'none';
                    historyShell.style.display = '';
                } else {
                    showStatus('✗ ' + (data.error ?? data.message ?? 'Submission failed.'), 'error');
                }
            } catch (err) {
                showStatus('✗ Network error: ' + err.message, 'error');
            } finally {
                postBtn.disabled   = false;
                postBtn.textContent = 'Submit PR to D365';
            }
        });

        saveBtn.addEventListener('click', async () => {
            if (currentViewOnly) return;
            clearStatus();

            const payload = {
                _token: csrf,
                company: companyEl.value.trim() || null,
                buying_legal_entity: (buyingLegalEntityEl?.value || companyEl.value.trim() || null),
                pr_date: prDateEl.value || null,
                warehouse: warehouseEl.value.trim() || null,
                pool_id: poolIdEl.value.trim() || null,
                contact_name: contactEl.value.trim() || null,
                remarks: remarksEl.value.trim() || null,
                department: departmentEl.value.trim() || null,
                lines: collectLines(),
                attachments: attachments.map(a => ({
                    file_name: a.fileName,
                    file_type: a.fileType,
                    mime_type: a.mimeType,
                    size_bytes: a.sizeBytes,
                    file_content: a.fileContent,
                    purch_id: a.purchId,
                })),
            };

            const isEdit = Boolean(currentDraftId);
            const url = isEdit
                ? `{{ route("modules.procurement.purch-req.drafts.update", ["journal" => "__ID__"]) }}`.replace('__ID__', currentDraftId)
                : `{{ route("modules.procurement.purch-req.save") }}`;
            const method = isEdit ? 'PUT' : 'POST';

            saveBtn.disabled = true;
            saveBtn.textContent = isEdit ? 'Updating…' : 'Saving…';
            try {
                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();
                if (!res.ok || !data.status) {
                    throw new Error(data.message || data.error || 'Save failed.');
                }
                currentDraftId = data.journal_id;
                showStatus('✓ Draft saved successfully.', 'success');
            } catch (err) {
                showStatus('✗ ' + err.message, 'error');
            } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Draft';
            }
        });

        function addToHistory(data, payload) {
            const emptyRow = historyBody.querySelector('td[colspan]');
            if (emptyRow) emptyRow.closest('tr').remove();

            const now = new Date();
            const fmt = now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
                      + ' ' + now.toTimeString().slice(0, 5);

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong>${data.request_id ?? '—'}</strong></td>
                <td>${data.pr_no ?? '—'}</td>
                <td>${payload.company}</td>
                <td>${payload.warehouse}</td>
                <td>${payload.contact_name}</td>
                <td><span class="badge badge-count">${payload.lines.length}</span></td>
                <td>${payload.attachments.length
                    ? payload.attachments.map(a => `<span class="att-link">${fileIcon(a.file_type)} ${a.file_name}</span>`).join(' ')
                    : '<span style="color:#8a8886;font-size:11px;">—</span>'}</td>
                <td><span class="badge">Submitted</span></td>
                <td>You</td>
                <td>${fmt}</td>
                <td>
                    <button type="button" class="btn btn-sm pr-view-btn" data-id="${data.journal_id}">View</button>
                    <button type="button" class="btn btn-danger btn-sm pr-delete-btn" data-id="${data.journal_id}">Delete</button>
                </td>
            `;
            historyBody.prepend(tr);
        }

        function resetForm() {
            prNoEl.value        = '';
            requestIdEl.value   = '';
            if (buyingLegalEntityEl) buyingLegalEntityEl.value = '';
            prDateEl.value      = todayStr();
            warehouseEl.value   = '';
            poolIdEl.value      = '';
            contactEl.value     = '';
            remarksEl.value     = '';
            departmentEl.value  = '';
            linesBody.querySelectorAll('tr[data-line]').forEach(r => r.remove());
            linesBody.querySelectorAll('tr[data-line-detail]').forEach(r => r.remove());
            lineCount    = 0;
            attachments  = [];
            renderAttachments();
            renumberLines();
            currentDraftId = null;
        }

        document.getElementById('reset-btn').addEventListener('click', () => {
            clearStatus();
            resetForm();
        });

        createPrBtn.addEventListener('click', () => {
            resetForm();
            setFormViewMode(false);
            historyShell.classList.add('hidden');
            formShell.classList.remove('hidden');
            historyShell.style.display = 'none';
            formShell.style.display = '';
            clearStatus();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        backToListBtn.addEventListener('click', () => {
            formShell.classList.add('hidden');
            historyShell.classList.remove('hidden');
            formShell.style.display = 'none';
            historyShell.style.display = '';
            clearStatus();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        historyBody.addEventListener('click', async (e) => {
            const viewBtn = e.target.closest('.pr-view-btn');
            const editBtn = e.target.closest('.pr-edit-btn');
            const deleteBtn = e.target.closest('.pr-delete-btn');

            if (!viewBtn && !editBtn && !deleteBtn) return;

            const rowId = (viewBtn || editBtn || deleteBtn).dataset.id;

            if (deleteBtn) {
                if (!confirm('Delete this PR record?')) return;
                try {
                    const url = `{{ route("modules.procurement.purch-req.journals.destroy", ["journal" => "__ID__"]) }}`.replace('__ID__', rowId);
                    const res = await fetch(url, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } });
                    const data = await res.json();
                    if (!res.ok || !data.status) throw new Error(data.message || data.error || 'Delete failed.');
                    (viewBtn || editBtn || deleteBtn).closest('tr')?.remove();
                    showStatus('✓ PR deleted.', 'success');
                } catch (err) {
                    showStatus('✗ ' + err.message, 'error');
                }
                return;
            }

            try {
                const url = `{{ route("modules.procurement.purch-req.journals.show", ["journal" => "__ID__"]) }}`.replace('__ID__', rowId);
                const res = await fetch(url, { headers: { Accept: 'application/json' } });
                const payload = await res.json();
                if (!res.ok || !payload.status) throw new Error(payload.message || payload.error || 'Failed to load PR.');

                const j = payload.data;
                resetForm();
                companyEl.value = j.company || '';
                buyingLegalEntityEl.value = j.buying_legal_entity || '';
                requestIdEl.value = j.request_id || '';
                prNoEl.value = j.pr_no || '';
                prDateEl.value = j.pr_date || '';
                warehouseEl.value = j.warehouse || '';
                poolIdEl.value = j.pool_id || '';
                contactEl.value = j.contact_name || '';
                remarksEl.value = j.remarks || '';
                departmentEl.value = j.department || '';

                (Array.isArray(j.lines) ? j.lines : []).forEach((line) => addLine(line));

                attachments = (Array.isArray(j.attachments) ? j.attachments : []).map((a) => ({
                    fileName: a.file_name || '',
                    fileType: a.file_type || '',
                    mimeType: a.mime_type || '',
                    sizeBytes: Number(a.size_bytes || 0),
                    fileContent: a.file_content || '',
                    purchId: '',
                }));
                renderAttachments();

                currentDraftId = payload.is_draft ? j.id : null;
                setFormViewMode(Boolean(viewBtn));

                historyShell.classList.add('hidden');
                formShell.classList.remove('hidden');
                historyShell.style.display = 'none';
                formShell.style.display = '';
                clearStatus();
            } catch (err) {
                showStatus('✗ ' + err.message, 'error');
            }
        });

        formShell.classList.add('hidden');
        historyShell.classList.remove('hidden');
        formShell.style.display = 'none';
        historyShell.style.display = '';
    })();
    </script>
</body>
</html>
