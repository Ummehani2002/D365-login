<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Settings — Users</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('settings.rbac.partials.styles')
    @include('settings.rbac.partials.d365-rbac-page-styles')
    <style>
        /* Keep the Organizations form aligned and simple. */
        #orgScopeSection .org-access-options {
            display: block;
            width: 100%;
        }
        #orgScopeSection .org-access-option {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            margin: 0 0 10px;
            cursor: default;
        }
        #orgScopeSection .org-access-option:last-child {
            margin-bottom: 0;
        }
        #orgScopeSection .org-access-option input[type="radio"] {
            margin: 0;
            flex: none;
            width: auto !important;
            min-width: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }
        #orgScopeSection .org-access-option span {
            display: block;
            line-height: 1.35;
            font-size: 14px;
            color: #323130;
        }
        #orgScopeSection #orgCompaniesChecks.org-company-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-height: 340px;
            overflow: auto;
            overflow-x: hidden;
            padding: 10px 12px;
            border: 1px solid #edebe9;
            border-radius: 2px;
            background: #faf9f8;
            width: 100%;
        }
        #orgScopeSection #orgCompaniesChecks .org-company-row {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            margin: 0;
            cursor: default;
            color: #323130;
            font-size: 14px;
            line-height: 1.35;
        }
        #orgScopeSection #orgCompaniesChecks .org-company-row input[type="checkbox"] {
            margin: 0;
            flex: none;
            width: auto !important;
            min-width: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }
        #orgScopeSection #orgCompaniesChecks .org-company-row span {
            display: block;
            white-space: normal;
            word-break: break-word;
        }
    </style>
</head>
<body
    data-url-memberships="{{ route('settings.users.api.memberships.index') }}"
    data-url-memberships-store="{{ route('settings.users.api.memberships.store') }}"
    data-url-companies="{{ route('settings.users.api.companies.index') }}"
    data-url-roles="{{ route('settings.users.api.roles.index') }}"
    data-url-role-scopes="{{ route('settings.users.api.memberships.role-scopes.index', ['membership' => 0]) }}"
    data-url-role-scopes-upsert="{{ route('settings.users.api.memberships.role-scopes.upsert', ['membership' => 0]) }}"
