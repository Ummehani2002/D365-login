<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Settings - Menu Match</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('settings.rbac.partials.styles')
    @include('settings.rbac.partials.d365-rbac-page-styles')
    <style>
        .menu-key {
            font-family: Consolas, "Courier New", monospace;
            font-size: 12px;
            color: #605e5c;
        }
        .status-pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            border: 1px solid #edebe9;
            color: #605e5c;
            background: #f3f2f1;
        }
        .status-pill.dirty {
            border-color: #f3d4a0;
            color: #8a5a00;
            background: #fff4ce;
        }
        .perm-select {
            min-width: 260px;
        }
        .route-name {
            font-family: Consolas, "Courier New", monospace;
            font-size: 12px;
            color: #323130;
        }
        .unmapped-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid #edebe9;
            background: #f3f2f1;
            font-size: 12px;
            color: #323130;
        }
        .mm-modal {
            position: fixed;
            inset: 0;
            z-index: 3000;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.25);
            padding: 20px;
        }
        .mm-modal-card {
            width: min(640px, 100%);
            background: #fff;
            border: 1px solid #d2d0ce;
            border-radius: 4px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        .mm-modal-head {
            padding: 14px 16px;
            border-bottom: 1px solid #edebe9;
            font-size: 18px;
            font-weight: 600;
            color: #201f1e;
        }
        .mm-modal-body {
            padding: 16px;
            display: grid;
            gap: 12px;
        }
        .mm-field {
            display: grid;
            gap: 6px;
        }
        .mm-field label {
            font-size: 13px;
            color: #323130;
            font-weight: 600;
        }
        .mm-readonly {
            background: #f3f2f1;
            color: #605e5c;
        }
        .mm-modal-foot {
            padding: 12px 16px;
            border-top: 1px solid #edebe9;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }
    </style>
</head>
<body
    data-url-mappings="{{ route('settings.menu-match.api.mappings.index') }}"
    data-url-mappings-save="{{ route('settings.menu-match.api.mappings.update') }}"
    data-url-available-menus="{{ route('settings.menu-match.api.available-menus.index') }}"
    data-url-assign="{{ route('settings.menu-match.api.assign.store') }}"
>
    @include('partials.global-company-selector')
    @include('settings.rbac.partials.sidebar')

    <main class="main">
        <div class="page-bar">
            <div>
                <h1 class="page-title">Settings</h1>
                <p class="rbac-page-intro">Match module menu items to permissions from the database. A user sees a module only when their role includes the mapped permission.</p>
            </div>
        </div>

        <div id="flashError" class="flash-error"></div>

        <div id="listChrome">
            <div class="d365-title-row">
                <h1 class="d365-page-h1">Menu match</h1>
            </div>
            <div class="d365-cmd-bar" id="toolbarList">
                <button type="button" class="d365-cmd d365-cmd-primary" id="cmdSave" disabled>Save mapping</button>
                <button type="button" class="d365-cmd d365-cmd-primary" id="cmdOpenAssign">Assign menu item to permission</button>
                <span class="d365-cmd-sep" aria-hidden="true"></span>
                <button type="button" class="d365-cmd" id="cmdReload">Reload</button>
                <span id="unmappedPill" class="unmapped-pill" style="margin-left:auto;">Unmapped menus: 0</span>
            </div>
            <div class="d365-filter-row" id="filterWrap">
                <label for="filterInput">Filter</label>
                <input type="search" id="filterInput" placeholder="" autocomplete="off" aria-label="Filter menu items">
            </div>

            <section id="listSection">
                <div class="table-card">
                    <table class="users-grid" aria-label="Menu permission mapping list">
                        <thead>
                            <tr>
                                <th>Menu item</th>
                                <th>Menu key</th>
                                <th>Route name</th>
                                <th>Permission</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="matchTableBody"></tbody>
                    </table>
                    <div id="emptyHint" class="empty-hint" style="display:none;">No module menu items configured yet.</div>
                </div>
            </section>
        </div>

        <div id="assignModal" class="mm-modal" role="dialog" aria-modal="true" aria-labelledby="assignModalTitle">
            <div class="mm-modal-card">
                <div class="mm-modal-head" id="assignModalTitle">Assign menu item to permission</div>
                <div class="mm-modal-body">
                    <div class="mm-field">
                        <label for="assignMenuItem">Menu item</label>
                        <select id="assignMenuItem"></select>
                    </div>
                    <div class="mm-field">
                        <label for="assignMenuKey">Menu key</label>
                        <input type="text" id="assignMenuKey" class="mm-readonly" readonly>
                    </div>
                    <div class="mm-field">
                        <label for="assignRouteName">Route name</label>
                        <input type="text" id="assignRouteName" class="mm-readonly" readonly>
                    </div>
                    <div class="mm-field">
                        <label for="assignPermission">Required permission</label>
                        <select id="assignPermission"></select>
                    </div>
                </div>
                <div class="mm-modal-foot">
                    <button type="button" class="d365-cmd" id="assignCancel">Cancel</button>
                    <button type="button" class="d365-cmd d365-cmd-primary" id="assignSave">Save mapping</button>
                </div>
            </div>
        </div>
    </main>

    <script>
(function () {
    var body = document.body;
    var urlMappings = body.dataset.urlMappings;
    var urlMappingsSave = body.dataset.urlMappingsSave;
    var urlAvailableMenus = body.dataset.urlAvailableMenus;
    var urlAssign = body.dataset.urlAssign;
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    var state = {
        menuItems: [],
        availableMenus: [],
        permissions: [],
        filter: '',
        dirty: {},
        saving: false,
        assigning: false
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

    function hasDirtyRows() {
        return Object.keys(state.dirty).length > 0;
    }

    function updateSaveButton() {
        $('cmdSave').disabled = state.saving || !hasDirtyRows();
    }

    function updateUnmappedPill() {
        var unmapped = state.availableMenus.filter(function (menu) { return !menu.mapped; }).length;
        $('unmappedPill').textContent = 'Unmapped menus: ' + unmapped;
    }

    function renderAssignMenuSelect() {
        var select = $('assignMenuItem');
        select.innerHTML = '';

        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select menu item';
        select.appendChild(placeholder);

        state.availableMenus.forEach(function (menu) {
            var option = document.createElement('option');
            option.value = menu.key;
            option.textContent = menu.label + (menu.mapped ? ' [Already mapped]' : ' [Unmapped]');
            select.appendChild(option);
        });
    }

    function renderAssignPermissionSelect(permissionId) {
        var select = $('assignPermission');
        select.innerHTML = '';

        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select permission';
        select.appendChild(placeholder);

        state.permissions.forEach(function (permission) {
            var option = document.createElement('option');
            option.value = String(permission.id);
            option.textContent = (permission.name || '') + ' (' + (permission.slug || '-') + ')';
            select.appendChild(option);
        });

        select.value = permissionId === null || permissionId === undefined ? '' : String(permissionId);
    }

    function setAssignMenuDetails(menuKey) {
        var selected = state.availableMenus.find(function (menu) { return menu.key === menuKey; });
        if (!selected) {
            $('assignMenuKey').value = '';
            $('assignRouteName').value = '';
            renderAssignPermissionSelect(null);
            return;
        }

        $('assignMenuKey').value = selected.key || '';
        $('assignRouteName').value = selected.route_name || '';
        renderAssignPermissionSelect(selected.permission_id);
    }

    function openAssignModal() {
        clearError();
        renderAssignMenuSelect();
        setAssignMenuDetails('');
        $('assignModal').style.display = 'flex';
        $('assignMenuItem').focus();
    }

    function closeAssignModal() {
        $('assignModal').style.display = 'none';
    }

    function currentPermissionId(item) {
        if (Object.prototype.hasOwnProperty.call(state.dirty, String(item.id))) {
            return state.dirty[String(item.id)];
        }
        return item.permission_id === null ? null : Number(item.permission_id);
    }

    function permissionLabel(permissionId) {
        if (permissionId === null) return '';
        var match = state.permissions.find(function (p) { return Number(p.id) === Number(permissionId); });
        if (!match) return '';
        return (match.name || '') + ' (' + (match.slug || '-') + ')';
    }

    function isRowDirty(item) {
        var original = item.permission_id === null ? null : Number(item.permission_id);
        return currentPermissionId(item) !== original;
    }

    function renderTable() {
        var tbody = $('matchTableBody');
        tbody.innerHTML = '';

        var q = state.filter.trim().toLowerCase();
        var list = state.menuItems.filter(function (item) {
            if (!q) return true;
            var currentPermText = permissionLabel(currentPermissionId(item));
            var blob = [
                item.menu_label || '',
                item.menu_key || '',
                item.route_name || '',
                currentPermText
            ].join(' ').toLowerCase();
            return blob.indexOf(q) !== -1;
        });

        $('emptyHint').style.display = list.length ? 'none' : 'block';

        list.forEach(function (item) {
            var tr = document.createElement('tr');

            var tdLabel = document.createElement('td');
            tdLabel.textContent = item.menu_label || '';

            var tdKey = document.createElement('td');
            var key = document.createElement('span');
            key.className = 'menu-key';
            key.textContent = item.menu_key || '';
            tdKey.appendChild(key);

            var tdRoute = document.createElement('td');
            if (item.route_name) {
                var route = document.createElement('span');
                route.className = 'route-name';
                route.textContent = item.route_name;
                tdRoute.appendChild(route);
            } else {
                tdRoute.innerHTML = '<span class="muted">-</span>';
            }

            var tdPerm = document.createElement('td');
            var sel = document.createElement('select');
            sel.className = 'perm-select';
            var empty = document.createElement('option');
            empty.value = '';
            empty.textContent = 'No permission selected';
            sel.appendChild(empty);
            state.permissions.forEach(function (p) {
                var opt = document.createElement('option');
                opt.value = String(p.id);
                opt.textContent = (p.name || '') + ' (' + (p.slug || '-') + ')';
                sel.appendChild(opt);
            });
            var activePermissionId = currentPermissionId(item);
            sel.value = activePermissionId === null ? '' : String(activePermissionId);
            sel.addEventListener('change', function () {
                var nextValue = sel.value ? Number(sel.value) : null;
                var original = item.permission_id === null ? null : Number(item.permission_id);
                if (nextValue === original) {
                    delete state.dirty[String(item.id)];
                } else {
                    state.dirty[String(item.id)] = nextValue;
                }
                updateSaveButton();
                renderTable();
            });
            tdPerm.appendChild(sel);

            var tdStatus = document.createElement('td');
            var pill = document.createElement('span');
            pill.className = 'status-pill' + (isRowDirty(item) ? ' dirty' : '');
            pill.textContent = isRowDirty(item) ? 'Unsaved' : 'Saved';
            tdStatus.appendChild(pill);

            tr.appendChild(tdLabel);
            tr.appendChild(tdKey);
            tr.appendChild(tdRoute);
            tr.appendChild(tdPerm);
            tr.appendChild(tdStatus);
            tbody.appendChild(tr);
        });
    }

    function reloadData() {
        return Promise.all([api(urlMappings), api(urlAvailableMenus)]).then(function (results) {
            var mappingsRef = results[0];
            var menusRef = results[1];

            if (!mappingsRef.res.ok) {
                showError(mappingsRef.data.message || ('HTTP ' + mappingsRef.res.status));
                return;
            }

            if (!menusRef.res.ok) {
                showError(menusRef.data.message || ('HTTP ' + menusRef.res.status));
                return;
            }

            state.menuItems = mappingsRef.data.menu_items || [];
            state.permissions = mappingsRef.data.permissions || [];
            state.availableMenus = menusRef.data.menus || mappingsRef.data.available_menus || [];
            state.dirty = {};
            updateSaveButton();
            updateUnmappedPill();
            renderTable();
        });
    }

    function saveMappings() {
        if (state.saving || !hasDirtyRows()) return;
        clearError();

        var payload = {
            mappings: Object.keys(state.dirty).map(function (id) {
                return {
                    id: Number(id),
                    permission_id: state.dirty[id]
                };
            })
        };

        state.saving = true;
        updateSaveButton();

        api(urlMappingsSave, {
            method: 'PUT',
            body: JSON.stringify(payload)
        }).then(function (ref) {
            state.saving = false;
            updateSaveButton();
            if (!ref.res.ok) {
                var msg = ref.data.message || firstValidationMessage(ref.data) || ('HTTP ' + ref.res.status);
                showError(msg);
                return;
            }
            return reloadData();
        }).catch(function () {
            state.saving = false;
            updateSaveButton();
            showError('Network error.');
        });
    }

    function saveAssignMapping() {
        if (state.assigning) return;
        clearError();

        var menuKey = ($('assignMenuItem').value || '').trim();
        var permissionValue = ($('assignPermission').value || '').trim();

        if (!menuKey) {
            showError('Select a menu item.');
            return;
        }
        if (!permissionValue) {
            showError('Select a permission.');
            return;
        }

        state.assigning = true;
        $('assignSave').disabled = true;

        api(urlAssign, {
            method: 'POST',
            body: JSON.stringify({
                menu_key: menuKey,
                permission_id: Number(permissionValue)
            })
        }).then(function (ref) {
            state.assigning = false;
            $('assignSave').disabled = false;

            if (!ref.res.ok) {
                var msg = ref.data.message || firstValidationMessage(ref.data) || ('HTTP ' + ref.res.status);
                showError(msg);
                return;
            }

            closeAssignModal();
            return reloadData();
        }).catch(function () {
            state.assigning = false;
            $('assignSave').disabled = false;
            showError('Network error.');
        });
    }

    $('filterInput').addEventListener('input', function (e) {
        state.filter = e.target.value || '';
        renderTable();
    });

    $('cmdReload').addEventListener('click', function () {
        clearError();
        reloadData().catch(function () { showError('Could not reload mappings.'); });
    });

    $('cmdSave').addEventListener('click', function () {
        saveMappings();
    });

    $('cmdOpenAssign').addEventListener('click', function () {
        openAssignModal();
    });

    $('assignMenuItem').addEventListener('change', function (e) {
        setAssignMenuDetails((e.target.value || '').trim());
    });

    $('assignCancel').addEventListener('click', function () {
        closeAssignModal();
    });

    $('assignSave').addEventListener('click', function () {
        saveAssignMapping();
    });

    $('assignModal').addEventListener('click', function (e) {
        if (e.target && e.target.id === 'assignModal') {
            closeAssignModal();
        }
    });

    reloadData().catch(function () { showError('Could not load menu mappings.'); });
})();
    </script>
</body>
</html>
