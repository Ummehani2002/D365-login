<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Purchase Requisition</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f3f2f1; color: #323130; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #fff; border-right: 1px solid #edebe9; padding: 12px 0; }
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
        th, td { border-bottom: 1px solid #edebe9; padding: 8px 10px; text-align: left; font-size: 13px; vertical-align: top; }
        th { color: #605e5c; font-weight: 600; background: #faf9f8; white-space: nowrap; }
        .table-wrap { overflow-x: auto; border: 1px solid #edebe9; border-radius: 2px; margin-top: 10px; }
        .table-wrap table { min-width: 100%; }
        .list-table { min-width: 760px; }
        .line-table { min-width: 1500px; }
        .attachment-table { min-width: 900px; }
        .empty-note { text-align: center; color: #8a8886; padding: 22px 10px; font-size: 13px; }
        .pr-form { background: #fff; border: 1px solid #edebe9; border-radius: 2px; padding: 14px; }
        .pr-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 14px; }
        .pr-left { display: flex; align-items: center; gap: 10px; }
        .pr-title { margin: 0; font-size: 28px; font-weight: 600; }
        .fields { margin-bottom: 12px; }
        .field label { display: block; font-size: 12px; margin-bottom: 4px; color: #605e5c; font-weight: 500; }
        .field input, .field select, .field textarea,
        .header-form-table input, .header-form-table select, .header-form-table textarea {
            width: 100%;
            border: 1px solid #8a8886;
            border-radius: 2px;
            padding: 6px 8px;
            font-size: 13px;
            background: #fff;
        }
        .field textarea, .header-form-table textarea { min-height: 66px; resize: vertical; }
        .header-form-table { width: 100%; border: 1px solid #edebe9; border-radius: 2px; overflow: hidden; }
        .header-form-table td { border-bottom: 1px solid #edebe9; padding: 8px; }
        .header-form-table tr:last-child td { border-bottom: none; }
        .header-label { font-size: 12px; margin-bottom: 4px; color: #605e5c; font-weight: 500; display: block; }
        .section { border: 1px solid #edebe9; border-radius: 2px; margin-bottom: 12px; background: #fff; }
        .section-head { padding: 10px 12px; border-bottom: 1px solid #edebe9; background: #f3f2f1; font-size: 15px; font-weight: 600; }
        .section-body { padding: 10px; background: #faf9f8; }
        .line-input { width: 100%; border: 1px solid #8a8886; border-radius: 2px; padding: 5px 7px; font-size: 12px; min-width: 90px; }
        .line-input[type="date"] { min-width: 130px; }
        .line-input[type="file"] { min-width: 220px; }
        .line-table th, .line-table td,
        .attachment-table th, .attachment-table td {
            padding: 6px 8px;
            font-size: 12px;
        }
        .line-table th, .attachment-table th {
            background: #f3f2f1;
            color: #605e5c;
            font-weight: 600;
        }
        .line-table .line-input,
        .attachment-table .line-input {
            height: 30px;
            border: 1px solid #a19f9d;
            border-radius: 2px;
            background: #fff;
        }
        .line-table textarea.line-input,
        .attachment-table textarea.line-input {
            height: 56px;
        }
        .btn-light {
            border-color: #c7e0f4;
            color: #005a9e;
            background: #fff;
            padding: 4px 10px;
        }
        .status-box { margin-bottom: 10px; padding: 8px 10px; border-radius: 2px; font-size: 13px; display: none; }
        .status-box.success { display: block; background: #e8f6ee; color: #1f7a48; }
        .status-box.error { display: block; background: #fde7e9; color: #a4262c; }
        .hidden { display: none; }
        .att-status { font-size: 11px; color: #605e5c; display: inline-block; padding-top: 6px; }
        /* Legal entity codes always shown uppercase (payload may still normalize server-side). */
        #company, #company option { text-transform: uppercase; font-variant-numeric: tabular-nums; }
    </style>
    @include('settings.rbac.partials.styles')
</head>
<body>
    @include('partials.global-company-selector')
    @php
        $companyCodeNav = strtoupper((string) request()->query('company', ''));
        $companyQuery = $companyCodeNav !== '' ? ['company' => $companyCodeNav] : [];
    @endphp
    @include('settings.rbac.partials.sidebar')

    <main class="main">
        <div class="page-shell">
            <div class="command-bar">
                <div class="crumb">Modules / Procurement / Purchase Requisition</div>
            </div>
            <div style="padding:12px;">
                <div id="list-toolbar" class="toolbar">
                    <div class="toolbar-row">
                        <div><h1 class="title">Purchase Requisitions</h1></div>
                        <button id="create-pr-btn" class="btn btn-primary" type="button">Create New</button>
                    </div>
                    <input class="search" type="text" placeholder="Search purchase requests..." disabled>
                </div>

                <div id="pr-list-view" class="card">
                    <div class="card-head">Purchase Requisition List</div>
                    <div class="table-wrap">
                        <table class="list-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>PR No</th>
                                    <th>Contact</th>
                                    <th>Department</th>
                                    <th>PR Date</th>
                                    <th>Lines</th>
                                </tr>
                            </thead>
                            <tbody id="pr-list-body">
                                <tr><td class="empty-note" colspan="6">No purchase requisitions yet.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="pr-form-view" class="pr-form hidden">
                    <div class="pr-header">
                        <div class="pr-left">
                            <button id="back-to-list-btn" class="btn" type="button">← Back</button>
                            <h2 class="pr-title">New Purchase Requisition</h2>
                        </div>
                        <button id="post-btn" class="btn btn-primary" type="button">Post to D365</button>
                    </div>

                    <div id="status-box" class="status-box"></div>

                    <div class="fields">
                        <table class="header-form-table">
                            <tr>
                                <td>
                                    <label class="header-label">Company (DataAreaId)</label>
                                    <select id="company">
                                        @foreach($companies as $company)
                                            <option value="{{ $company->d365_id }}">{{ $company->d365_id }} - {{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <label class="header-label">Request ID</label>
                                    <input id="request-id" type="text">
                                </td>
                                <td>
                                    <label class="header-label">PR No</label>
                                    <input id="pr-no" type="text">
                                </td>
                                <td>
                                    <label class="header-label">PR Date</label>
                                    <input id="pr-date" type="date">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="header-label">Warehouse</label>
                                    <input id="warehouse" type="text" placeholder="e.g. PSE20251008">
                                </td>
                                <td>
                                    <label class="header-label">Contact Name</label>
                                    <input id="contact-name" type="text">
                                </td>
                                <td>
                                    <label class="header-label">Department</label>
                                    <input id="department" type="text" value="Procurement">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <label class="header-label">Remarks</label>
                                    <textarea id="remarks" placeholder="Urgent purchase request"></textarea>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="section">
                        <div class="section-head">Purchase requisition lines</div>
                        <div class="section-body">
                            <button id="add-line-btn" class="btn btn-light" type="button">+ Add Line</button>
                            <div class="table-wrap">
                            <table class="line-table">
                                <thead>
                                    <tr>
                                        <th>Line No</th>
                                        <th>Item Category</th>
                                        <th>Item ID</th>
                                        <th>Description</th>
                                        <th>Required Date</th>
                                        <th>Unit</th>
                                        <th>Qty</th>
                                        <th>Currency</th>
                                        <th>Rate</th>
                                        <th>CandyBudget</th>
                                        <th>BudgetResourceId</th>
                                        <th>Warranty</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="lines-body"></tbody>
                            </table>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-head">Attachments</div>
                        <div class="section-body">
                            <button id="add-attachment-btn" class="btn btn-light" type="button">+ Add Attachment</button>
                            <div class="table-wrap">
                            <table class="attachment-table">
                                <thead>
                                    <tr>
                                        <th>Purch ID</th>
                                        <th>Upload File</th>
                                        <th>File Name</th>
                                        <th>File Type</th>
                                        <th>Encoded Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="attachments-body"></tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const DEFAULT_POOL_ID = 'P_LPO';
        const postEndpoint = "{{ route('purchase-requisitions.api.post') }}";
        const catalogEndpoint = "{{ route('modules.procurement.purch-req.api.catalog') }}";

        const listToolbar = document.getElementById('list-toolbar');
        const listView = document.getElementById('pr-list-view');
        const formView = document.getElementById('pr-form-view');
        const createBtn = document.getElementById('create-pr-btn');
        const backBtn = document.getElementById('back-to-list-btn');
        const linesBody = document.getElementById('lines-body');
        const attachmentsBody = document.getElementById('attachments-body');
        const statusBox = document.getElementById('status-box');
        const postBtn = document.getElementById('post-btn');
        const prListBody = document.getElementById('pr-list-body');

        let itemCatalog = { categories: [], items: [] };
        let lineUid = 0;

        const showStatus = (message, type = 'success') => {
            statusBox.textContent = message;
            statusBox.className = `status-box ${type}`;
        };

        const escapeHtml = (value) => String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        const normalizeLineNos = () => {
            Array.from(linesBody.querySelectorAll('tr')).forEach((tr, idx) => {
                const lineNoInput = tr.querySelector('.line-no');
                if (lineNoInput) lineNoInput.value = String(idx + 1);
            });
        };

        const renderCategoryOptions = () => {
            const categories = Array.isArray(itemCatalog.categories) ? itemCatalog.categories : [];
            return categories.map((c) => {
                const id = String(c.id ?? '').trim();
                const name = String(c.name ?? id).trim();
                const label = name && name !== id ? `${id} - ${name}` : id;
                return `<option value="${escapeHtml(id)}">${escapeHtml(label)}</option>`;
            }).join('');
        };

        const getItemsByCategory = (categoryId) => {
            const key = String(categoryId ?? '').trim().toLowerCase();
            if (!key) {
                return Array.isArray(itemCatalog.items) ? itemCatalog.items : [];
            }
            return (Array.isArray(itemCatalog.items) ? itemCatalog.items : []).filter((item) => {
                return String(item.category ?? '').trim().toLowerCase() === key;
            });
        };

        const getItemById = (itemId) => {
            const key = String(itemId ?? '').trim().toLowerCase();
            if (!key) return null;
            return (Array.isArray(itemCatalog.items) ? itemCatalog.items : []).find((item) => {
                return String(item.id ?? '').trim().toLowerCase() === key;
            }) ?? null;
        };

        const renderItemOptionsForRow = (tr) => {
            const category = tr.querySelector('.item-category')?.value?.trim() ?? '';
            const items = getItemsByCategory(category);
            return items.map((item) => {
                const id = String(item.id ?? '').trim();
                const name = String(item.name ?? '').trim();
                const cat = String(item.category ?? '').trim();
                const label = name ? `${id} - ${name}` : id;
                const labelWithCategory = cat ? `${label} (${cat})` : label;
                return `<option value="${escapeHtml(id)}">${escapeHtml(labelWithCategory)}</option>`;
            }).join('');
        };

        const refreshRowDatalists = (tr) => {
            const catList = tr.querySelector('.item-category-list');
            const itemList = tr.querySelector('.item-id-list');
            if (catList) {
                catList.innerHTML = renderCategoryOptions();
            }
            if (itemList) {
                const prevItem = tr.querySelector('.item-id')?.value ?? '';
                itemList.innerHTML = renderItemOptionsForRow(tr);
                const itemInput = tr.querySelector('.item-id');
                if (itemInput) {
                    itemInput.value = prevItem;
                }
            }
        };

        const updateCategoryFromItem = (tr) => {
            const itemId = tr.querySelector('.item-id')?.value?.trim() ?? '';
            const categoryInput = tr.querySelector('.item-category');
            if (!categoryInput) return;
            const selected = getItemById(itemId);
            if (selected?.category) {
                categoryInput.value = selected.category;
                refreshRowDatalists(tr);
            }
        };

        const addLineRow = () => {
            lineUid += 1;
            const uid = lineUid;
            const tr = document.createElement('tr');
            tr.dataset.lineUid = String(uid);
            tr.innerHTML = `
                <td><input class="line-input line-no" type="number" min="1" value="${linesBody.querySelectorAll('tr').length + 1}"></td>
                <td>
                    <input class="line-input item-category" type="text" list="pr-cat-list-${uid}" placeholder="Type category id / name">
                    <datalist id="pr-cat-list-${uid}" class="item-category-list"></datalist>
                </td>
                <td>
                    <input class="line-input item-id" type="text" list="pr-item-list-${uid}" placeholder="Type item id to search">
                    <datalist id="pr-item-list-${uid}" class="item-id-list"></datalist>
                </td>
                <td><input class="line-input item-description" type="text" placeholder="Description"></td>
                <td><input class="line-input required-date" type="date"></td>
                <td><input class="line-input unit" type="text" value="EA"></td>
                <td><input class="line-input qty" type="number" min="0.01" step="0.01" value="1"></td>
                <td><input class="line-input currency" type="text" value="AED"></td>
                <td><input class="line-input rate" type="number" min="0" step="0.01" value="0"></td>
                <td><input class="line-input candy-budget" type="number" min="0" step="0.01" value="0"></td>
                <td><input class="line-input budget-resource-id" type="text"></td>
                <td><input class="line-input warranty" type="text" value="N/A"></td>
                <td><button class="btn remove-line-btn" type="button">Remove</button></td>
            `;
            linesBody.appendChild(tr);
            refreshRowDatalists(tr);
        };

        const addAttachmentRow = () => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input class="line-input att-purch-id" type="text"></td>
                <td><input class="line-input att-file-upload" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"></td>
                <td><input class="line-input att-file-name" type="text" placeholder="PR1.pdf"></td>
                <td><input class="line-input att-file-type" type="text"></td>
                <td><span class="att-status">No file selected</span><textarea class="line-input att-file-content" style="display:none;"></textarea></td>
                <td><button class="btn remove-attachment-btn" type="button">Remove</button></td>
            `;
            attachmentsBody.appendChild(tr);
        };

        async function loadCatalogForCompany() {
            const company = (document.getElementById('company')?.value?.trim() ?? '').toUpperCase();
            itemCatalog = { categories: [], items: [] };

            if (!company) {
                Array.from(linesBody.querySelectorAll('tr')).forEach((tr) => refreshRowDatalists(tr));
                return;
            }

            try {
                const response = await fetch(catalogEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ company }),
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok || !payload.status) {
                    throw new Error(payload.message || payload.error || 'Catalog load failed.');
                }

                itemCatalog = {
                    categories: Array.isArray(payload.categories) ? payload.categories : [],
                    items: Array.isArray(payload.items) ? payload.items : [],
                };

                Array.from(linesBody.querySelectorAll('tr')).forEach((tr) => refreshRowDatalists(tr));
            } catch (error) {
                // Keep the form usable even if catalog fails; user can still type manually.
                itemCatalog = { categories: [], items: [] };
                Array.from(linesBody.querySelectorAll('tr')).forEach((tr) => refreshRowDatalists(tr));
                showStatus(error.message || 'Unable to load item catalog.', 'error');
            }
        }

        const readFileAsBase64 = (file) => new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => {
                const result = typeof reader.result === 'string' ? reader.result : '';
                const base64 = result.includes(',') ? result.split(',')[1] : result;
                resolve(base64);
            };
            reader.onerror = () => reject(new Error('Failed to read attachment file.'));
            reader.readAsDataURL(file);
        });

        const detectFileType = (fileName) => {
            const extension = fileName.split('.').pop()?.toLowerCase() ?? '';
            const supported = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
            if (!supported.includes(extension)) {
                throw new Error('Only PDF, Word (.doc/.docx), and Excel (.xls/.xlsx) files are allowed.');
            }
            return extension;
        };

        const callPost = async (payload) => {
            const response = await fetch(postEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
            });

            const json = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(json?.error || json?.message || `Request failed (${response.status}).`);
            }
            return json;
        };

        const buildPayload = () => {
            const company = document.getElementById('company').value.trim().toUpperCase();
            const requestId = document.getElementById('request-id').value.trim();
            const prNo = document.getElementById('pr-no').value.trim();
            const prDate = document.getElementById('pr-date').value;
            const warehouse = document.getElementById('warehouse').value.trim();
            const contactName = document.getElementById('contact-name').value.trim();
            const department = document.getElementById('department').value.trim();
            const remarks = document.getElementById('remarks').value.trim();

            if (!company || !requestId || !prNo || !prDate || !warehouse || !contactName || !department) {
                throw new Error('Please fill all required header fields.');
            }

            const lines = Array.from(linesBody.querySelectorAll('tr')).map((tr, index) => {
                const lineNo = Number(tr.querySelector('.line-no')?.value ?? index + 1);
                const itemCategory = tr.querySelector('.item-category')?.value?.trim() ?? '';
                const itemId = tr.querySelector('.item-id')?.value?.trim() ?? '';
                const itemDescription = tr.querySelector('.item-description')?.value?.trim() ?? '';
                const requiredDate = tr.querySelector('.required-date')?.value ?? '';
                const unit = tr.querySelector('.unit')?.value?.trim() ?? '';
                const qty = Number(tr.querySelector('.qty')?.value ?? 0);
                const currency = tr.querySelector('.currency')?.value?.trim() ?? '';
                const rate = Number(tr.querySelector('.rate')?.value ?? 0);
                const candyBudget = Number(tr.querySelector('.candy-budget')?.value ?? 0);
                const budgetResourceId = tr.querySelector('.budget-resource-id')?.value?.trim() ?? '';
                const warranty = tr.querySelector('.warranty')?.value?.trim() ?? 'N/A';

                if (!itemCategory || !itemId || !itemDescription || !requiredDate || !unit || !currency || qty <= 0) {
                    throw new Error(`Line ${index + 1}: complete all required line fields.`);
                }

                return {
                    line_no: lineNo,
                    item_category: itemCategory,
                    item_id: itemId,
                    item_description: itemDescription,
                    required_date: requiredDate,
                    unit,
                    qty,
                    currency,
                    rate,
                    candy_budget: candyBudget,
                    budget_resource_id: budgetResourceId,
                    warranty,
                };
            });

            if (lines.length === 0) {
                throw new Error('Add at least one line.');
            }

            const attachments = Array.from(attachmentsBody.querySelectorAll('tr'))
                .map((tr) => {
                    const fileName = tr.querySelector('.att-file-name')?.value?.trim() ?? '';
                    const fileType = tr.querySelector('.att-file-type')?.value?.trim() ?? '';
                    const fileContentBase64 = tr.querySelector('.att-file-content')?.value?.trim() ?? '';

                    if (!fileName && !fileType && !fileContentBase64) {
                        return null;
                    }

                    if (!fileName || !fileType || !fileContentBase64) {
                        throw new Error('Attachment row is incomplete. Upload a supported file (PDF/Word/Excel).');
                    }

                    return {
                        purch_id: tr.querySelector('.att-purch-id')?.value?.trim() ?? '',
                        file_name: fileName,
                        file_type: fileType,
                        file_content_base64: fileContentBase64,
                    };
                })
                .filter(Boolean);

            return {
                company,
                request_id: requestId,
                pr_no: prNo,
                pr_date: prDate,
                warehouse,
                pool_id: DEFAULT_POOL_ID,
                contact_name: contactName,
                remarks,
                department,
                lines,
                attachments,
            };
        };

        const resetForm = () => {
            statusBox.className = 'status-box';
            statusBox.textContent = '';
            linesBody.innerHTML = '';
            attachmentsBody.innerHTML = '';

            const stamp = Date.now().toString().slice(-4);
            document.getElementById('request-id').value = `REQ-${stamp}`;
            document.getElementById('pr-no').value = `REQ-${stamp}`;
            document.getElementById('pr-date').value = new Date().toISOString().slice(0, 10);
            document.getElementById('warehouse').value = '';
            document.getElementById('contact-name').value = '';
            document.getElementById('department').value = 'Procurement';
            document.getElementById('remarks').value = '';

            addLineRow();
            const firstRequiredDate = linesBody.querySelector('.required-date');
            if (firstRequiredDate) {
                firstRequiredDate.value = document.getElementById('pr-date').value;
            }

            void loadCatalogForCompany();
        };

        const addPrToList = ({ prNo, contactName, department, prDate, lineCount }) => {
            if (prListBody.querySelector('.empty-note')) {
                prListBody.innerHTML = '';
            }

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${prListBody.querySelectorAll('tr').length + 1}</td>
                <td>${prNo || '—'}</td>
                <td>${contactName || '—'}</td>
                <td>${department || '—'}</td>
                <td>${prDate || '—'}</td>
                <td>${lineCount || 0}</td>
            `;
            prListBody.prepend(row);
        };

        createBtn.addEventListener('click', () => {
            listToolbar.classList.add('hidden');
            listView.classList.add('hidden');
            formView.classList.remove('hidden');
            resetForm();
        });

        backBtn.addEventListener('click', () => {
            formView.classList.add('hidden');
            listToolbar.classList.remove('hidden');
            listView.classList.remove('hidden');
        });

        document.getElementById('add-line-btn').addEventListener('click', addLineRow);
        document.getElementById('add-attachment-btn').addEventListener('click', addAttachmentRow);

        linesBody.addEventListener('click', (event) => {
            if (!event.target.classList.contains('remove-line-btn')) return;
            event.target.closest('tr')?.remove();
            normalizeLineNos();
        });

        attachmentsBody.addEventListener('click', (event) => {
            if (!event.target.classList.contains('remove-attachment-btn')) return;
            event.target.closest('tr')?.remove();
        });

        attachmentsBody.addEventListener('change', async (event) => {
            if (!event.target.classList.contains('att-file-upload')) return;

            const tr = event.target.closest('tr');
            if (!tr) return;

            const file = event.target.files?.[0];
            const fileNameInput = tr.querySelector('.att-file-name');
            const fileTypeInput = tr.querySelector('.att-file-type');
            const fileContentInput = tr.querySelector('.att-file-content');
            const status = tr.querySelector('.att-status');

            if (!file) {
                fileContentInput.value = '';
                status.textContent = 'No file selected';
                return;
            }

            try {
                const fileType = detectFileType(file.name);
                const base64 = await readFileAsBase64(file);

                fileNameInput.value = file.name;
                fileTypeInput.value = fileType;
                fileContentInput.value = base64;
                status.textContent = `Encoded (${Math.round(file.size / 1024)} KB)`;
            } catch (error) {
                event.target.value = '';
                fileNameInput.value = '';
                fileTypeInput.value = '';
                fileContentInput.value = '';
                status.textContent = 'Invalid file';
                showStatus(error.message, 'error');
            }
        });

        document.getElementById('company')?.addEventListener('change', () => {
            void loadCatalogForCompany();
        });

        linesBody.addEventListener('input', (event) => {
            const tr = event.target.closest('tr');
            if (!tr) return;

            if (event.target.classList.contains('item-category')) {
                const itemId = tr.querySelector('.item-id')?.value?.trim() ?? '';
                const selectedItem = getItemById(itemId);
                const selectedCategory = tr.querySelector('.item-category')?.value?.trim().toLowerCase() ?? '';
                const itemCategory = String(selectedItem?.category ?? '').trim().toLowerCase();
                if (itemId && selectedItem && selectedCategory && itemCategory && selectedCategory !== itemCategory) {
                    tr.querySelector('.item-id').value = '';
                }
                refreshRowDatalists(tr);
            }

            if (event.target.classList.contains('item-id')) {
                updateCategoryFromItem(tr);
            }
        });

        linesBody.addEventListener('change', (event) => {
            const tr = event.target.closest('tr');
            if (!tr) return;

            if (event.target.classList.contains('item-category')) {
                const itemId = tr.querySelector('.item-id')?.value?.trim() ?? '';
                const selectedItem = getItemById(itemId);
                const selectedCategory = tr.querySelector('.item-category')?.value?.trim().toLowerCase() ?? '';
                const itemCategory = String(selectedItem?.category ?? '').trim().toLowerCase();
                if (itemId && selectedItem && selectedCategory && itemCategory && selectedCategory !== itemCategory) {
                    tr.querySelector('.item-id').value = '';
                }
                refreshRowDatalists(tr);
            }

            if (event.target.classList.contains('item-id')) {
                updateCategoryFromItem(tr);
            }
        });

        linesBody.addEventListener('blur', (event) => {
            if (!event.target.classList.contains('item-id')) return;
            const tr = event.target.closest('tr');
            if (!tr) return;
            updateCategoryFromItem(tr);
        }, true);

        void loadCatalogForCompany();

        postBtn.addEventListener('click', async () => {
            statusBox.className = 'status-box';
            statusBox.textContent = '';

            try {
                const payload = buildPayload();
                postBtn.disabled = true;
                postBtn.textContent = 'Posting...';

                await callPost(payload);
                addPrToList({
                    prNo: payload.pr_no,
                    contactName: payload.contact_name,
                    department: payload.department,
                    prDate: payload.pr_date,
                    lineCount: payload.lines.length,
                });
                showStatus('Purchase requisition posted successfully.');
            } catch (error) {
                showStatus(error.message, 'error');
            } finally {
                postBtn.disabled = false;
                postBtn.textContent = 'Post to D365';
            }
        });
    </script>
</body>
</html>
