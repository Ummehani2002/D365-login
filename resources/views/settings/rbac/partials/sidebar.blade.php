@php
    $activeSection = 'dashboard';
    if (request()->routeIs('masters.*')) {
        $activeSection = 'masters';
    } elseif (request()->routeIs('modules.*')) {
        $activeSection = 'modules';
    } elseif (request()->routeIs('settings.*')) {
        $activeSection = 'settings';
    }

    $mastersLinks = [
        ['label' => 'Companies', 'route' => 'masters.company.index', 'pattern' => 'masters.company.index'],
        ['label' => 'Categories', 'route' => 'masters.categories.index', 'pattern' => 'masters.categories.index'],
        ['label' => 'Items', 'route' => 'masters.items.index', 'pattern' => 'masters.items.index'],
        ['label' => 'Sizes', 'route' => 'masters.sizes.index', 'pattern' => 'masters.sizes.index'],
        ['label' => 'Colors', 'route' => 'masters.colors.index', 'pattern' => 'masters.colors.index'],
        ['label' => 'Styles', 'route' => 'masters.styles.index', 'pattern' => 'masters.styles.index'],
        ['label' => 'Locations', 'route' => 'masters.locations.index', 'pattern' => 'masters.locations.index'],
        ['label' => 'Sites', 'route' => 'masters.site.index', 'pattern' => 'masters.site.index'],
        ['label' => 'Warehouses', 'route' => 'masters.warehouses.index', 'pattern' => 'masters.warehouses.index'],
        ['label' => 'Currencies', 'route' => 'masters.currencies.index', 'pattern' => 'masters.currencies.index'],
        ['label' => 'Units', 'route' => 'masters.units.index', 'pattern' => 'masters.units.index'],
        ['label' => 'Projects', 'route' => 'masters.project.index', 'pattern' => 'masters.project.index'],
        ['label' => 'Pools', 'route' => 'masters.pools.index', 'pattern' => 'masters.pools.index'],
        ['label' => 'FD Locations', 'route' => 'masters.fd-locations.index', 'pattern' => 'masters.fd-locations.index'],
        ['label' => 'Budget Resource Codes', 'route' => 'masters.budget-resource-codes.index', 'pattern' => 'masters.budget-resource-codes.index'],
        ['label' => 'Batches', 'route' => 'masters.batches.index', 'pattern' => 'masters.batches.index'],
        ['label' => 'Sales Tax Groups', 'route' => 'masters.sales-tax-groups.index', 'pattern' => 'masters.sales-tax-groups.index'],
        ['label' => 'Item Sales Tax Groups', 'route' => 'masters.item-sales-tax-groups.index', 'pattern' => 'masters.item-sales-tax-groups.index'],
        ['label' => 'Department Managers', 'route' => 'masters.department-managers.index', 'pattern' => 'masters.department-managers.index'],
    ];

    $showProjectManagement = ($authIsSuperAdmin ?? false) || ($canItemIssue ?? false);
    $showProcurement = ($authIsSuperAdmin ?? false) || ($canPr ?? false) || ($canGrn ?? false);
