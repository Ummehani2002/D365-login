<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Settings - Permissions</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('settings.rbac.partials.styles')
    @include('settings.rbac.partials.d365-rbac-page-styles')
</head>
<body
    data-url-permissions="{{ route('settings.permissions.api.permissions.index') }}"
    data-url-permissions-store="{{ route('settings.permissions.api.permissions.store') }}"
>
    @include('partials.global-company-selector')
    @include('settings.rbac.partials.sidebar')

    <main class="main">
        <div class="page-bar">
            <div>
                <h1 class="page-title">Settings</h1>
                <p class="rbac-page-intro">Manage permissions stored in the database. These are used when assigning permissions to roles.</p>
            </div>
        </div>

        <div id="flashError" class="flash-error"></div>

        <div id="listChrome">
            <div class="d365-title-row">
                <h1 class="d365-page-h1">Permissions</h1>
            </div>
            <div class="d365-cmd-bar" id="toolbarList">
                <button type="button" class="d365-cmd d365-cmd-primary" id="cmdNew">+ New</button>
                <span class="d365-cmd-sep" aria-hidden="true"></span>
                <button type="button" class="d365-cmd d365-cmd-danger" id="cmdDelete" disabled>Delete</button>
            </div>
            <div class="d365-filter-row" id="filterWrap">
                <label for="filterInput">Filter</label>
                <input type="search" id="filterInput" placeholder="" autocomplete="off" aria-label="Filter permissions">
            </div>

            <section id="listSection">
                <div class="table-card">
                    <table class="users-grid" aria-label="Permissions list">
                        <thead>
                            <tr>
                                <th class="td-radio" aria-label="Select"></th>
                                <th>Slug</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody id="permsTableBody"></tbody>
                    </table>
                    <div id="emptyHint" class="empty-hint" style="display:none;">No permissions yet. Choose <strong>+ New</strong> to create one.</div>
                </div>
            </section>
        </div>

        <section id="detailSection" class="detail-card" style="display:none;">
            <div class="detail-top">
                <div class="d365-bc" id="detailBreadcrumb">Permissions</div>
                <h2 class="d365-record-title" id="detailRecordTitle">Permission</h2>
                <div class="d365-cmd-bar">
                    <button type="button" class="d365-cmd btn-secondary" id="btnCancelDetail">Close</button>
                    <button type="button" class="d365-cmd btn-save d365-cmd-primary" id="btnSave">Save</button>
                    <button type="button" class="d365-cmd btn-edit" id="btnEdit" style="display:none;">Edit</button>
                    <span class="d365-cmd-sep" aria-hidden="true"></span>
                    <button type="button" class="d365-cmd d365-cmd-danger" id="btnDeleteDetail" style="display:none;">Delete</button>
                </div>
            </div>

            <div class="d365-section">
                <div class="d365-section-head">
                    <span class="d365-caret" aria-hidden="true">&#9660;</span>
                    Permission details
                </div>
                <div class="d365-section-body">
                    <div class="form-grid">
                        <div class="field" style="grid-column: 1 / -1;">
                            <label for="f_slug">Slug <span class="req">*</span></label>
                            <input type="text" id="f_slug" autocomplete="off" required placeholder="e.g. users.manage">
                            <div class="muted" style="font-size:12px;margin-top:6px;">Allowed characters: letters, digits, dot, dash, underscore.</div>
                        </div>
                        <div class="field" style="grid-column: 1 / -1;">
                            <label for="f_name">Name <span class="req">*</span></label>
                            <input type="text" id="f_name" autocomplete="off" required placeholder="e.g. Manage users">
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
(function () {
    var body = document.body;
    var urlPerms = body.dataset.urlPermissions;
    var urlPermsStore = body.dataset.urlPermissionsStore;
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    var state = {
        mainTab: 'list',
        formMode: 'hidden', // hidden | view | new | edit
        permissions: [],
        selectedPermissionId: null,
        permissionId: null,
        filter: '',
        saving: false
    };

    function $(id) { return document.getElementById(id); }

    function showError(msg) {
        var el = $('flashError');
        el.textContent = msg || 'Something went wrong.';
        el.classList.add('visible');
    }

    function clearError() {
        var el = $('flashError');
        el.textContent = '';
        el.classList.remove('visible');
    }

    function syncChrome() {
        var onList = state.mainTab === 'list';
        $('listChrome').style.display = onList ? 'block' : 'none';
        $('detailSection').style.display = onList ? 'none' : 'block';

        $('cmdDelete').disabled = state.selectedPermissionId === null;

        $('cmdNew').classList.toggle('d365-cmd-active', state.mainTab === 'new');
    }

    function api(path, opts) {
        opts = opts || {};
        var headers = Object.assign({
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest'
        }, opts.headers || {});
        if (opts.body && typeof opts.body === 'string') {
            headers['Content-Type'] = 'application/json';
        }
        return fetch(path, Object.assign({}, opts, { headers: headers })).then(function (res) {
            return res.json().then(function (data) {
                return { res: res, data: data };
            }).catch(function () {
                return { res: res, data: {} };
            });
        });
    }

    function firstValidationMessage(data) {
        if (!data.errors) return null;
        var keys = Object.keys(data.errors);
        if (!keys.length) return null;
        var arr = data.errors[keys[0]];
        return Array.isArray(arr) && arr.length ? arr[0] : null;
    }

    function computeRecordTitle() {
        if (state.formMode === 'new') {
            return 'New permission';
        }
        var slug = ($('f_slug').value || '').trim();
        return slug || 'Permission';
    }

    function updateDetailChrome() {
        $('detailBreadcrumb').textContent = 'Permissions';
        $('detailRecordTitle').textContent = computeRecordTitle();
    }

    function selectedPermission() {
        return state.permissions.find(function (p) { return Number(p.id) === Number(state.selectedPermissionId); });
    }

    function setFormDisabled(disabled) {
        ['f_slug', 'f_name'].forEach(function (id) { $(id).disabled = disabled; });
    }

    function applyFormMode() {
        var view = state.formMode === 'view';
        var edit = state.formMode === 'edit';
        var isNew = state.formMode === 'new';

        updateDetailChrome();

        if (state.mainTab === 'list') return;

        $('btnSave').style.display = (isNew || edit) ? 'inline-block' : 'none';
        $('btnEdit').style.display = view ? 'inline-block' : 'none';
        $('btnDeleteDetail').style.display = view ? 'inline-block' : 'none';

        if (view) {
            setFormDisabled(true);
        } else if (edit || isNew) {
            setFormDisabled(false);
            $('f_slug').disabled = !isNew;
        }
    }

    function populateFormFields(p) {
        state.permissionId = p.id;
        state.selectedPermissionId = p.id;
        $('f_slug').value = p.slug || '';
        $('f_name').value = p.name || '';
        syncChrome();
    }

    function openList() {
        state.mainTab = 'list';
        state.formMode = 'hidden';
        syncChrome();
        applyFormMode();
    }

    function openNew() {
        clearError();
        state.mainTab = 'new';
        state.formMode = 'new';
        state.selectedPermissionId = null;
        state.permissionId = null;
        syncChrome();

        $('f_slug').value = '';
        $('f_name').value = '';

        applyFormMode();
        $('detailSection').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function openViewTab() {
        if (state.selectedPermissionId === null) {
            showError('Select a permission in the list first.');
            return;
        }
        var p = selectedPermission();
        if (!p) {
            showError('Selected permission is no longer in the list.');
            state.selectedPermissionId = null;
            syncChrome();
            return;
        }
        clearError();
        state.mainTab = 'view';
        state.formMode = 'view';
        syncChrome();

        populateFormFields(p);
        applyFormMode();
        $('detailSection').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function highlightRow() {
        var rows = document.querySelectorAll('#permsTableBody tr');
        rows.forEach(function (tr) {
            tr.classList.toggle('selected', Number(tr.dataset.pid) === Number(state.selectedPermissionId));
        });
    }

    function renderTable() {
        var tbody = $('permsTableBody');
        tbody.innerHTML = '';
        var q = state.filter.trim().toLowerCase();
        var list = state.permissions.filter(function (p) {
            if (!q) return true;
            var blob = [p.slug, p.name].join(' ').toLowerCase();
            return blob.indexOf(q) !== -1;
        });

        $('emptyHint').style.display = list.length ? 'none' : 'block';

        list.forEach(function (p) {
            var tr = document.createElement('tr');
            tr.dataset.pid = String(p.id);

            var tdRad = document.createElement('td');
            tdRad.className = 'td-radio';
            var rad = document.createElement('input');
            rad.type = 'radio';
            rad.name = 'permPick';
            rad.setAttribute('aria-label', 'Select row');
            if (Number(state.selectedPermissionId) === Number(p.id)) rad.checked = true;
            rad.addEventListener('click', function (e) {
                e.stopPropagation();
                state.selectedPermissionId = p.id;
                syncChrome();
                highlightRow();
            });
            tdRad.appendChild(rad);

            var tdSlug = document.createElement('td');
            var aSlug = document.createElement('a');
            aSlug.href = '#';
            aSlug.className = 'user-id-link view-inline-link';
            aSlug.setAttribute('aria-label', 'View permission');
            aSlug.textContent = p.slug || '-';
            aSlug.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                state.selectedPermissionId = p.id;
                openViewTab();
            });
            tdSlug.appendChild(aSlug);

            var tdName = document.createElement('td');
            tdName.textContent = p.name || '';

            tr.appendChild(tdRad);
            tr.appendChild(tdSlug);
            tr.appendChild(tdName);

            tr.addEventListener('click', function () {
                rad.checked = true;
                state.selectedPermissionId = p.id;
                syncChrome();
                highlightRow();
            });
            tbody.appendChild(tr);
        });

        highlightRow();
    }

    function reloadPermissions() {
        return api(urlPerms).then(function (ref) {
            state.permissions = ref.data.permissions || [];
            renderTable();
        });
    }

    function save() {
        if (state.saving) return;
        clearError();

        var payload = {
            slug: $('f_slug').value.trim(),
            name: $('f_name').value.trim()
        };

        state.saving = true;
        $('btnSave').disabled = true;

        var promise;
        if (state.formMode === 'new') {
            promise = api(urlPermsStore, { method: 'POST', body: JSON.stringify(payload) });
        } else if (state.formMode === 'edit' && state.permissionId) {
            var url = urlPerms.replace(/\/?$/, '/') + state.permissionId;
            promise = api(url, { method: 'PUT', body: JSON.stringify({ name: payload.name }) });
        } else {
            state.saving = false;
            $('btnSave').disabled = false;
            return;
        }

        promise.then(function (ref) {
            state.saving = false;
            $('btnSave').disabled = false;

            if (!ref.res.ok) {
                var msg = ref.data.message || firstValidationMessage(ref.data) || ('HTTP ' + ref.res.status);
                showError(msg);
                return;
            }

            return reloadPermissions().then(function () {
                if (ref.data.permission) {
                    state.selectedPermissionId = ref.data.permission.id;
                }
                openList();
            });
        }).catch(function () {
            state.saving = false;
            $('btnSave').disabled = false;
            showError('Network error.');
        });
    }

    function deletePermissionById(id) {
        if (!id || !window.confirm('Delete this permission? Roles using it will lose it.')) return;
        var url = urlPerms.replace(/\/?$/, '/') + id;
        api(url, { method: 'DELETE' }).then(function (ref) {
            if (!ref.res.ok) {
                showError(ref.data.message || 'Could not delete.');
                return;
            }
            state.selectedPermissionId = null;
            reloadPermissions().then(function () { openList(); });
        }).catch(function () {
            showError('Network error.');
        });
    }

    $('cmdNew').addEventListener('click', function () { openNew(); });
    $('cmdDelete').addEventListener('click', function () {
        if ($('cmdDelete').disabled || state.selectedPermissionId === null) return;
        deletePermissionById(state.selectedPermissionId);
    });
    $('btnCancelDetail').addEventListener('click', function () { clearError(); openList(); });
    $('btnSave').addEventListener('click', save);
    $('btnDeleteDetail').addEventListener('click', function () {
        if (!state.permissionId) return;
        deletePermissionById(state.permissionId);
    });
    $('btnEdit').addEventListener('click', function () {
        clearError();
        state.formMode = 'edit';
        applyFormMode();
    });
    $('filterInput').addEventListener('input', function (e) {
        state.filter = e.target.value;
        renderTable();
    });
    ['f_slug', 'f_name'].forEach(function (id) {
        $(id).addEventListener('input', function () {
            if (state.mainTab !== 'list') updateDetailChrome();
        });
    });

    syncChrome();
    reloadPermissions().catch(function () { showError('Could not load permissions.'); });
})();
    </script>
</body>
</html>
