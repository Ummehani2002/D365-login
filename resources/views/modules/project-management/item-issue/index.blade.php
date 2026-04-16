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
            min-height: 100vh;
        }
        .sidebar {
            width: 260px;
            background: #fff;
            border-right: 1px solid #edebe9;
            padding: 12px 0;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            overflow-y: auto;
            z-index: 10;
        }
        .logo {
            padding: 10px 16px 18px;
            border-bottom: 1px solid #edebe9;
            margin-bottom: 8px;
            font-weight: 700;
        }
        .label {
            padding: 10px 16px 4px;
            color: #8a8886;
            font-size: 11px;
            text-transform: uppercase;
        }
        .menu-link {
            display: block;
            padding: 10px 16px;
            color: #323130;
            text-decoration: none;
            border-radius: 8px;
            margin: 2px 8px;
            font-size: 14px;
        }
        .menu-link:hover { background: #f3f2f1; }
        .menu-link.active { background: #deecf9; color: #005a9e; }
        .sub {
            margin-left: 16px;
            padding-left: 8px;
            border-left: 2px solid #edebe9;
        }
        .main {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding: 12px 16px;
            overflow: auto;
        }
        .page-shell {
            border: 1px solid #edebe9;
            background: #fff;
            border-radius: 2px;
            overflow: hidden;
        }
        .command-bar {
            height: 44px;
            border-bottom: 1px solid #edebe9;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 12px;
        }
        .crumb {
            font-size: 12px;
            color: #605e5c;
        }
        .toolbar { margin-bottom: 12px; }
        .toolbar-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }
        .title {
            margin: 0 0 4px;
            font-size: 24px;
            font-weight: 600;
        }
        .subtitle {
            margin: 0;
            color: #605e5c;
            font-size: 12px;
        }
        .search {
            width: 240px;
            border: 1px solid #8a8886;
            border-radius: 2px;
            padding: 7px 10px;
            font-size: 13px;
            margin-top: 10px;
        }
        .btn {
            border: 1px solid #8a8886;
            background: #fff;
            color: #323130;
            border-radius: 2px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-primary {
            border-color: #106ebe;
            background: #106ebe;
            color: #fff;
        }
        .btn-light {
            background: #fff;
            color: #106ebe;
            border-color: #c7e0f4;
        }
        .card {
            background: #fff;
            border: 1px solid #edebe9;
            border-radius: 2px;
            overflow: hidden;
        }
        .card-head {
            padding: 12px 14px;
            border-bottom: 1px solid #edebe9;
            font-size: 32px;
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 920px;
        }
        th, td {
            border-bottom: 1px solid #edebe9;
            padding: 10px 12px;
            text-align: left;
            font-size: 13px;
            white-space: nowrap;
        }
        th { color: #605e5c; font-weight: 600; background: #faf9f8; }
        .empty-note {
            text-align: center;
            color: #8a8886;
            padding: 22px 10px;
            font-size: 13px;
        }
        .journal-form {
            background: #fff;
            border: 1px solid #edebe9;
            border-radius: 12px;
            padding: 16px;
        }
        .journal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }
        .journal-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .journal-title {
            margin: 0;
            font-size: 36px;
            font-weight: 600;
        }
        .journal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-bottom: 0;
        }
        .fields {
            display: grid;
            grid-template-columns: repeat(3, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }
        .field label {
            display: block;
            font-size: 13px;
            margin-bottom: 6px;
            color: #605e5c;
        }
        .field input, .field select {
            width: 100%;
            border: 1px solid #8a8886;
            border-radius: 2px;
            padding: 7px 8px;
            font-size: 13px;
            background: #fff;
        }
        .field input[readonly] {
            background: #f3f2f1;
            color: #605e5c;
            cursor: not-allowed;
        }
        .status-box {
            margin-bottom: 10px;
            padding: 8px 10px;
            border-radius: 2px;
            font-size: 13px;
            display: none;
        }
        .status-box.success {
            display: block;
            background: #e8f6ee;
            color: #1f7a48;
        }
        .status-box.error {
            display: block;
            background: #fde7e9;
            color: #a4262c;
        }
        .lines-toolbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        }
        .line-area {
            border: 1px solid #edebe9;
            border-radius: 10px;
            overflow: hidden;
        }
        .btn:disabled {
            border-color: #edebe9;
            background: #f3f2f1;
            color: #a19f9d;
            cursor: not-allowed;
        }
        .hidden { display: none; }
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
    </aside>

    <main class="main">
        <div class="page-shell">
        <div class="command-bar">
            <div class="crumb">Modules / Project Management / Item Issue</div>
        </div>
        <div style="padding:12px;">
        <div id="journal-toolbar" class="toolbar">
            <div class="toolbar-row">
                <div>
                    <h1 class="title">Item Issue</h1>
                    
                </div>
                <button id="create-journal-btn" class="btn btn-primary" type="button">Create New Journal</button>
            </div>
            <input class="search" type="text" placeholder="Search journals..." disabled>
        </div>

        <div id="journal-list-view" class="card">
            <div class="card-head">Journals</div>
            <div style="overflow:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Ref.Number</th>
                            <th>Journal Id</th>
                            <th>Project</th>
                            <th>Created By</th>
                            <th>Accounting Date</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="empty-note" colspan="6">No journal records yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="journal-form-view" class="journal-form hidden">
            <div class="journal-header">
                <div class="journal-left">
                    <button id="back-to-list-btn" class="btn" type="button">Back</button>
                    <h2 class="journal-title">Journal</h2>
                </div>
                <div class="journal-actions">
                    <button id="post-journal-btn" class="btn btn-primary" type="button">Post</button>
                </div>
            </div>
            <div id="status-box" class="status-box"></div>

            <div class="fields">
                <div class="field">
                    <label>Journal ID</label>
                    <input id="journal-id" type="text" value="Not Yet Posted" readonly>
                </div>
                <div class="field">
                    <label>Project</label>
                    <select id="project-id">
                        <option value="">Select a project...</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->d365_id }}">{{ $project->d365_id }} - {{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Accounting Date</label>
                    <input id="accounting-date" type="text" readonly>
                </div>
            </div>
            <input id="request-id" type="hidden" value="">
            <input id="post-data-area" type="hidden" value="PS">
            <input id="project-data-area" type="hidden" value="GC">
            <input id="item-data-area" type="hidden" value="PS">
            <input id="description" type="hidden" value="Issue of items for project">
            <input id="invent-site-id" type="hidden" value="PIE20241004">
            <input id="invent-location-id" type="hidden" value="PIE20241004">

            <div class="lines-toolbar">
                <button id="new-line-btn" class="btn btn-light" type="button" disabled>New Line</button>
            </div>
            <datalist id="item-options"></datalist>

            <div class="line-area" style="overflow:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="journal-lines-body">
                        <tr>
                            <td class="empty-note" colspan="4">No journal items found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
        </div>
    </main>
    <script>
        const listView = document.getElementById('journal-list-view');
        const formView = document.getElementById('journal-form-view');
        const createBtn = document.getElementById('create-journal-btn');
        const backBtn = document.getElementById('back-to-list-btn');
        const journalToolbar = document.getElementById('journal-toolbar');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const newLineBtn = document.getElementById('new-line-btn');
        const postBtn = document.getElementById('post-journal-btn');
        const linesBody = document.getElementById('journal-lines-body');
        const journalsListBody = listView.querySelector('tbody');
        const statusBox = document.getElementById('status-box');
        const itemOptions = document.getElementById('item-options');
        const projectInput = document.getElementById('project-id');

        const endpoints = {
            projectLookup: "{{ route('modules.project-management.item-issue.api.projects.lookup') }}",
            itemLookup: "{{ route('modules.project-management.item-issue.api.items.lookup') }}",
            post: "{{ route('modules.project-management.item-issue.api.post') }}",
        };

        const showStatus = (message, type = 'success') => {
            statusBox.textContent = message;
            statusBox.className = `status-box ${type}`;
        };

        const clearStatus = () => {
            statusBox.textContent = '';
            statusBox.className = 'status-box';
        };

        const generateRequestId = () => {
            const now = new Date();
            const stamp = `${now.getFullYear()}${String(now.getMonth() + 1).padStart(2, '0')}${String(now.getDate()).padStart(2, '0')}${String(now.getHours()).padStart(2, '0')}${String(now.getMinutes()).padStart(2, '0')}${String(now.getSeconds()).padStart(2, '0')}`;
            return `REQ${stamp}`;
        };

        const normalizeResponseRows = (payload) => {
            if (Array.isArray(payload?.data?.value)) return payload.data.value;
            if (Array.isArray(payload?.data)) return payload.data;
            if (Array.isArray(payload?.data?.data)) return payload.data.data;
            if (Array.isArray(payload?.data?.result)) return payload.data.result;
            if (Array.isArray(payload?.data?._response)) return payload.data._response;
            return [];
        };

        const extractIdFromProjectInput = () => {
            const value = document.getElementById('project-id').value.trim();
            if (!value) return '';
            return value;
        };

        const createLineRow = (defaults = {}) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input list="item-options" class="line-item-id" type="text" placeholder="Select item..." value="${defaults.itemId ?? ''}"></td>
                <td><input class="line-qty" type="number" min="0.01" step="0.01" value="${defaults.qty ?? '1'}"></td>
                <td><input class="line-unit" type="text" placeholder="KGS" value="${defaults.unit ?? ''}"></td>
                <td><button type="button" class="btn line-remove-btn">Remove</button></td>
            `;

            return row;
        };

        const setAccountingDate = () => {
            const now = new Date();
            const formatted = now.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
            document.getElementById('accounting-date').value = formatted;
        };

        const toggleNewLineButton = () => {
            newLineBtn.disabled = !extractIdFromProjectInput();
        };

        const resetForm = () => {
            document.getElementById('journal-id').value = 'Not Yet Posted';
            document.getElementById('project-id').value = '';
            document.getElementById('request-id').value = generateRequestId();
            document.getElementById('description').value = 'Issue of items for project';
            linesBody.innerHTML = '<tr><td class="empty-note" colspan="4">No journal items found</td></tr>';
            setAccountingDate();
            toggleNewLineButton();
            clearStatus();
        };

        const callPostJson = async (url, body) => {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(body),
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                const message = payload?.message || payload?.error || 'API request failed.';
                throw new Error(message);
            }

            return payload;
        };

        const addJournalToGrid = ({ requestId, journalId, projectId }) => {
            const emptyRow = journalsListBody.querySelector('.empty-note');
            if (emptyRow) {
                journalsListBody.innerHTML = '';
            }

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${requestId || '-'}</td>
                <td>${journalId || '-'}</td>
                <td>${projectId || '-'}</td>
                <td>Current User</td>
                <td>${new Date().toLocaleDateString()}</td>
                <td>${new Date().toLocaleString()}</td>
            `;
            journalsListBody.prepend(row);
        };

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

        newLineBtn.addEventListener('click', () => {
            if (linesBody.querySelector('.empty-note')) {
                linesBody.innerHTML = '';
            }
            const row = createLineRow();
            linesBody.appendChild(row);
        });

        linesBody.addEventListener('click', (event) => {
            if (!event.target.classList.contains('line-remove-btn')) return;
            event.target.closest('tr')?.remove();

            if (!linesBody.querySelector('tr')) {
                linesBody.innerHTML = '<tr><td class="empty-note" colspan="4">No journal items found</td></tr>';
            }
        });

        const loadItemOptions = async () => {
            clearStatus();
            try {
                const dataAreaId = document.getElementById('item-data-area').value.trim();
                const payload = await callPostJson(endpoints.itemLookup, {
                    DataAreaId: dataAreaId,
                    ItemId: '',
                });

                const rows = normalizeResponseRows(payload);
                itemOptions.innerHTML = '';
                rows.forEach((row) => {
                    const id = row.ItemId || row.itemId || row.ItemNumber || '';
                    if (!id) return;
                    const name = row.Name || row.ItemName || row.name || '';
                    const option = document.createElement('option');
                    option.value = name ? `${id} - ${name}` : id;
                    itemOptions.appendChild(option);
                });
            } catch (error) {
                showStatus(`Item list could not be loaded: ${error.message}`, 'error');
            }
        };

        projectInput.addEventListener('change', toggleNewLineButton);
        projectInput.addEventListener('input', toggleNewLineButton);
        setAccountingDate();
        loadItemOptions();

        postBtn.addEventListener('click', async () => {
            clearStatus();
            try {
                const requestId = document.getElementById('request-id').value.trim();
                const description = document.getElementById('description').value.trim();
                const dataAreaId = document.getElementById('post-data-area').value.trim();
                const inventSiteId = document.getElementById('invent-site-id').value.trim();
                const inventLocationId = document.getElementById('invent-location-id').value.trim();
                const projectId = extractIdFromProjectInput();

                if (!requestId || !description || !dataAreaId || !inventSiteId || !inventLocationId || !projectId) {
                    throw new Error('Please fill request/header fields and select a valid project before posting.');
                }

                const lineRows = Array.from(linesBody.querySelectorAll('tr')).filter((tr) => !tr.querySelector('.empty-note'));
                if (!lineRows.length) {
                    throw new Error('Add at least one item line before posting.');
                }

                const lines = lineRows.map((row, index) => {
                    const getVal = (selector) => row.querySelector(selector)?.value?.trim() ?? '';
                    const itemIdRaw = getVal('.line-item-id');
                    const itemId = itemIdRaw.split(' - ')[0].trim();
                    const qty = Number(getVal('.line-qty'));
                    const price = 0;
                    const priceUnit = 1;

                    if (!itemId || !qty || !priceUnit) {
                        throw new Error(`Line ${index + 1}: item, qty and price unit are required.`);
                    }

                    return {
                        RequestId: requestId,
                        InventSiteId: inventSiteId,
                        InventLocationId: inventLocationId,
                        ProjId: projectId,
                        ProjCategoryId: 'Material',
                        ItemId: itemId,
                        ProjSalesCurrencyId: 'AED',
                        ProjSalesPrice: Number.isFinite(price) ? price : 0,
                        ProjUnitID: getVal('.line-unit'),
                        ProjTaxGroupId: 'C-DXB',
                        ProjTaxItemGroupId: '',
                        Qty: qty,
                        PriceUnit: priceUnit,
                        LineNum: index + 1,
                        wMSLocationId: 'Default',
                        InventSizeId: '',
                        InventSerialId: '',
                        InventStyleId: '',
                    };
                });

                const payload = await callPostJson(endpoints.post, {
                    DataAreaId: dataAreaId,
                    ItemIssueHeader: {
                        RequestId: requestId,
                        Description: description,
                        InventSiteId: inventSiteId,
                        InventLocationId: inventLocationId,
                    },
                    ItemIssueLines: lines,
                });

                const journalId = payload.journal_id || '';
                document.getElementById('journal-id').value = journalId;
                showStatus(journalId
                    ? `Posted successfully. D365 journal id: ${journalId}`
                    : 'Posted successfully. D365 response did not include a journal id.');

                addJournalToGrid({
                    requestId,
                    journalId,
                    projectId,
                });
            } catch (error) {
                showStatus(error.message, 'error');
            }
        });
    </script>
</body>
</html>