@endphp
<aside class="sidebar" id="appSidebar" data-start-collapsed="{{ request()->routeIs('dashboard') ? '1' : '0' }}">
    <div class="sidebar-rail" aria-label="Navigation rail">
        <button type="button" class="rail-toggle rail-menu-toggle" data-action="toggle-panel" title="Toggle menu" aria-label="Toggle menu">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 6h18"></path>
                <path d="M3 12h18"></path>
                <path d="M3 18h18"></path>
            </svg>
        </button>
        <button type="button" class="rail-toggle {{ $activeSection === 'dashboard' ? 'active' : '' }}" data-target="dashboard" title="Dashboard" aria-label="Dashboard">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
        </button>
        @if($authCanAccessMasters ?? false)
        <button type="button" class="rail-toggle {{ $activeSection === 'masters' ? 'active' : '' }}" data-target="masters" title="Masters" aria-label="Masters">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        </button>
        @endif
        <button type="button" class="rail-toggle {{ $activeSection === 'modules' ? 'active' : '' }}" data-target="modules" title="Modules" aria-label="Modules">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
        </button>
        @if($authCanAccessMasters ?? false)
        <button type="button" class="rail-toggle {{ $activeSection === 'settings' ? 'active' : '' }}" data-target="settings" title="Settings" aria-label="Settings">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.7 1.7 0 0 0-1.87-.34 1.7 1.7 0 0 0-1 1.54V21a2 2 0 1 1-4 0v-.09a1.7 1.7 0 0 0-1-1.54 1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.7 1.7 0 0 0 .34-1.87 1.7 1.7 0 0 0-1.54-1H3a2 2 0 1 1 0-4h.09a1.7 1.7 0 0 0 1.54-1 1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.7 1.7 0 0 0 1.87.34H9a1.7 1.7 0 0 0 1-1.54V3a2 2 0 1 1 4 0v.09a1.7 1.7 0 0 0 1 1.54 1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.7 1.7 0 0 0-.34 1.87v.09a1.7 1.7 0 0 0 1.54 1H21a2 2 0 1 1 0 4h-.09a1.7 1.7 0 0 0-1.54 1z"/></svg>
        </button>
        @endif
        <div class="rail-spacer"></div>
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="rail-toggle" title="Log out" aria-label="Log out">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
            </button>
        </form>
    </div>

    <div class="sidebar-panel">
        <div class="panel-card">
            <div class="sidebar-brand">TI Web App</div>
            <nav>
                <section class="sidebar-section {{ $activeSection === 'dashboard' ? 'active' : '' }}" data-section="dashboard">
                    <div class="nav-section-label">Dashboard</div>
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard', $companyQuery ?? []) }}">Home</a>
                </section>

                @if($authCanAccessMasters ?? false)
                <section class="sidebar-section {{ $activeSection === 'masters' ? 'active' : '' }}" data-section="masters">
                    <div class="nav-section-label">Masters</div>
                    <div class="nav-subgroup">
                        <button type="button" class="nav-subgroup-header" data-nav-target="masters-all" aria-expanded="false">
                            Masters
                            <span class="chevron-sm" aria-hidden="true">▲</span>
                        </button>
                        <div class="nav-subgroup-body" id="masters-all" hidden>
                            @foreach($mastersLinks as $item)
                                @if(\Illuminate\Support\Facades\Route::has($item['route']))
                                    <a
                                        class="nav-link nested {{ request()->routeIs($item['pattern']) ? 'active' : '' }}"
                                        href="{{ route($item['route'], $companyQuery ?? []) }}"
                                    >{{ $item['label'] }}</a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </section>
                @endif

                <section class="sidebar-section {{ $activeSection === 'modules' ? 'active' : '' }}" data-section="modules">
                    <div class="nav-section-label">Modules</div>
                    @if($showProjectManagement)
                        <div class="nav-subgroup">
                            <button type="button" class="nav-subgroup-header" data-nav-target="modules-project-management" aria-expanded="false">
                                Project Management
                                <span class="chevron-sm" aria-hidden="true">▲</span>
                            </button>
                            <div class="nav-subgroup-body" id="modules-project-management" hidden>
                                @if(($authIsSuperAdmin ?? false) || ($canItemIssue ?? false))
                                    <a
                                        class="nav-link nested {{ request()->routeIs('modules.project-management.item-issue*') ? 'active' : '' }}"
                                        href="{{ route('modules.project-management.item-issue', $companyQuery ?? []) }}"
                                    >Item Issue</a>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($showProcurement)
                        <div class="nav-subgroup">
                            <button type="button" class="nav-subgroup-header" data-nav-target="modules-procurement" aria-expanded="false">
                                Procurement &amp; Sourcing
                                <span class="chevron-sm" aria-hidden="true">▲</span>
                            </button>
                            <div class="nav-subgroup-body" id="modules-procurement" hidden>
                                @if(($authIsSuperAdmin ?? false) || ($canPr ?? false))
                                    <a
                                        class="nav-link nested {{ request()->routeIs('modules.procurement.purch-req*') ? 'active' : '' }}"
                                        href="{{ route('modules.procurement.purch-req', $companyQuery ?? []) }}"
                                    >Purchase Requisition</a>
                                @endif
                                @if(($authIsSuperAdmin ?? false) || ($canGrn ?? false))
                                    <a
                                        class="nav-link nested {{ request()->routeIs('modules.procurement.grn*') ? 'active' : '' }}"
                                        href="{{ route('modules.procurement.grn', $companyQuery ?? []) }}"
                                    >Goods Receive Note</a>
                                @endif
                            </div>
                        </div>
                    @endif
                </section>

                @if($authCanAccessMasters ?? false)
                <section class="sidebar-section {{ $activeSection === 'settings' ? 'active' : '' }}" data-section="settings">
                    <div class="nav-section-label">Settings</div>
                    @if($authIsSuperAdmin ?? false)
                        <div class="nav-subgroup">
                            <button type="button" class="nav-subgroup-header" data-nav-target="settings-api-configuration" aria-expanded="false">
                                API Configuration
                                <span class="chevron-sm" aria-hidden="true">▲</span>
                            </button>
                            <div class="nav-subgroup-body" id="settings-api-configuration" hidden>
                                <a class="nav-link nested {{ request()->routeIs('settings.token') ? 'active' : '' }}" href="{{ route('settings.token', $companyQuery ?? []) }}">API Token Timer</a>
                                <a class="nav-link nested {{ request()->routeIs('settings.credentials') ? 'active' : '' }}" href="{{ route('settings.credentials', $companyQuery ?? []) }}">D365 Credentials</a>
                            </div>
                        </div>
                    @endif
                    <div class="nav-subgroup">
                        <button type="button" class="nav-subgroup-header" data-nav-target="settings-system-administration" aria-expanded="false">
                            System Administration
                            <span class="chevron-sm" aria-hidden="true">▲</span>
                        </button>
                        <div class="nav-subgroup-body" id="settings-system-administration" hidden>
                            <a class="nav-link nested {{ request()->routeIs('settings.users.*') ? 'active' : '' }}" href="{{ route('settings.users.index', $companyQuery ?? []) }}">Users</a>
                            <a class="nav-link nested {{ request()->routeIs('settings.roles.*') ? 'active' : '' }}" href="{{ route('settings.roles.index', $companyQuery ?? []) }}">Roles</a>
                            <a class="nav-link nested {{ request()->routeIs('settings.permissions.*') ? 'active' : '' }}" href="{{ route('settings.permissions.index', $companyQuery ?? []) }}">Permissions</a>
                            <a class="nav-link nested {{ request()->routeIs('settings.menu-match.*') ? 'active' : '' }}" href="{{ route('settings.menu-match.index', $companyQuery ?? []) }}">Menu match</a>
                        </div>
                    </div>
                </section>
                @endif
            </nav>
        </div>
    </div>
