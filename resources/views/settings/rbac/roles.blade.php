<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Settings - Roles</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('settings.rbac.partials.styles')
    @include('settings.rbac.partials.d365-rbac-page-styles')
</head>
<body
    data-url-roles="{{ route('settings.roles.api.roles.index') }}"
    data-url-roles-store="{{ route('settings.roles.api.roles.store') }}"
    data-url-permissions="{{ route('settings.roles.api.permissions.index') }}"
>
    @include('partials.global-company-selector')
    @include('settings.rbac.partials.sidebar')

    <main class="main">
        <div class="page-bar">
            <div>
                <h1 class="page-title">Settings</h1>
                <p class="rbac-page-intro">Manage global roles below. Click <strong>Role name</strong> to view. Use <strong>+ New</strong> to create roles and assign permissions.</p>
            </div>
        </div>

        <div id="flashError" class="flash-error"></div>

        <div id="listChrome">
            <div class="d365-title-row">
                <h1 class="d365-page-h1">Roles</h1>
            </div>
            <div class="d365-cmd-bar" id="toolbarList">
                <button type="button" class="d365-cmd d365-cmd-primary" id="cmdNew">+ New</button>
                <span class="d365-cmd-sep" aria-hidden="true"></span>
                <button type="button" class="d365-cmd d365-cmd-danger" id="cmdDelete" disabled>Delete</button>
            </div>
            <div class="d365-filter-row" id="filterWrap">
                <label for="filterInput">Filter</label>
                <input type="search" id="filterInput" placeholder="" autocomplete="off" aria-label="Filter roles">
            </div>

            <section id="listSection">
                <div class="table-card">
                    <table class="users-grid" aria-label="Roles list">
                        <thead>
                            <tr>
                                <th class="td-radio" aria-label="Select"></th>
                                <th>Role name</th>
                                <th>Permissions</th>
                            </tr>
                        </thead>
                        <tbody id="rolesTableBody"></tbody>
                    </table>
                    <div id="emptyHint" class="empty-hint" style="display:none;">No roles yet. Choose <strong>+ New</strong> to create one.</div>
                </div>
            </section>
        </div>

        <section id="detailSection" class="detail-card" style="display:none;">
            <div class="detail-top">
                <div class="d365-bc" id="detailBreadcrumb">Roles</div>
                <h2 class="d365-record-title" id="detailRecordTitle">Role</h2>
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
                    Role details
                </div>
                <div class="d365-section-body">
                    <div class="form-grid">
                        <div class="field" style="grid-column: 1 / -1;">
                            <label for="f_name">Role name <span class="req">*</span></label>
                            <input type="text" id="f_name" autocomplete="off" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d365-section">
                <div class="d365-section-head">
                    <span class="d365-caret" aria-hidden="true">&#9660;</span>
                    Permissions
                </div>
                <div class="d365-section-body">
                    <div class="roles-toolbar">
                        <button type="button" class="primary" id="btnToggleAssignPerms">+ Assign permissions</button>
                        <button type="button" disabled title="Coming soon">Remove permission</button>
                    </div>
                    <div id="permsEditorWrap" style="display:none;">
                        <div id="permChecks" class="role-checks"></div>
                    </div>
                    <div id="permsReadonlyWrap" style="display:none;">
                        <ul id="permsReadonlyList" class="role-readonly-list"></ul>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