>
    @include('partials.global-company-selector')
    @include('settings.rbac.partials.sidebar')

    <main class="main">
        <div class="page-bar">
            <div>
                <h1 class="page-title">Settings</h1>
            </div>
        </div>

        <div id="flashError" class="flash-error"></div>

        <div id="listChrome">
            <div class="d365-title-row">
                <h1 class="d365-page-h1">Users</h1>
            </div>
            <div class="d365-cmd-bar" id="toolbarList">
                <button type="button" class="d365-cmd d365-cmd-primary" id="cmdNew">+ New</button>
                <span class="d365-cmd-sep" aria-hidden="true"></span>
                <button type="button" class="d365-cmd" id="cmdDelete" disabled>Delete</button>
                <button type="button" class="d365-cmd" id="cmdImportUsers">Import users</button>
            </div>

            <section id="listSection">
                <div class="table-card">
                    <table class="users-grid" aria-label="Users list">
                        <thead>
                            <tr>
                                <th class="td-radio" aria-label="Select"></th>
                                <th>User ID</th>
                                <th>User name</th>
                                <th>Email</th>
                                <th>Company</th>
                                <th>Person</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody"></tbody>
                    </table>
                    <div id="emptyHint" class="empty-hint" style="display:none;">No users yet. Choose <strong>+ New</strong> to add one.</div>
                </div>
            </section>
        </div>

        <section id="detailSection" class="detail-card" style="display:none;">
            <div class="detail-top">
                <div class="d365-bc" id="detailBreadcrumb">Users</div>
                <h2 class="d365-record-title" id="detailRecordTitle">User</h2>
                <div class="d365-cmd-bar">
                    <button type="button" class="d365-cmd btn-secondary" id="btnCancelDetail">Close</button>
                    <button type="button" class="d365-cmd btn-save d365-cmd-primary" id="btnSave">Save</button>
                    <button type="button" class="d365-cmd btn-edit" id="btnEdit" style="display:none;">Edit</button>
                    <span class="d365-cmd-sep" aria-hidden="true"></span>
                    <button type="button" class="d365-cmd" disabled title="Coming soon">Delete</button>
                </div>
            </div>

            <div class="d365-section">
                <div class="d365-section-head">
                    <span class="d365-caret" aria-hidden="true">&#9660;</span>
                    User details
                </div>
                <div class="d365-section-body">
                    <div class="form-grid">
                        <div class="field">
                            <label for="f_user_code">User ID</label>
                            <input type="text" id="f_user_code" autocomplete="off">
                        </div>
                        <div class="field">
                            <label for="f_person">Person</label>
                            <input type="text" id="f_person" class="d365-readonly-display" readonly tabindex="-1">
                        </div>
                        <div class="field">
                            <label for="f_name">User name <span class="req">*</span></label>
                            <input type="text" id="f_name" autocomplete="name" required>
                        </div>
                        <div class="field">
                            <label for="f_email">Email <span class="req">*</span></label>
                            <input type="email" id="f_email" autocomplete="email" required>
                        </div>
                        <div class="field" style="grid-column: 1 / -1;">
                            <label for="f_company">Company <span class="req">*</span></label>
                            <select id="f_company"></select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d365-section">
                <div class="d365-section-head">
                    <span class="d365-caret" aria-hidden="true">&#9660;</span>
                    User's roles
                </div>
                <div class="d365-section-body">
                    <div class="roles-toolbar">
                        <button type="button" class="primary" id="btnToggleAssignRoles">+ Assign roles</button>
                        <button type="button" id="btnRemoveRole" disabled>Remove role</button>
                        <button type="button" class="d365-cmd" id="btnAssignOrganizations">Assign organizations</button>
                    </div>

                    <div id="rolesEditorWrap" style="display:none;">
                        <div class="muted" style="margin-bottom:8px;font-size:12px;">Roles for the selected company</div>
                        <div id="rolesChecks" class="role-checks"></div>
                    </div>
                    <div id="rolesReadonlyWrap" style="display:none;">
                        <ul id="rolesReadonlyList" class="role-readonly-list"></ul>
                    </div>
                </div>
            </div>
        </section>

        <section id="orgScopeSection" class="detail-card" style="display:none;">
            <div class="detail-top">
                <div class="d365-bc" id="orgBreadcrumb">Users</div>
                <h2 class="d365-record-title" id="orgRecordTitle">Organizations</h2>
                <div class="d365-cmd-bar">
                    <button type="button" class="d365-cmd btn-secondary" id="orgClose">Close</button>
                    <button type="button" class="d365-cmd d365-cmd-primary" id="orgSave">Save</button>
                </div>
            </div>

            <div class="d365-section">
                <div class="d365-section-head">
                    <span class="d365-caret" aria-hidden="true">&#9660;</span>
                    Organizations
                </div>
                <div class="d365-section-body">
                    <div class="muted" style="font-size:12px;margin-bottom:10px;" id="orgSubtitle">
                        Select organizations for this user and role.
                    </div>

                    <div class="form-grid">
                        <div class="field">
                            <label>User</label>
                            <input type="text" id="org_user_text" class="d365-readonly-display" readonly tabindex="-1">
                        </div>
                        <div class="field">
                            <label>Role</label>
                            <input type="text" id="org_role_text" class="d365-readonly-display" readonly tabindex="-1">
                        </div>
                        <div class="field" style="grid-column: 1 / -1;">
                            <label>Home company</label>
                            <input type="text" id="org_home_company_text" class="d365-readonly-display" readonly tabindex="-1">
                        </div>

                        <div class="field" style="grid-column: 1 / -1;">
                            <label>Access</label>
                            <div class="org-access-options">
                                <div class="org-access-option">
                                    <input type="radio" name="org_access_mode" id="org_all" value="all">
                                    <span>Grant access to all organizations</span>
                                </div>
                                <div class="org-access-option">
                                    <input type="radio" name="org_access_mode" id="org_specific" value="specific">
                                    <span>Grant access to selected organizations</span>
                                </div>
                            </div>
                        </div>

                        <div class="field" style="grid-column: 1 / -1;" id="orgCompaniesBlock">
                            <label>Select organizations</label>
                            <div id="orgCompaniesChecks" class="org-company-list"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
