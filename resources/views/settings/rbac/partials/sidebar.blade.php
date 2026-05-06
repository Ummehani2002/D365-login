<aside class="sidebar">
    <div class="sidebar-brand">TI Web App</div>
    <nav>
        <div class="nav-section-label">Menu</div>
        <a class="nav-link" href="{{ route('dashboard', $companyQuery ?? []) }}">Dashboard</a>
        @if($authCanAccessMasters ?? false)
        <a class="nav-link" href="{{ route('masters.company.index', $companyQuery ?? []) }}">Masters</a>
        @endif
        <a class="nav-link" href="{{ route('modules.project-management.item-issue', $companyQuery ?? []) }}">Modules</a>

        <div class="nav-section-label" style="margin-top:8px;">Settings</div>
        <div class="nav-sub">
            @if($authIsSuperAdmin ?? false)
            <a class="nav-link {{ request()->routeIs('settings.token') ? 'active' : '' }}" href="{{ route('settings.token', $companyQuery ?? []) }}">API Token Timer</a>
            <a class="nav-link {{ request()->routeIs('settings.credentials') ? 'active' : '' }}" href="{{ route('settings.credentials', $companyQuery ?? []) }}">D365 Credentials</a>
            @endif
            <a class="nav-link {{ request()->routeIs('settings.users.*') ? 'active' : '' }}" href="{{ route('settings.users.index', $companyQuery ?? []) }}">Users</a>
            <a class="nav-link {{ request()->routeIs('settings.roles.*') ? 'active' : '' }}" href="{{ route('settings.roles.index', $companyQuery ?? []) }}">Roles</a>
            <a class="nav-link {{ request()->routeIs('settings.permissions.*') ? 'active' : '' }}" href="{{ route('settings.permissions.index', $companyQuery ?? []) }}">Permissions</a>
            <a class="nav-link {{ request()->routeIs('settings.menu-match.*') ? 'active' : '' }}" href="{{ route('settings.menu-match.index', $companyQuery ?? []) }}">Menu match</a>
        </div>
    </nav>
    <div class="sidebar-footer">
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">Log out</button>
        </form>
    </div>
</aside>