(function () {
    var body = document.body;
    var urlRoles = body.dataset.urlRoles;
    var urlRolesStore = body.dataset.urlRolesStore;
    var urlPermissions = body.dataset.urlPermissions;
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    var state = {
        mainTab: 'list',
        formMode: 'hidden',
        roles: [],
        allPermissions: [],
        selectedRoleId: null,
        roleId: null,
        assignPermsOpen: true,
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

        var hasRow = state.selectedRoleId !== null;
        $('cmdDelete').disabled = !hasRow;

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
            return 'New role';
        }
        var nm = ($('f_name').value || '').trim();
        return nm || 'Role';
    }

    function updateDetailChrome() {
        $('detailBreadcrumb').textContent = 'Roles';
        $('detailRecordTitle').textContent = computeRecordTitle();
    }

    function selectedRole() {
        return state.roles.find(function (r) { return Number(r.id) === Number(state.selectedRoleId); });
    }

    function applyFormMode() {
        var view = state.formMode === 'view';
        var edit = state.formMode === 'edit';
        var isNew = state.formMode === 'new';

        updateDetailChrome();

        if (state.mainTab === 'list') {
            return;
        }

        $('btnSave').style.display = (isNew || edit) ? 'inline-block' : 'none';
        $('btnEdit').style.display = view ? 'inline-block' : 'none';
        $('btnDeleteDetail').style.display = view ? 'inline-block' : 'none';

        if (view) {
            $('f_name').disabled = true;
            $('permsEditorWrap').style.display = 'none';
            $('permsReadonlyWrap').style.display = 'block';
            $('btnToggleAssignPerms').disabled = true;
            state.assignPermsOpen = false;

            var ul = $('permsReadonlyList');
            ul.innerHTML = '';
            var role = selectedRole();
            (role && role.permissions ? role.permissions : []).forEach(function (p) {
                var li = document.createElement('li');
                li.textContent = p.name + ' (' + p.slug + ')';
                ul.appendChild(li);
            });
            if (!ul.children.length) {
                var li = document.createElement('li');
                li.className = 'muted';
                li.textContent = 'No permissions assigned.';
                ul.appendChild(li);
            }
        } else if (edit || isNew) {
            $('f_name').disabled = false;
            $('permsReadonlyWrap').style.display = 'none';
            $('btnToggleAssignPerms').disabled = false;
            if (state.assignPermsOpen) {
                $('permsEditorWrap').style.display = 'block';
                renderPermissionChecks();
            } else {
                $('permsEditorWrap').style.display = 'none';
            }
        }

    }

    function renderPermissionChecks() {
        var wrap = $('permChecks');
        wrap.innerHTML = '';
        var role = selectedRole();
        state.allPermissions.forEach(function (p) {
            var lab = document.createElement('label');
            var cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.value = String(p.id);
            var checked = false;
            if (state.formMode === 'edit' && role && (role.permissions || []).some(function (x) {
                return Number(x.id) === Number(p.id);
            })) {
                checked = true;
            }
            cb.checked = checked;
            lab.appendChild(cb);
            lab.appendChild(document.createTextNode(p.name + ' (' + p.slug + ')'));
            wrap.appendChild(lab);
        });
        if (!state.allPermissions.length) {
            var d = document.createElement('div');
            d.className = 'muted';
            d.textContent = 'No permissions in database. Run PermissionSeeder.';
            wrap.appendChild(d);
        }
    }

    function gatherPermissionIds() {
        var ids = [];
        document.querySelectorAll('#permChecks input[type="checkbox"]').forEach(function (cb) {
            if (cb.checked) ids.push(Number(cb.value));
        });
        return ids;
    }

    function populateFormFields(r) {
        state.roleId = r.id;
        state.selectedRoleId = r.id;
        $('f_name').value = r.name || '';
        syncChrome();
        updateDetailChrome();
    }

    function selectRowOnly(r) {
        clearError();
        state.selectedRoleId = r.id;
        syncChrome();
        highlightRow();
    }

    function highlightRow() {
        document.querySelectorAll('#rolesTableBody tr').forEach(function (tr) {
            tr.classList.toggle('selected', Number(tr.dataset.rid) === Number(state.selectedRoleId));
        });
    }

    function pruneStaleSelection() {
        if (state.selectedRoleId === null) return;
        var exists = state.roles.some(function (r) { return r.id === state.selectedRoleId; });
        if (!exists) state.selectedRoleId = null;
        syncChrome();
    }

    function openViewForRole(r) {
        selectRowOnly(r);
        openViewTab();
    }

    function renderTable() {
        var tbody = $('rolesTableBody');
        tbody.innerHTML = '';

        var q = state.filter.trim().toLowerCase();
        var list = state.roles.filter(function (r) {
            if (!q) return true;
            var blob = [r.name, String(r.permission_count)].join(' ').toLowerCase();
            return blob.indexOf(q) !== -1;
        });

        if (!list.length) {
            $('emptyHint').style.display = 'block';
            $('emptyHint').innerHTML = 'No roles yet. Choose <strong>+ New</strong>.';
        } else {
            $('emptyHint').style.display = 'none';
        }

        list.forEach(function (r) {
            var tr = document.createElement('tr');
            tr.dataset.rid = String(r.id);

            var tdRad = document.createElement('td');
            tdRad.className = 'td-radio';
            var rad = document.createElement('input');
            rad.type = 'radio';
            rad.name = 'rolePick';
            rad.setAttribute('aria-label', 'Select row');
            if (Number(state.selectedRoleId) === Number(r.id)) rad.checked = true;
            rad.addEventListener('click', function (e) {
                e.stopPropagation();
                selectRowOnly(r);
            });
            tdRad.appendChild(rad);

            var tdName = document.createElement('td');
            var aName = document.createElement('a');
            aName.href = '#';
            aName.className = 'user-id-link view-inline-link';
            aName.setAttribute('aria-label', 'View role');
            aName.textContent = r.name || '-';
            aName.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                openViewForRole(r);
            });
            tdName.appendChild(aName);

            var tdCnt = document.createElement('td');
            tdCnt.textContent = String(r.permission_count);

            tr.appendChild(tdRad);
            tr.appendChild(tdName);
            tr.appendChild(tdCnt);

            tr.addEventListener('click', function () {
                rad.checked = true;
                selectRowOnly(r);
            });
            tbody.appendChild(tr);
        });
        pruneStaleSelection();
        highlightRow();
    }

    function reloadRoles() {
        return api(urlRoles)
            .then(function (ref) {
                state.roles = ref.data.roles || [];
                renderTable();
            });
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
        state.selectedRoleId = null;
        state.roleId = null;
        state.assignPermsOpen = true;
        syncChrome();

        $('f_name').value = '';

        $('permsEditorWrap').style.display = 'block';
        renderPermissionChecks();

        applyFormMode();
        $('detailSection').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function openViewTab() {
        if (state.selectedRoleId === null) {
            showError('Select a role in the list first.');
            return;
        }
        var r = state.roles.find(function (x) { return x.id === state.selectedRoleId; });
        if (!r) {
            showError('Selected role is no longer in the list.');
            state.selectedRoleId = null;
            syncChrome();
            return;
        }
        clearError();
        state.mainTab = 'view';
        state.formMode = 'view';
        state.assignPermsOpen = false;
        syncChrome();

        populateFormFields(r);
        applyFormMode();
        $('detailSection').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function save() {
        if (state.saving) return;
        clearError();

        var name = $('f_name').value.trim();
        if (!name) {
            showError('Role name is required.');
            return;
        }

        state.saving = true;
        $('btnSave').disabled = true;

        var promise;
        if (state.formMode === 'new') {
            promise = api(urlRolesStore, {
                method: 'POST',
                body: JSON.stringify({
                    name: name,
                    permission_ids: gatherPermissionIds()
                })
            });
        } else if (state.formMode === 'edit' && state.roleId) {
            var url = urlRoles.replace(/\/?$/, '/') + state.roleId;
            promise = api(url, {
                method: 'PUT',
                body: JSON.stringify({
                    name: name,
                    permission_ids: gatherPermissionIds()
                })
            });
        } else {
            state.saving = false;
            $('btnSave').disabled = false;
            return;
        }

        promise.then(function (ref) {
            state.saving = false;
            $('btnSave').disabled = false;
            if (!ref.res.ok) {
                showError(ref.data.message || firstValidationMessage(ref.data) || ('HTTP ' + ref.res.status));
                return;
            }
            return reloadRoles().then(function () {
                if (ref.data.role) {
                    state.selectedRoleId = ref.data.role.id;
                    syncChrome();
                }
                openList();
                highlightRow();
            });
        }).catch(function () {
            state.saving = false;
            $('btnSave').disabled = false;
            showError('Network error.');
        });
    }

    function deleteRoleById(id) {
        if (!id || !window.confirm('Delete this role? Memberships using it will lose this role.')) return;
        var url = urlRoles.replace(/\/?$/, '/') + id;
        api(url, { method: 'DELETE' }).then(function (ref) {
            if (!ref.res.ok) {
                showError(ref.data.message || 'Could not delete.');
                return;
            }
            state.selectedRoleId = null;
            reloadRoles().then(function () {
                openList();
            });
        });
    }

    $('cmdNew').addEventListener('click', openNew);
    $('cmdDelete').addEventListener('click', function () {
        if ($('cmdDelete').disabled || state.selectedRoleId === null) return;
        deleteRoleById(state.selectedRoleId);
    });

    $('filterInput').addEventListener('input', function (e) {
        state.filter = e.target.value;
        renderTable();
    });

    $('f_name').addEventListener('input', function () {
        if (state.mainTab !== 'list') updateDetailChrome();
    });

    $('btnToggleAssignPerms').addEventListener('click', function () {
        if (state.formMode === 'view') return;
        state.assignPermsOpen = !state.assignPermsOpen;
        if (state.assignPermsOpen) {
            $('permsEditorWrap').style.display = 'block';
            renderPermissionChecks();
        } else {
            $('permsEditorWrap').style.display = 'none';
        }
    });

    $('btnEdit').addEventListener('click', function () {
        clearError();
        state.formMode = 'edit';
        state.assignPermsOpen = true;
        $('permsEditorWrap').style.display = 'block';
        renderPermissionChecks();
        applyFormMode();
    });

    $('btnCancelDetail').addEventListener('click', function () {
        clearError();
        openList();
    });

    $('btnSave').addEventListener('click', save);

    $('btnDeleteDetail').addEventListener('click', function () {
        if (state.roleId) deleteRoleById(state.roleId);
    });

    syncChrome();
    Promise.all([api(urlPermissions), reloadRoles()]).then(function (results) {
        state.allPermissions = results[0].data.permissions || [];
    }).catch(function () {
        showError('Could not load roles or permissions.');
    });
})();
    </script>
</body>
</html>
