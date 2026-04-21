<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Item Issue</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f3f2f1;
            color: #323130;
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 260px;
            background: #fff;
            border-right: 1px solid #edebe9;
            padding: 12px 0;
        }
        .logo { padding: 10px 16px 18px; border-bottom: 1px solid #edebe9; margin-bottom: 8px; font-weight: 700; }
        .label { padding: 10px 16px 4px; color: #8a8886; font-size: 11px; text-transform: uppercase; }
        .menu-link { display: block; padding: 10px 16px; color: #323130; text-decoration: none; border-radius: 8px; margin: 2px 8px; font-size: 14px; }
        .menu-link:hover { background: #f3f2f1; }
        .menu-link.active { background: #deecf9; color: #005a9e; }
        .sub { margin-left: 16px; padding-left: 8px; border-left: 2px solid #edebe9; }
        .main { flex: 1; padding: 12px 16px; overflow: auto; }
        .page-shell { border: 1px solid #edebe9; background: #fff; border-radius: 2px; overflow: hidden; }
        .command-bar { height: 44px; border-bottom: 1px solid #edebe9; background: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 12px; }
        .crumb { font-size: 12px; color: #605e5c; }
        .toolbar { margin-bottom: 12px; }
        .toolbar-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
        .title { margin: 0 0 4px; font-size: 24px; font-weight: 600; }
        .search { width: 240px; border: 1px solid #8a8886; border-radius: 2px; padding: 7px 10px; font-size: 13px; margin-top: 10px; }
        .btn { border: 1px solid #8a8886; background: #fff; color: #323130; border-radius: 2px; padding: 6px 12px; font-size: 12px; font-weight: 600; cursor: pointer; }
        .btn-primary { border-color: #106ebe; background: #106ebe; color: #fff; }
        .btn-light { background: #fff; color: #106ebe; border-color: #c7e0f4; }
        .btn:disabled { border-color: #edebe9; background: #f3f2f1; color: #a19f9d; cursor: not-allowed; }
        .card { background: #fff; border: 1px solid #edebe9; border-radius: 2px; overflow: hidden; }
        .card-head { padding: 12px 14px; border-bottom: 1px solid #edebe9; font-size: 20px; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #edebe9; padding: 8px 10px; text-align: left; font-size: 13px; }
        th { color: #605e5c; font-weight: 600; background: #faf9f8; white-space: nowrap; }
        .empty-note { text-align: center; color: #8a8886; padding: 22px 10px; font-size: 13px; }
        .journal-form { background: #fff; border: 1px solid #edebe9; border-radius: 2px; padding: 14px; }
        .journal-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 14px; }
        .journal-left { display: flex; align-items: center; gap: 10px; }
        .journal-title { margin: 0; font-size: 28px; font-weight: 600; }
        .journal-actions { display: flex; gap: 8px; }
        .fields { display: grid; grid-template-columns: repeat(3, minmax(160px, 1fr)); gap: 12px; margin-bottom: 14px; }
        .field label { display: block; font-size: 12px; margin-bottom: 4px; color: #605e5c; font-weight: 500; }
        .field input, .field select { width: 100%; border: 1px solid #8a8886; border-radius: 2px; padding: 6px 8px; font-size: 13px; background: #fff; }
        .field input[readonly] { background: #f3f2f1; color: #605e5c; cursor: not-allowed; }
        .status-box { margin-bottom: 10px; padding: 8px 10px; border-radius: 2px; font-size: 13px; display: none; }
        .status-box.success { display: block; background: #e8f6ee; color: #1f7a48; }
        .status-box.error { display: block; background: #fde7e9; color: #a4262c; }
        .lines-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .lines-toolbar-title { font-size: 13px; font-weight: 600; color: #323130; }
        .line-area { border: 1px solid #edebe9; border-radius: 2px; overflow: auto; }
        .line-select { width: 100%; min-width: 160px; border: 1px solid #8a8886; border-radius: 2px; padding: 5px 7px; font-size: 12px; background: #fff; }
        .line-input { width: 80px; border: 1px solid #8a8886; border-radius: 2px; padding: 5px 7px; font-size: 12px; }
        .onhand-badge { display: inline-block; font-size: 11px; padding: 2px 7px; border-radius: 10px; margin-top: 3px; background: #f3f2f1; color: #605e5c; }
        .onhand-badge.ok { background: #dff6dd; color: #107c10; }
        .onhand-badge.low { background: #fff4ce; color: #8a6914; }
        .onhand-badge.zero { background: #fde7e9; color: #a4262c; }
        .unit-loading { font-size: 11px; color: #8a8886; font-style: italic; }
        .hidden { display: none; }
        .loading-overlay { font-size: 12px; color: #8a8886; padding: 6px 10px; }
 
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo">Logo</div>
        <div class="label">Menu</div>
        <a class="menu-link" href="{{ route('dashboard') }}">Dashboard</a>
        <a class="menu-link" href="{{ route('masters.company.index') }}">Masters</a>
        <a class="menu-link active" href="#">Modules</a>
        <div class="sub">
            <a class="menu-link active" href="#">Project Management</a>
            <a class="menu-link active" href="{{ route('modules.project-management.item-issue') }}">Item Issue</a>
            <a class="menu-link" href="#">Procurement &amp; Sourcing</a>
        </div>
        <a class="menu-link" href="{{ route('settings.index') }}" style="display:flex;align-items:center;gap:6px;margin-top:8px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
            </svg>
            Settings
        </a>
    </aside>
 
    <main class="main">
        <div class="page-shell">
            <div class="command-bar">
                <div class="crumb">Modules / Project Management / Item Issue</div>
            </div>
            <div style="padding:12px;">
 
                {{-- Journal list --}}
                <div id="journal-toolbar" class="toolbar">
                    <div class="toolbar-row">
                        <div><h1 class="title">Item Issue</h1></div>
                        <button id="create-journal-btn" class="btn btn-primary" type="button">+ Create New Journal</button>
                    </div>
                    <input class="search" type="text" placeholder="Search journals..." disabled>
                </div>
 
                <div id="journal-list-view" class="card">
                    <div class="card-head">Journals</div>
                    <div style="overflow:auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Request ID</th>
                                    <th>Journal ID</th>
                                    <th>Company</th>
                                    <th>Project</th>
                                    <th>Lines</th>
                                    <th>Posted By</th>
                                    <th>Posted At</th>
                                </tr>
                            </thead>
                            <tbody id="journals-list-body">
                                @forelse($journals as $journal)
                                <tr>
                                    <td>{{ $journal->request_id ?? '—' }}</td>
                                    <td><strong>{{ $journal->journal_id ?? '—' }}</strong></td>
                                    <td>{{ $journal->company ?? '—' }}</td>
                                    <td>{{ $journal->project_id ?? '—' }}</td>
                                    <td>{{ count($journal->lines ?? []) }}</td>
                                    <td>{{ $journal->postedBy?->name ?? 'System' }}</td>
                                    <td>{{ $journal->created_at->format('d M Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr><td class="empty-note" colspan="7">No journal records yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
 
                {{-- Journal form --}}
                <div id="journal-form-view" class="journal-form hidden">
                    <div class="journal-header">
                        <div class="journal-left">
                            <button id="back-to-list-btn" class="btn" type="button">← Back</button>
                            <h2 class="journal-title">New Journal</h2>
                        </div>
                        <div class="journal-actions">
                            <button id="post-journal-btn" class="btn btn-primary" type="button">Post to D365</button>
                        </div>
                    </div>
 
                    <div id="status-box" class="status-box"></div>
 
                    {{-- Header: Journal ID · Company · Project · Tax Group · Tax Item Group --}}
                    <div class="fields" style="grid-template-columns: repeat(5, minmax(140px, 1fr));">
                        <div class="field">
                            <label>Journal ID</label>
                            <input id="journal-id" type="text" value="Not Yet Posted" readonly>
                        </div>
                        <div class="field">
                            <label>Company</label>
                            <select id="company-id">
                                @foreach($companies as $company)
                                    <option value="{{ $company->d365_id }}">{{ $company->d365_id }} – {{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Project</label>
                            <select id="project-id">
                                <option value="">Select a project...</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->d365_id }}">{{ $project->d365_id }} – {{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Tax Group ID</label>
                            <input id="tax-group-id" type="text" value="C-DXB" placeholder="e.g. C-DXB">
                        </div>
                        <div class="field">
                            <label>Tax Item Group ID</label>
                            <input id="tax-item-group-id" type="text" value="" placeholder="optional">
                        </div>
                    </div>
 
                    {{-- Hidden values auto-derived from selections --}}
                    <input id="description"        type="hidden" value="Issue of items for project">
                    <input id="request-id"         type="hidden" value="">
                    <input id="invent-site-id"     type="hidden" value="">
                    <input id="invent-location-id" type="hidden" value="">
 
                    {{-- Items loading status --}}
                    <div id="items-loading-msg" class="loading-overlay hidden">⏳ Loading items from D365...</div>
 
                    {{-- Lines --}}
                    <div class="lines-toolbar">
                        <span class="lines-toolbar-title">Journal Lines</span>
                        <button id="new-line-btn" class="btn btn-light" type="button" disabled>+ New Line</button>
                    </div>
 
                    <div class="line-area">
                        <table id="lines-table" style="min-width:900px;">
                            <thead>
                                <tr>
                                    <th style="min-width:200px;">Item ID</th>
                                    <th style="min-width:240px;">Description</th>
                                    <th style="min-width:90px;">On Hand</th>
                                    <th style="min-width:110px;">Unit</th>
                                    <th style="min-width:80px;">Qty</th>
                                    <th style="width:70px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="journal-lines-body">
                                <tr><td class="empty-note" colspan="6">No lines added yet.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
 
            </div>
        </div>
    </main>
 
    <script>
        /* ── DOM refs ─────────────────────────────────────────────────── */
        const listView         = document.getElementById('journal-list-view');
        const formView         = document.getElementById('journal-form-view');
        const journalToolbar   = document.getElementById('journal-toolbar');
        const createBtn        = document.getElementById('create-journal-btn');
        const backBtn          = document.getElementById('back-to-list-btn');
        const newLineBtn       = document.getElementById('new-line-btn');
        const postBtn          = document.getElementById('post-journal-btn');
        const linesBody        = document.getElementById('journal-lines-body');
        const journalsListBody = document.getElementById('journals-list-body');
        const statusBox        = document.getElementById('status-box');
        const companySelect    = document.getElementById('company-id');
        const projectSelect    = document.getElementById('project-id');
        const itemsLoadingMsg  = document.getElementById('items-loading-msg');
        const csrfToken        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
 
        /* ── API endpoints ────────────────────────────────────────────── */
        const endpoints = {
            itemLookup: "{{ route('modules.project-management.item-issue.api.items.lookup') }}",
            units:      "{{ route('modules.project-management.item-issue.api.units') }}",
            post:       "{{ route('modules.project-management.item-issue.api.post') }}",
        };
 
        /* ── Item cache: Map<itemId → { id, name, onhandQty }> ─────────── */
        let itemsCache  = new Map();
        let itemsLoaded = false;
 
        /* ── Helpers ──────────────────────────────────────────────────── */
        const showStatus = (msg, type = 'success') => {
            statusBox.textContent = msg;
            statusBox.className   = `status-box ${type}`;
        };
        const clearStatus = () => { statusBox.textContent = ''; statusBox.className = 'status-box'; };
 
        const pad = (n) => String(n).padStart(2, '0');
        const generateRequestId = () => {
            const d = new Date();
            return `REQ${d.getFullYear()}${pad(d.getMonth()+1)}${pad(d.getDate())}${pad(d.getHours())}${pad(d.getMinutes())}${pad(d.getSeconds())}`;
        };
        const setAccountingDate = () => {
            document.getElementById('accounting-date').value =
                new Date().toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
        };
        const toggleNewLineBtn = () => {
            newLineBtn.disabled = !projectSelect.value || !itemsLoaded;
        };
 
        /* ── JSON POST helper ─────────────────────────────────────────── */
        const callPost = async (url, body) => {
            const res = await fetch(url, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body:    JSON.stringify(body),
            });
            const payload = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(payload?.message || payload?.error || 'API request failed.');
            return payload;
        };
 
        /* ── Load items from D365 for the selected company + project ───── */
        const loadItems = async () => {
            const company   = companySelect.value;
            const projectId = projectSelect.value;
            if (!company || !projectId) return;
 
            itemsLoaded = false;
            toggleNewLineBtn();
            itemsLoadingMsg.classList.remove('hidden');
            clearStatus();
 
            try {
                const payload = await callPost(endpoints.itemLookup, { company, project_id: projectId });
 
                // D365 returns an array at payload.data
                const rows = Array.isArray(payload.data) ? payload.data : [];
 
                itemsCache.clear();
                rows.forEach((row) => {
                    const id  = row['Item Id']   || row.ItemId   || row.itemId   || '';
                    const name = row['Item name'] || row.ItemName || row.name     || '';
                    const qty  = parseFloat(row['OnHand Qty'] ?? row.OnHandQty   ?? row.AvailPhysical ?? 0);
                    if (id) itemsCache.set(id, { id, name, onhandQty: qty });
                });
 
                itemsLoaded = true;
                toggleNewLineBtn();
 
                // Refresh item selects already in the table
                linesBody.querySelectorAll('.line-item-select').forEach(sel => {
                    const current = sel.value;
                    populateItemSelect(sel);
                    if (current) sel.value = current;
                });
 
            } catch (err) {
                showStatus('Could not load items: ' + err.message, 'error');
            } finally {
                itemsLoadingMsg.classList.add('hidden');
            }
        };
 
        /* ── Populate a <select> with items from cache ────────────────── */
        const populateItemSelect = (sel) => {
            const prev = sel.value;
            sel.innerHTML = '<option value="">— Select item —</option>';
            itemsCache.forEach(({ id, name }) => {
                const opt = document.createElement('option');
                opt.value       = id;
                opt.textContent = name ? `${id}  –  ${name}` : id;
                if (id === prev) opt.selected = true;
                sel.appendChild(opt);
            });
        };
 
        /* ── Fill description + on-hand + load units when item selected ── */
        const fillItemDetails = async (row, itemId) => {
            const descEl   = row.querySelector('.line-desc');
            const badge    = row.querySelector('.onhand-badge');
            const qtyInput = row.querySelector('.line-qty');
            const unitSel  = row.querySelector('.line-unit-select');
 
            if (!itemId) {
                descEl.textContent    = '—';
                badge.textContent     = '—';
                badge.className       = 'onhand-badge';
                unitSel.innerHTML     = '<option value="">— select item first —</option>';
                unitSel.disabled      = true;
                return;
            }
 
            // On-hand + description from cache
            const item = itemsCache.get(itemId);
            const qty  = item?.onhandQty ?? 0;
            descEl.textContent = item?.name ?? '—';
            badge.textContent  = qty;
            badge.className    = qty > 10 ? 'onhand-badge ok' : qty > 0 ? 'onhand-badge low' : 'onhand-badge zero';
            qtyInput.max       = qty > 0 ? qty : '';
 
            // Load units from D365 for this item (falls back to common units if service unavailable)
            unitSel.innerHTML = '<option value="">Loading units…</option>';
            unitSel.disabled  = true;
 
            try {
                const res   = await callPost(endpoints.units, { company: companySelect.value, item_id: itemId });
                const units = res.units ?? [];
                if (units.length > 0) {
                    unitSel.innerHTML = units.map(u => `<option value="${u.id}">${u.id}</option>`).join('');
                    unitSel.disabled  = false;
                    // Auto-select if only one unit returned (D365 assigns one unit per item)
                    if (units.length === 1) {
                        unitSel.value = units[0].id;
                    }
                } else {
                    unitSel.innerHTML = '<option value="">No unit</option>';
                }
            } catch (err) {
                unitSel.innerHTML = `<option value="">Error: ${err.message}</option>`;
            }
        };
 
        /* ── Create a new line row ────────────────────────────────────── */
        const createLineRow = () => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <select class="line-select line-item-select" style="min-width:180px;"></select>
                </td>
                <td>
                    <span class="line-desc" style="font-size:12px;color:#323130;">—</span>
                </td>
                <td>
                    <span class="onhand-badge">—</span>
                </td>
                <td>
                    <select class="line-select line-unit-select" disabled>
                        <option value="">— select item first —</option>
                    </select>
                </td>
                <td>
                    <input class="line-input line-qty" type="number" min="0.01" step="0.01" value="1" style="width:75px;">
                </td>
                <td>
                    <button type="button" class="btn line-remove-btn" style="font-size:11px;padding:4px 8px;">Remove</button>
                </td>
            `;
 
            const itemSel = row.querySelector('.line-item-select');
            populateItemSelect(itemSel);
 
            itemSel.addEventListener('change', function () {
                fillItemDetails(row, this.value);
            });
 
            return row;
        };
 
        /* ── Reset form ───────────────────────────────────────────────── */
        const resetForm = () => {
            document.getElementById('journal-id').value        = 'Not Yet Posted';
            document.getElementById('request-id').value        = generateRequestId();
            document.getElementById('invent-site-id').value    = '';
            document.getElementById('invent-location-id').value = '';
            document.getElementById('tax-group-id').value      = 'C-DXB';
            document.getElementById('tax-item-group-id').value = '';
            projectSelect.value = '';
            linesBody.innerHTML = '<tr><td class="empty-note" colspan="6">No lines added yet.</td></tr>';
            itemsCache.clear();
            itemsLoaded = false;
            toggleNewLineBtn();
            clearStatus();
        };
 
        /* ── Add journal to grid ──────────────────────────────────────── */
        const addJournalToGrid = ({ requestId, journalId, company, projectId, lineCount }) => {
            if (journalsListBody.querySelector('.empty-note')) journalsListBody.innerHTML = '';
            const row = document.createElement('tr');
            const now = new Date();
            row.innerHTML = `
                <td>${requestId  || '—'}</td>
                <td><strong>${journalId || '—'}</strong></td>
                <td>${company    || '—'}</td>
                <td>${projectId  || '—'}</td>
                <td>${lineCount  || 0}</td>
                <td>Me</td>
                <td>${now.toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' })} ${now.toLocaleTimeString('en-GB', { hour:'2-digit', minute:'2-digit' })}</td>
            `;
            journalsListBody.prepend(row);
        };
 
        /* ── Toolbar / form toggle ────────────────────────────────────── */
        createBtn.addEventListener('click', () => {
            journalToolbar.classList.add('hidden');
            listView.classList.add('hidden');
            formView.classList.remove('hidden');
            resetForm();
        });
 
        backBtn.addEventListener('click', () => {
            formView.classList.add('hidden');
            journalToolbar.classList.remove('hidden');
            listView.classList.remove('hidden');
        });
 
        /* ── Company change → clear cached items ──────────────────────── */
        companySelect.addEventListener('change', () => {
            itemsCache.clear();
            itemsLoaded = false;
            toggleNewLineBtn();
            linesBody.querySelectorAll('.line-item-select').forEach(sel => {
                sel.innerHTML = '<option value="">— Select item —</option>';
            });
            if (projectSelect.value) loadItems();
        });
 
        /* ── Project change → set site/location = project, load items ────── */
        projectSelect.addEventListener('change', () => {
            const projId = projectSelect.value;
            // Project ID is used as InventSiteId and InventLocationId
            document.getElementById('invent-site-id').value     = projId;
            document.getElementById('invent-location-id').value = projId;
 
            itemsCache.clear();
            itemsLoaded = false;
            toggleNewLineBtn();
            linesBody.querySelectorAll('.line-item-select').forEach(sel => {
                sel.innerHTML = '<option value="">— Select item —</option>';
            });
            if (projId) loadItems();
        });
 
        /* ── New line ─────────────────────────────────────────────────── */
        newLineBtn.addEventListener('click', () => {
            if (linesBody.querySelector('.empty-note')) linesBody.innerHTML = '';
            linesBody.appendChild(createLineRow());
        });
 
        /* ── Remove line ──────────────────────────────────────────────── */
        linesBody.addEventListener('click', (e) => {
            if (!e.target.classList.contains('line-remove-btn')) return;
            e.target.closest('tr')?.remove();
            if (!linesBody.querySelector('tr')) {
                linesBody.innerHTML = '<tr><td class="empty-note" colspan="6">No lines added yet.</td></tr>';
            }
        });
 
        /* ── Post journal ─────────────────────────────────────────────── */
        postBtn.addEventListener('click', async () => {
            clearStatus();
            try {
                const requestId        = document.getElementById('request-id').value.trim();
                const description      = document.getElementById('description').value.trim();
                const company          = companySelect.value.trim();
                const projectId        = projectSelect.value.trim();
                const inventSiteId     = document.getElementById('invent-site-id').value.trim();
                const inventLocationId = document.getElementById('invent-location-id').value.trim();
                const taxGroupId       = document.getElementById('tax-group-id').value.trim();
                const taxItemGroupId   = document.getElementById('tax-item-group-id').value.trim();
 
                if (!company || !projectId) throw new Error('Select a company and project before posting.');
 
                const lineRows = Array.from(linesBody.querySelectorAll('tr')).filter(tr => !tr.querySelector('.empty-note'));
                if (!lineRows.length) throw new Error('Add at least one item line before posting.');
 
                const lines = lineRows.map((row, index) => {
                    const itemId = row.querySelector('.line-item-select')?.value?.trim() ?? '';
                    const unit   = row.querySelector('.line-unit-select')?.value?.trim() ?? '';
                    const qty    = Number(row.querySelector('.line-qty')?.value ?? 0);
                    const onhand = itemsCache.get(itemId)?.onhandQty ?? 0;
 
                    if (!itemId)         throw new Error(`Line ${index + 1}: select an item.`);
                    if (!unit)           throw new Error(`Line ${index + 1}: unit not loaded — wait for item selection to complete.`);
                    if (!qty || qty <= 0) throw new Error(`Line ${index + 1}: qty must be greater than 0.`);
                    if (onhand > 0 && qty > onhand) throw new Error(`Line ${index + 1}: qty (${qty}) exceeds on-hand (${onhand}).`);
 
                    return {
                        project_id:     projectId,
                        item_id:        itemId,
                        category:       'Material',
                        currency:       'AED',
                        sales_price:    0,
                        unit:           unit,
                        tax_group:      taxGroupId,
                        tax_item_group: taxItemGroupId,
                        qty:            qty,
                        price_unit:     1,
                        line_num:       index + 1,
                        wms_location:   'Default',
                    };
                });
 
                postBtn.disabled    = true;
                postBtn.textContent = 'Posting…';
 
                const payload = await callPost(endpoints.post, {
                    company,
                    project_id:         projectId,
                    description,
                    invent_site_id:     inventSiteId,
                    invent_location_id: inventLocationId,
                    lines,
                });
 
                const journalId = payload.journal_id || '';
                document.getElementById('journal-id').value = journalId || 'Posted';
                showStatus(journalId
                    ? `✅ Posted successfully. D365 Journal ID: ${journalId}`
                    : '✅ Posted successfully. No journal ID returned yet.');
 
                addJournalToGrid({ requestId, journalId, company, projectId, lineCount: lines.length });
 
            } catch (err) {
                showStatus(err.message, 'error');
            } finally {
                postBtn.disabled    = false;
                postBtn.textContent = 'Post to D365';
            }
        });
 
        /* ── Init ─────────────────────────────────────────────────────── */
        document.getElementById('request-id').value = generateRequestId();
 
    </script>
 
</body>
</html>