(function () {
    var body = document.body;
    var urlMemberships = body.dataset.urlMemberships;
    var urlMembershipsStore = body.dataset.urlMembershipsStore;
    var urlCompanies = body.dataset.urlCompanies;
    var urlRoles = body.dataset.urlRoles;
    var urlRoleScopesTemplate = body.dataset.urlRoleScopes;
    var urlRoleScopesUpsertTemplate = body.dataset.urlRoleScopesUpsert;

    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    var state = {
        mainTab: 'list',
        formMode: 'hidden',
        memberships: [],
        companies: [],
        companyRoles: [],
        selectedMembershipId: null,
        assignRolesOpen: false,
        membershipId: null,
        userId: null,
        saving: false,
        roleScopes: {}, // role_id -> { all_organizations: bool, company_ids: [] } for existing membership
        pendingRoleScopes: {}, // role_id -> { all_organizations: bool, company_ids: [] } during new user flow
        orgRoleContext: null // { id, name }
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
        var onOrgs = state.mainTab === 'orgs';
        var onDetail = !onList && !onOrgs;

        $('listChrome').style.display = onList ? 'block' : 'none';
        $('detailSection').style.display = onDetail ? 'block' : 'none';
        $('orgScopeSection').style.display = onOrgs ? 'block' : 'none';

        $('cmdDelete').disabled = state.selectedMembershipId === null;

        $('cmdNew').classList.toggle('d365-cmd-active', state.mainTab === 'new');
    }

    function computeRecordTitle() {
        if (state.formMode === 'new') {
            return 'New user';
        }
        var uc = ($('f_user_code').value || '').trim();
        var nm = ($('f_name').value || '').trim();
        var em = ($('f_email').value || '').trim();
        var left = uc;
        if (!left && em.indexOf('@') !== -1) {
            left = em.split('@')[0];
        }
        if (!left) {
            left = 'User';
        }
        return left + ' : ' + (nm || '-');
    }

    function updateDetailChrome() {
        $('detailBreadcrumb').textContent = 'Users';
        $('detailRecordTitle').textContent = computeRecordTitle();
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

    function personFromName(name) {
        return String(name || '').trim().toUpperCase();
    }

    function setDetailHeading() {
        updateDetailChrome();
    }

    function refreshPersonField() {
        $('f_person').value = personFromName($('f_name').value);
        if (state.mainTab !== 'list') {
            updateDetailChrome();
        }
    }

    function populateCompanySelect(lockSelection) {
        var sel = $('f_company');
        var prev = sel.value;
        sel.innerHTML = '';
        var opt0 = document.createElement('option');
        opt0.value = '';
        opt0.textContent = 'Select company...';
        sel.appendChild(opt0);
        state.companies.forEach(function (c) {
            var o = document.createElement('option');
            o.value = String(c.id);
            o.textContent = c.code + ' - ' + c.name;
            sel.appendChild(o);
        });
        if (lockSelection && prev) sel.value = prev;
    }

    function setFormDisabled(disabled) {
        ['f_user_code', 'f_name', 'f_email', 'f_company'].forEach(function (id) {
            $(id).disabled = disabled;
        });
    }

    function updateRemoveRoleButton() {
        var btn = $('btnRemoveRole');
        var editable = state.formMode === 'new' || state.formMode === 'edit';
        if (!editable || !state.assignRolesOpen) {
            btn.disabled = true;
            return;
        }
        var anyChecked = false;
        document.querySelectorAll('#rolesChecks input[type="checkbox"]').forEach(function (cb) {
            if (cb.checked) anyChecked = true;
        });
        btn.disabled = !anyChecked;
    }

    function applyFormMode() {
        var view = state.formMode === 'view';
        var edit = state.formMode === 'edit';
        var isNew = state.formMode === 'new';

        updateDetailChrome();

        if (state.mainTab === 'list' || state.mainTab === 'orgs') {
            return;
        }

        $('btnSave').style.display = (isNew || edit) ? 'inline-block' : 'none';
        $('btnEdit').style.display = view ? 'inline-block' : 'none';
        $('btnAssignOrganizations').disabled = !(isNew || edit);
        $('btnToggleAssignRoles').textContent = state.assignRolesOpen ? 'Hide role picker' : '+ Assign roles';

        if (view) {
            setFormDisabled(true);
            $('rolesEditorWrap').style.display = 'none';
            $('rolesReadonlyWrap').style.display = 'block';
            state.assignRolesOpen = false;
            $('btnToggleAssignRoles').disabled = true;
        } else if (edit || isNew) {
            setFormDisabled(false);
            $('rolesReadonlyWrap').style.display = state.assignRolesOpen ? 'none' : 'block';
            $('btnToggleAssignRoles').disabled = false;
            if (state.assignRolesOpen) {
                $('rolesEditorWrap').style.display = 'block';
                renderRoleChecks();
            } else {
                $('rolesEditorWrap').style.display = 'none';
            }
        }

        if (view || edit) {
            var ul = $('rolesReadonlyList');
            ul.innerHTML = '';
            var m = state.memberships.find(function (x) { return x.id === state.selectedMembershipId; });
            (m && m.roles ? m.roles : []).forEach(function (r) {
                var li = document.createElement('li');
                li.textContent = r.name;
                ul.appendChild(li);
            });
            if (!ul.children.length) {
                var li = document.createElement('li');
                li.className = 'muted';
                li.textContent = 'No roles assigned.';
                ul.appendChild(li);
            }
        }
        updateRemoveRoleButton();
    }

    function renderRoleChecks() {
        var wrap = $('rolesChecks');
        var currentChecked = [];
        wrap.querySelectorAll('input[type="checkbox"]').forEach(function (cb) {
            if (cb.checked) currentChecked.push(Number(cb.value));
        });
        wrap.innerHTML = '';

        var membership = state.memberships.find(function (x) {
            return x.id === state.selectedMembershipId;
        });

        state.companyRoles.forEach(function (r) {
            var lab = document.createElement('label');
            var cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.value = String(r.id);
            var checked = false;
            if (currentChecked.indexOf(Number(r.id)) !== -1) {
                checked = true;
            } else if (state.formMode === 'edit' && membership && (membership.roles || []).some(function (x) {
                return Number(x.id) === Number(r.id);
            })) {
                checked = true;
            }
            cb.checked = checked;
            cb.addEventListener('change', function () { updateRemoveRoleButton(); });
            lab.appendChild(cb);
            lab.appendChild(document.createTextNode(r.name));
            wrap.appendChild(lab);
        });
        if (!state.companyRoles.length) {
            var p = document.createElement('div');
            p.className = 'muted';
            p.textContent = 'No roles defined for this company yet (seed permissions / open Roles).';
            wrap.appendChild(p);
        }
        updateRemoveRoleButton();
    }

    function loadRolesForCompany(companyId) {
        if (!companyId) {
            state.companyRoles = [];
            renderRoleChecks();
            return Promise.resolve();
        }
        return api(urlRoles + '?company_id=' + encodeURIComponent(companyId))
            .then(function (_ref) {
                state.companyRoles = _ref.data.roles || [];
                renderRoleChecks();
            });
    }

    function populateFormFields(m) {
        state.membershipId = m.id;
        state.userId = m.user_id;
        state.selectedMembershipId = m.id;
        $('f_user_code').value = m.user_code || '';
        $('f_name').value = m.name || '';
        $('f_email').value = m.email || '';
        $('f_person').value = m.person || personFromName(m.name);
        populateCompanySelect(false);
        $('f_company').value = String(m.company_id);
        syncChrome();
    }

    function selectRowOnly(m) {
        clearError();
        state.selectedMembershipId = m.id;
        syncChrome();
        highlightRow();
    }

    function openNew() {
        clearError();
        state.mainTab = 'new';
        state.formMode = 'new';
        state.selectedMembershipId = null;
        state.membershipId = null;
        state.userId = null;
        state.assignRolesOpen = true;
        state.pendingRoleScopes = {};
        syncChrome();

        $('f_user_code').value = '';
        $('f_name').value = '';
        $('f_email').value = '';
        $('f_person').value = '';
        populateCompanySelect(false);
        $('f_company').value = '';

        $('rolesEditorWrap').style.display = 'block';
        state.companyRoles = [];
        renderRoleChecks();

        applyFormMode();
        $('detailSection').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function openViewTab() {
        if (state.selectedMembershipId === null) {
            showError('Select a user in the list first.');
            return;
        }
        var m = state.memberships.find(function (x) { return x.id === state.selectedMembershipId; });
        if (!m) {
            showError('Selected user is no longer in the list.');
            state.selectedMembershipId = null;
            syncChrome();
            return;
        }
        clearError();
        state.mainTab = 'view';
        state.formMode = 'view';
        state.assignRolesOpen = false;
        syncChrome();

        populateFormFields(m);
        applyFormMode();

        loadRolesForCompany(String(m.company_id)).then(function () {
            applyFormMode();
        });

        $('detailSection').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function openList() {
        state.mainTab = 'list';
        state.formMode = 'hidden';
        syncChrome();

        applyFormMode();
    }

    function highlightRow() {
        var rows = document.querySelectorAll('#usersTableBody tr');
        rows.forEach(function (tr) {
            tr.classList.toggle('selected', Number(tr.dataset.mid) === Number(state.selectedMembershipId));
        });
    }

    function pruneStaleSelection() {
        if (state.selectedMembershipId === null) return;
        var exists = state.memberships.some(function (x) { return x.id === state.selectedMembershipId; });
        if (!exists) state.selectedMembershipId = null;
        syncChrome();
    }

    function openViewForMembership(m) {
        selectRowOnly(m);
        openViewTab();
    }

    function renderTable() {
        var tbody = $('usersTableBody');
        tbody.innerHTML = '';
        var list = state.memberships.slice();

        $('emptyHint').style.display = list.length ? 'none' : 'block';

        list.forEach(function (m) {
            var tr = document.createElement('tr');
            tr.dataset.mid = String(m.id);

            var tdRad = document.createElement('td');
            tdRad.className = 'td-radio';
            var rad = document.createElement('input');
            rad.type = 'radio';
            rad.name = 'membershipPick';
            rad.setAttribute('aria-label', 'Select row');
            if (Number(state.selectedMembershipId) === Number(m.id)) {
                rad.checked = true;
            }
            rad.addEventListener('click', function (e) {
                e.stopPropagation();
                selectRowOnly(m);
            });
            tdRad.appendChild(rad);

            var tdUid = document.createElement('td');
            var uid = (m.user_code || '').trim();
            if (uid) {
                var aUid = document.createElement('a');
                aUid.href = '#';
                aUid.className = 'user-id-link view-inline-link';
                aUid.setAttribute('aria-label', 'View user');
                aUid.textContent = uid;
                aUid.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openViewForMembership(m);
                });
                tdUid.appendChild(aUid);
            } else {
                var sp = document.createElement('span');
                sp.className = 'muted';
                sp.textContent = '-';
                tdUid.appendChild(sp);
            }

            var tdName = document.createElement('td');
            var aName = document.createElement('a');
            aName.href = '#';
            aName.className = 'user-id-link view-inline-link';
            aName.setAttribute('aria-label', 'View user');
            aName.textContent = m.name || '-';
            aName.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                openViewForMembership(m);
            });
            tdName.appendChild(aName);

            var tdEmail = document.createElement('td');
            tdEmail.textContent = m.email || '';

            var tdCo = document.createElement('td');
            tdCo.textContent = m.company_code || '';

            var tdPerson = document.createElement('td');
            tdPerson.textContent = m.person || '';

            tr.appendChild(tdRad);
            tr.appendChild(tdUid);
            tr.appendChild(tdName);
            tr.appendChild(tdEmail);
            tr.appendChild(tdCo);
            tr.appendChild(tdPerson);

            tr.addEventListener('click', function () {
                rad.checked = true;
                selectRowOnly(m);
            });
            tbody.appendChild(tr);
        });
        pruneStaleSelection();
        highlightRow();
    }

    function gatherRoleIds() {
        var ids = [];
        document.querySelectorAll('#rolesChecks input[type="checkbox"]').forEach(function (cb) {
            if (cb.checked) ids.push(Number(cb.value));
        });
        return ids;
    }

    function selectedRolesForNewFlow() {
        var ids = gatherRoleIds();
        return state.companyRoles.filter(function (role) {
            return ids.indexOf(Number(role.id)) !== -1;
        });
    }

    function buildRoleScopesForNew(roleIds) {
        var scopes = [];
        roleIds.forEach(function (roleId) {
            var scope = state.pendingRoleScopes[Number(roleId)];
            if (!scope) return;
            scopes.push({
                role_id: Number(roleId),
                all_organizations: scope.all_organizations ? 1 : 0,
                company_ids: scope.all_organizations ? [] : (scope.company_ids || [])
            });
        });
        return scopes;
    }

    function save() {
        if (state.saving) return;
        clearError();
        var companyId = $('f_company').value;
        if (!companyId) {
            showError('Choose a company.');
            return;
        }

        var selectedRoleIds = gatherRoleIds();
        var payload = {
            company_id: Number(companyId),
            email: $('f_email').value.trim(),
            name: $('f_name').value.trim(),
            user_code: $('f_user_code').value.trim() || null,
            role_ids: selectedRoleIds,
            role_scopes: state.formMode === 'new' ? buildRoleScopesForNew(selectedRoleIds) : []
        };

        if (state.formMode === 'new' && selectedRoleIds.length === 0) {
            showError('Assign at least one role before saving the user.');
            return;
        }

        state.saving = true;
        $('btnSave').disabled = true;

        var promise;
        if (state.formMode === 'new') {
            promise = api(urlMembershipsStore, { method: 'POST', body: JSON.stringify(payload) });
        } else if (state.formMode === 'edit' && state.membershipId) {
            var url = urlMemberships.replace(/\/?$/, '/') + state.membershipId;
            var reqBody = {
                email: payload.email,
                name: payload.name,
                user_code: payload.user_code
            };
            if (state.assignRolesOpen) {
                reqBody.role_ids = payload.role_ids;
            }
            promise = api(url, {
                method: 'PUT',
                body: JSON.stringify(reqBody)
            });
        } else {
            state.saving = false;
            $('btnSave').disabled = false;
            return;
        }

        promise.then(function (_ref2) {
            var res = _ref2.res;
            var data = _ref2.data;
            state.saving = false;
            $('btnSave').disabled = false;

            if (!res.ok) {
                var msg = data.message || firstValidationMessage(data) || ('HTTP ' + res.status);
                showError(msg);
                return;
            }

            return reloadMemberships().then(function () {
                if (data.membership) {
                    state.selectedMembershipId = data.membership.id;
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

    function deleteMembershipById(id) {
        if (!id || !window.confirm('Delete this user membership?')) return;
        var url = urlMemberships.replace(/\/?$/, '/') + id;
        api(url, { method: 'DELETE' }).then(function (ref) {
            if (!ref.res.ok) {
                showError(ref.data.message || 'Could not delete.');
                return;
            }
            state.selectedMembershipId = null;
            reloadMemberships().then(function () {
                openList();
            });
        }).catch(function () {
            showError('Network error.');
        });
    }

    function reloadMemberships() {
        return api(urlMemberships).then(function (_ref3) {
            state.memberships = _ref3.data.memberships || [];
            renderTable();
        });
    }

    $('cmdNew').addEventListener('click', function () {
        openNew();
    });

    $('cmdDelete').addEventListener('click', function () {
        if ($('cmdDelete').disabled || state.selectedMembershipId === null) return;
        deleteMembershipById(state.selectedMembershipId);
    });

    $('cmdImportUsers').addEventListener('click', function () {
        clearError();
        showError('Import users will be wired to backend import endpoint next.');
    });

    $('f_name').addEventListener('input', refreshPersonField);

    $('f_user_code').addEventListener('input', function () {
        if (state.mainTab !== 'list') {
            updateDetailChrome();
        }
    });

    $('f_email').addEventListener('input', function () {
        if (state.mainTab !== 'list') {
            updateDetailChrome();
        }
    });

    $('btnToggleAssignRoles').addEventListener('click', function () {
        if (state.formMode !== 'new' && state.formMode !== 'edit') {
            showError('Open New or Edit mode to change roles.');
            return;
        }
        state.assignRolesOpen = !state.assignRolesOpen;
        var cid = $('f_company').value;
        if (state.assignRolesOpen && cid) {
            loadRolesForCompany(cid).then(function () {
                applyFormMode();
            });
        } else {
            applyFormMode();
        }
    });

    function roleScopesUrl(membershipId) {
        return String(urlRoleScopesTemplate).replace('/0/role-scopes', '/' + membershipId + '/role-scopes');
    }

    function roleScopesUpsertUrl(membershipId) {
        return String(urlRoleScopesUpsertTemplate).replace('/0/role-scopes', '/' + membershipId + '/role-scopes');
    }

    function loadRoleScopes(membershipId) {
        if (!membershipId) return Promise.resolve();
        return api(roleScopesUrl(membershipId)).then(function (ref) {
            var scopes = ref.data.scopes || [];
            state.roleScopes = {};
            scopes.forEach(function (s) {
                state.roleScopes[Number(s.role_id)] = {
                    all_organizations: !!s.all_organizations,
                    company_ids: (s.company_ids || []).map(Number)
                };
            });
        });
    }

    function renderOrgCompaniesChecks(selectedCompanyIds) {
        var wrap = $('orgCompaniesChecks');
        wrap.innerHTML = '';
        var ids = Array.isArray(selectedCompanyIds) ? selectedCompanyIds.map(Number) : [];
        state.companies.forEach(function (c) {
            var row = document.createElement('div');
            row.className = 'org-company-row';
            var cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.value = String(c.id);
            cb.checked = ids.indexOf(Number(c.id)) !== -1;
            row.appendChild(cb);
            var txt = document.createElement('span');
            txt.textContent = (c.code ? (c.code + ' - ') : '') + (c.name || '');
            row.appendChild(txt);
            wrap.appendChild(row);
        });
        if (!state.companies.length) {
            var p = document.createElement('div');
            p.className = 'muted';
            p.textContent = 'No companies found.';
            wrap.appendChild(p);
        }
    }

    function resolveOrgRoleContext() {
        var checkedRoleIds = gatherRoleIds();
        if (!checkedRoleIds.length) {
            return { error: 'Select one role first, then click Assign organizations.' };
        }
        if (checkedRoleIds.length > 1) {
            return { error: 'Select only one role before Assign organizations.' };
        }
        var roleId = Number(checkedRoleIds[0]);
        var role = state.companyRoles.find(function (r) { return Number(r.id) === roleId; });
        var roleName = role ? role.name : ('Role #' + roleId);
        return { roleId: roleId, roleName: roleName };
    }

    function openOrgScope() {
        clearError();
        if (state.formMode !== 'new' && !state.selectedMembershipId) {
            showError('Select a user first.');
            return;
        }
        if (state.formMode !== 'new' && state.formMode !== 'edit') {
            showError('Assign organizations is available only in New or Edit.');
            return;
        }
        if (!state.assignRolesOpen) {
            showError('Click + Assign roles and select one role first.');
            return;
        }

        var roleCtx = resolveOrgRoleContext();
        if (roleCtx.error) {
            showError(roleCtx.error);
            return;
        }
        state.orgRoleContext = { id: roleCtx.roleId, name: roleCtx.roleName };

        state.mainTab = 'orgs';
        syncChrome();
        var roleId = state.orgRoleContext.id;

        var m = state.memberships.find(function (x) { return x.id === state.selectedMembershipId; });
        var roleLabel = state.orgRoleContext.name || '';
        var userLabel = '';
        if (state.formMode === 'new') {
            userLabel = ($('f_user_code').value || '').trim() || ($('f_name').value || '').trim() || 'new user';
        } else if (m) {
            userLabel = (m.user_code || '').trim();
            if (!userLabel && (m.email || '').indexOf('@') !== -1) userLabel = (m.email || '').split('@')[0];
            if (!userLabel) userLabel = (m.name || '').trim();
        }
        var selectedCompanyLabel = '';
        var companySelect = $('f_company');
        if (companySelect && companySelect.selectedOptions && companySelect.selectedOptions.length) {
            selectedCompanyLabel = (companySelect.selectedOptions[0].textContent || '').trim();
        }
        $('org_user_text').value = userLabel || '-';
        $('org_role_text').value = roleLabel || '-';
        $('org_home_company_text').value = selectedCompanyLabel && selectedCompanyLabel !== 'Select company...'
            ? selectedCompanyLabel
            : '-';
        $('orgRecordTitle').textContent = userLabel && roleLabel
            ? ('Organizations for ' + userLabel + ' in role ' + roleLabel)
            : 'Organizations';
        var scopeSource = state.formMode === 'new' ? state.pendingRoleScopes : state.roleScopes;
        var scope = scopeSource[roleId] || { all_organizations: false, company_ids: [] };
        $('org_all').checked = !!scope.all_organizations;
        $('org_specific').checked = !scope.all_organizations;
        renderOrgCompaniesChecks(scope.company_ids);
        $('orgCompaniesBlock').style.display = scope.all_organizations ? 'none' : 'block';
        $('org_all').focus();
    }

    function closeOrgScope() {
        state.orgRoleContext = null;
        if (state.formMode === 'new') {
            state.mainTab = 'new';
        } else if (state.formMode === 'edit') {
            state.mainTab = 'edit';
        } else {
            state.mainTab = 'view';
        }
        syncChrome();
        applyFormMode();
        $('detailSection').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function gatherOrgCompanyIds() {
        var ids = [];
        document.querySelectorAll('#orgCompaniesChecks input[type=\"checkbox\"]').forEach(function (cb) {
            if (cb.checked) ids.push(Number(cb.value));
        });
        return ids;
    }

    function saveOrgScope() {
        var roleId = state.orgRoleContext ? Number(state.orgRoleContext.id) : 0;
        if (!roleId) {
            showError('Assign a role first.');
            return;
        }
        var allOrgs = $('org_all').checked;
        var companyIds = allOrgs ? [] : gatherOrgCompanyIds();

        if (state.formMode === 'new') {
            state.pendingRoleScopes[roleId] = {
                all_organizations: !!allOrgs,
                company_ids: companyIds
            };
            closeOrgScope();
            return;
        }

        if (!state.selectedMembershipId) return;

        var payload = {
            role_id: roleId,
            all_organizations: allOrgs ? 1 : 0,
            company_ids: companyIds
        };
        api(roleScopesUpsertUrl(state.selectedMembershipId), {
            method: 'PUT',
            body: JSON.stringify(payload)
        }).then(function (ref) {
            if (!ref.res.ok) {
                showError(ref.data.message || firstValidationMessage(ref.data) || ('HTTP ' + ref.res.status));
                return;
            }
            return loadRoleScopes(state.selectedMembershipId).then(function () {
                closeOrgScope();
            });
        }).catch(function () {
            showError('Network error.');
        });
    }

    $('btnAssignOrganizations').addEventListener('click', function () {
        loadRoleScopes(state.selectedMembershipId).then(function () {
            openOrgScope();
        });
    });

    ['org_all', 'org_specific'].forEach(function (id) {
        $(id).addEventListener('change', function () {
            var allOrgs = $('org_all').checked;
            $('orgCompaniesBlock').style.display = allOrgs ? 'none' : 'block';
        });
    });

    $('orgClose').addEventListener('click', function () { closeOrgScope(); });

    $('orgSave').addEventListener('click', function () {
        saveOrgScope();
    });

    $('f_company').addEventListener('change', function () {
        if (state.formMode !== 'new' && state.formMode !== 'edit') return;
        loadRolesForCompany($('f_company').value);
    });

    $('btnEdit').addEventListener('click', function () {
        clearError();
        state.formMode = 'edit';
        state.assignRolesOpen = true;
        loadRolesForCompany($('f_company').value).then(function () {
            $('rolesEditorWrap').style.display = 'block';
            renderRoleChecks();
            applyFormMode();
        });
    });

    $('btnRemoveRole').addEventListener('click', function () {
        if (!(state.formMode === 'new' || state.formMode === 'edit')) return;
        if (!state.assignRolesOpen) return;

        var changed = false;
        document.querySelectorAll('#rolesChecks input[type="checkbox"]').forEach(function (cb) {
            if (cb.checked) {
                cb.checked = false;
                changed = true;
            }
        });

        if (!changed) {
            showError('Select role(s) to remove.');
        }
        updateRemoveRoleButton();
    });

    $('btnCancelDetail').addEventListener('click', function () {
        clearError();
        openList();
    });

    $('btnSave').addEventListener('click', save);

    syncChrome();
    Promise.all([
        api(urlCompanies),
        api(urlMemberships)
    ]).then(function (results) {
        state.companies = results[0].data.companies || [];
        state.memberships = results[1].data.memberships || [];
        populateCompanySelect(false);
        renderTable();
    }).catch(function () {
        showError('Could not load users or companies.');
    });
})();
    </script>
</body>
</html>