</aside>
<script>
(() => {
    const sidebar = document.getElementById('appSidebar');
    if (!sidebar) return;

    const menuToggle = sidebar.querySelector('.rail-menu-toggle[data-action="toggle-panel"]');
    const railButtons = document.querySelectorAll('.sidebar-rail .rail-toggle[data-target]');
    const sections = document.querySelectorAll('.sidebar-panel .sidebar-section[data-section]');
    const sectionNames = new Set(Array.from(sections).map((section) => section.dataset.section));

    const setCollapsed = (isCollapsed) => {
        sidebar.classList.toggle('sidebar-collapsed', isCollapsed);
        sidebar.setAttribute('data-panel-open', isCollapsed ? 'false' : 'true');
    };

    const openPanel = () => setCollapsed(false);
    const showAllSections = () => {
        sections.forEach((section) => section.classList.add('active'));
        railButtons.forEach((btn) => btn.classList.remove('active'));
    };
    const togglePanel = () => {
        const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
        if (isCollapsed) {
            openPanel();
            showAllSections();
            return;
        }
        // Keep panel fixed open across modules/masters/settings.
        openPanel();
        showAllSections();
    };

    // On Home (dashboard), start with the text panel hidden — only the icon rail shows.
    // Hamburger or a section icon opens the full menu again.
    const startCollapsed = sidebar.dataset.startCollapsed === '1';
    setCollapsed(startCollapsed);

    const firstAvailableTarget = () => {
        for (const target of ['masters', 'modules', 'dashboard', 'settings']) {
            if (sectionNames.has(target)) {
                return target;
            }
        }
        return null;
    };

    const activate = (target) => {
        const resolvedTarget = sectionNames.has(target) ? target : firstAvailableTarget();
        if (!resolvedTarget) return;

        openPanel();
        railButtons.forEach((btn) => btn.classList.toggle('active', btn.dataset.target === resolvedTarget));
        sections.forEach((section) => section.classList.toggle('active', section.dataset.section === resolvedTarget));
    };

    if (menuToggle) {
        menuToggle.addEventListener('click', togglePanel);
    }
    railButtons.forEach((btn) => {
        btn.addEventListener('click', () => activate(btn.dataset.target));
    });

    sidebar.querySelectorAll('.nav-subgroup-header[data-nav-target]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-nav-target');
            const body = sidebar.querySelector(`#${CSS.escape(targetId)}`);
            if (!body) return;
            const isOpen = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            body.hidden = isOpen;
        });
    });
})();
</script>
