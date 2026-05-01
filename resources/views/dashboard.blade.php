<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            background: #f3f2f1;
            color: #323130;
            min-height: 100vh;
            display: flex;
        }
        .sidebar {
            width: 280px;
            min-height: 100vh;
            background: #fff;
            border-right: 1px solid #edebe9;
            padding: 16px 0;
            flex-shrink: 0;
        }
        .sidebar-brand {
            padding: 8px 16px 20px;
            border-bottom: 1px solid #edebe9;
            margin-bottom: 8px;
        }
        .sidebar-brand h1 {
            margin: 0;
            font-size: 1.15rem;
            color: #201f1e;
        }
        .nav-group { margin-bottom: 4px; }
        .nav-group-header {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            margin: 0 8px;
            border: none;
            border-radius: 2px;
            background: #deecf9;
            color: #005a9e;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-align: left;
            font-family: inherit;
        }
        .nav-group-header:hover { background: #c7e0f4; }
        .nav-group-header .nav-icon { flex-shrink: 0; opacity: 0.85; }
        .nav-group-header .nav-label { flex: 1; }
        .nav-group-header .chevron {
            font-size: 10px;
            transition: transform 0.2s;
        }
        .nav-group-header[aria-expanded="false"] .chevron {
            transform: rotate(180deg);
        }
        .nav-group-body {
            padding: 4px 0 8px 8px;
        }
        .nav-group-body[hidden] { display: none; }
        .nav-link {
            display: block;
            padding: 8px 14px 8px 36px;
            margin: 2px 8px;
            border-radius: 2px;
            color: #323130;
            text-decoration: none;
            font-size: 14px;
        }
        .nav-link:hover { background: #f3f2f1; color: #201f1e; }
        .nav-link.active {
            background: #deecf9;
            color: #005a9e;
            font-weight: 500;
        }
        .nav-subgroup { margin-top: 4px; }
        .nav-subgroup-header {
            width: calc(100% - 16px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px 8px 28px;
            margin: 0 8px;
            border: none;
            border-radius: 2px;
            background: #f3f2f1;
            color: #605e5c;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-align: left;
            font-family: inherit;
        }
        .nav-subgroup-header:hover { background: #edebe9; }
        .nav-subgroup-header .chevron-sm { font-size: 9px; transition: transform 0.2s; }
        .nav-subgroup-header[aria-expanded="false"] .chevron-sm { transform: rotate(180deg); }
        .nav-link.nested { padding-left: 44px; font-size: 13px; }
        .nav-subgroup-body[hidden] { display: none; }
        .main {
            flex: 1;
            padding: 24px 32px;
            overflow: auto;
        }
        .main-header {
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }
        .main-header h2 {
            margin: 0 0 6px;
            font-size: 1.5rem;
            color: #201f1e;
        }
        .info-card {
            max-width: 480px;
            padding: 20px;
            background: #fff;
            border-radius: 2px;
            box-shadow: none;
            border: 1px solid #edebe9;
        }
        .info-card h3 {
            margin: 0 0 12px;
            font-size: 1rem;
            color: #201f1e;
        }
        .info-card p { margin: 8px 0; font-size: 14px; color: #605e5c; }
        .logout-row { padding: 16px; margin-top: 8px; }
        .logout-row form { margin: 0; }
        .btn-logout {
            background: transparent;
            color: #605e5c;
            border: 1px solid #8a8886;
            padding: 8px 14px;
            border-radius: 2px;
            cursor: pointer;
            font-size: 13px;
            font-family: inherit;
        }
        .btn-logout:hover { background: #f3f2f1; color: #201f1e; }
    </style>
</head>
<body>
    @include('partials.global-company-selector')
    @php
        $companyQuery = $currentCompanyCode ? ['company' => strtoupper((string) $currentCompanyCode)] : [];
    @endphp
    <aside class="sidebar" aria-label="Main navigation">
        <div class="sidebar-brand">
            <h1>MENU</h1>
        </div>

        <nav>
            <div class="nav-group">
                <button type="button" class="nav-group-header" data-nav-target="nav-masters" aria-expanded="true">
                    <span class="nav-icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                            <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                        </svg>
                    </span>
                    <span class="nav-label">Masters</span>
                    <span class="chevron" aria-hidden="true">▲</span>
                </button>
                <div class="nav-group-body" id="nav-masters">
                    <a class="nav-link {{ request()->routeIs('masters.company.index') ? 'active' : '' }}" href="{{ route('masters.company.index', $companyQuery) }}">Companies</a>
                    <a class="nav-link {{ request()->routeIs('masters.categories.index') ? 'active' : '' }}" href="{{ route('masters.categories.index', $companyQuery) }}">Categories</a>
                    <a class="nav-link {{ request()->routeIs('masters.items.index') ? 'active' : '' }}" href="{{ route('masters.items.index', $companyQuery) }}">Items</a>
                    <a class="nav-link {{ request()->routeIs('masters.sizes.index') ? 'active' : '' }}" href="{{ route('masters.sizes.index', $companyQuery) }}">Sizes</a>
                    <a class="nav-link {{ request()->routeIs('masters.colors.index') ? 'active' : '' }}" href="{{ route('masters.colors.index', $companyQuery) }}">Colors</a>
                    <a class="nav-link {{ request()->routeIs('masters.styles.index') ? 'active' : '' }}" href="{{ route('masters.styles.index', $companyQuery) }}">Styles</a>
                    <a class="nav-link {{ request()->routeIs('masters.locations.index') ? 'active' : '' }}" href="{{ route('masters.locations.index', $companyQuery) }}">Locations</a>
                    <a class="nav-link {{ request()->routeIs('masters.site.index') ? 'active' : '' }}" href="{{ route('masters.site.index', $companyQuery) }}">Sites</a>
                    <a class="nav-link {{ request()->routeIs('masters.warehouses.index') ? 'active' : '' }}" href="{{ route('masters.warehouses.index', $companyQuery) }}">Warehouses</a>
                    <a class="nav-link {{ request()->routeIs('masters.currencies.index') ? 'active' : '' }}" href="{{ route('masters.currencies.index', $companyQuery) }}">Currencies</a>
                    <a class="nav-link {{ request()->routeIs('masters.units.index') ? 'active' : '' }}" href="{{ route('masters.units.index', $companyQuery) }}">Units</a>
                    <a class="nav-link {{ request()->routeIs('masters.project.index') ? 'active' : '' }}" href="{{ route('masters.project.index', $companyQuery) }}">Projects</a>
                    <a class="nav-link {{ request()->routeIs('masters.batches.index') ? 'active' : '' }}" href="{{ route('masters.batches.index', $companyQuery) }}">Batches</a>
                    <a class="nav-link {{ request()->routeIs('masters.sales-tax-groups.index') ? 'active' : '' }}" href="{{ route('masters.sales-tax-groups.index', $companyQuery) }}">Sales Tax Groups</a>
                    <a class="nav-link {{ request()->routeIs('masters.item-sales-tax-groups.index') ? 'active' : '' }}" href="{{ route('masters.item-sales-tax-groups.index', $companyQuery) }}">Item Sales Tax Groups</a>
                    <a class="nav-link {{ request()->routeIs('masters.department-managers.index') ? 'active' : '' }}" href="{{ route('masters.department-managers.index', $companyQuery) }}">Department Managers</a>
                </div>
            </div>

            <div class="nav-group">
                <button type="button" class="nav-group-header" data-nav-target="nav-modules" aria-expanded="true">
                    <span class="nav-icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
                        </svg>
                    </span>
                    <span class="nav-label">Modules</span>
                    <span class="chevron" aria-hidden="true">▲</span>
                </button>
                <div class="nav-group-body" id="nav-modules">
                    <div class="nav-subgroup">
                        <button type="button" class="nav-subgroup-header" data-nav-target="nav-pm" aria-expanded="true">
                            Project Management
                            <span class="chevron-sm" aria-hidden="true">▲</span>
                        </button>
                        <div class="nav-subgroup-body" id="nav-pm">
                            <a class="nav-link nested {{ request()->routeIs('modules.project-management.item-issue') ? 'active' : '' }}" href="{{ route('modules.project-management.item-issue', $companyQuery) }}">Item Issue</a>
                        </div>
                    </div>
                    <div class="nav-subgroup">
                        <button type="button" class="nav-subgroup-header" data-nav-target="nav-procurement" aria-expanded="true">
                            Procurement
                            <span class="chevron-sm" aria-hidden="true">▲</span>
                        </button>
                        <div class="nav-subgroup-body" id="nav-procurement">
                            <a class="nav-link nested {{ request()->routeIs('quotations.*') ? 'active' : '' }}" href="{{ route('quotations.index', $companyQuery) }}">Quotation</a>
                            <a class="nav-link nested {{ request()->routeIs('modules.procurement.purch-req*') ? 'active' : '' }}" href="{{ route('modules.procurement.purch-req', $companyQuery) }}">Purchase Requisition</a>
                            <a class="nav-link nested {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}" href="{{ route('purchase-orders.index', $companyQuery) }}">Purchase Order</a>
                            <a class="nav-link nested {{ request()->routeIs('modules.procurement.grn*') ? 'active' : '' }}" href="{{ route('modules.procurement.grn', $companyQuery) }}">Goods Receive Note</a>
                            <a class="nav-link nested {{ request()->routeIs('inventory.*') ? 'active' : '' }}" href="{{ route('inventory.index', $companyQuery) }}">Inventory</a>
                            <a class="nav-link nested {{ request()->routeIs('vendors.*') ? 'active' : '' }}" href="{{ route('vendors.index', $companyQuery) }}">Vendors</a>
                            <a class="nav-link nested {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index', $companyQuery) }}">Customers</a>
                            <a class="nav-link nested {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index', $companyQuery) }}">Reports</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nav-group">
                <button type="button" class="nav-group-header" data-nav-target="nav-settings" aria-expanded="true">
                    <span class="nav-icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.7 1.7 0 0 0-1.87-.34 1.7 1.7 0 0 0-1 1.54V21a2 2 0 1 1-4 0v-.09a1.7 1.7 0 0 0-1-1.54 1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.7 1.7 0 0 0 .34-1.87 1.7 1.7 0 0 0-1.54-1H3a2 2 0 1 1 0-4h.09a1.7 1.7 0 0 0 1.54-1 1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.7 1.7 0 0 0 1.87.34H9a1.7 1.7 0 0 0 1-1.54V3a2 2 0 1 1 4 0v.09a1.7 1.7 0 0 0 1 1.54 1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.7 1.7 0 0 0-.34 1.87v.09a1.7 1.7 0 0 0 1.54 1H21a2 2 0 1 1 0 4h-.09a1.7 1.7 0 0 0-1.54 1z"/>
                        </svg>
                    </span>
                    <span class="nav-label">Settings</span>
                    <span class="chevron" aria-hidden="true">▲</span>
                </button>
                <div class="nav-group-body" id="nav-settings">
                    <a class="nav-link {{ request()->routeIs('settings.index') ? 'active' : '' }}" href="{{ route('settings.index', $companyQuery) }}">API Configuration</a>
                    <a class="nav-link {{ request()->routeIs('settings.roles-permissions') ? 'active' : '' }}" href="{{ route('settings.roles-permissions', $companyQuery) }}">Roles & Permissions</a>
                </div>
            </div>
        </nav>

        <div class="logout-row">
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">Log out</button>
            </form>
        </div>
    </aside>

    <main class="main">
        <div class="main-header">
            <h2>Dashboard</h2>
        </div>

        <div class="info-card">
            <h3>User Details</h3>
            <p><strong>Name:</strong> {{ auth()->user()->name }}</p>
            <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
            <p><strong>User ID:</strong> {{ auth()->user()->id }}</p>
        </div>
    </main>

    <script>
        document.querySelectorAll('.nav-group-header[data-nav-target]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.getAttribute('data-nav-target');
                var body = document.getElementById(id);
                if (!body) return;
                var open = btn.getAttribute('aria-expanded') === 'true';
                btn.setAttribute('aria-expanded', open ? 'false' : 'true');
                body.hidden = open;
            });
        });
        document.querySelectorAll('.nav-subgroup-header[data-nav-target]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.getAttribute('data-nav-target');
                var body = document.getElementById(id);
                if (!body) return;
                var open = btn.getAttribute('aria-expanded') === 'true';
                btn.setAttribute('aria-expanded', open ? 'false' : 'true');
                body.hidden = open;
            });
        });

    </script>
</body>
</html>
