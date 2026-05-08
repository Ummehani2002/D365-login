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
        width: 322px;
        min-height: 100vh;
        background: #f3f2f1;
        border-right: 1px solid #d2d0ce;
        display: flex;
        flex-shrink: 0;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .sidebar-rail {
        width: 54px;
        background: #fff;
        border-right: 1px solid #edebe9;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 10px 6px;
    }
    .sidebar-panel {
        width: 268px;
        background: #f3f2f1;
        padding: 10px 10px 12px;
        overflow-y: auto;
        transition: width .2s ease, padding .2s ease, opacity .2s ease;
    }
    .rail-toggle {
        width: 40px;
        height: 40px;
        border: 1px solid #d2d0ce;
        background: #fff;
        border-radius: 2px;
        color: #323130;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .rail-toggle:hover { background: #f3f2f1; }
    .rail-toggle.active {
        border-color: #0f6cbd;
        background: #deecf9;
        color: #005a9e;
    }
    .rail-menu-toggle {
        border-color: #0f6cbd;
        color: #0f6cbd;
    }
    .rail-spacer { flex: 1; }
    .panel-card {
        border: 1px solid #d2d0ce;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        min-height: 100%;
    }
    .sidebar-brand {
        padding: 12px 14px 10px;
        border-bottom: 1px solid #edebe9;
        margin: 0;
        font-weight: 700;
        font-size: 15px;
    }
    .nav-section-label {
        padding: 8px 14px 4px;
        color: #8a8886;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .nav-link {
        display: block;
        padding: 8px 12px;
        color: #323130;
        text-decoration: none;
        font-size: 14px;
        border-radius: 6px;
        margin: 1px 10px;
    }
    .nav-link:hover { background: #f3f2f1; }
    .nav-link.active {
        background: #deecf9;
        color: #005a9e;
        font-weight: 500;
    }
    .nav-sub {
        margin: 2px 10px 4px 22px;
        border-left: 2px solid #edebe9;
        padding-left: 2px;
    }
    .nav-sub .nav-link {
        font-size: 13px;
        padding: 6px 10px;
        margin: 1px 0;
        color: #0f6cbd;
        font-weight: 500;
    }
    .sidebar-section[data-section="modules"] .nav-link {
        color: #0f6cbd;
        font-weight: 500;
    }
    .nav-subgroup {
        margin: 4px 10px 6px;
    }
    .nav-subgroup-header {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 7px 8px;
        border: none;
        border-radius: 4px;
        background: #f3f2f1;
        color: #323130;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        text-align: left;
        font-family: inherit;
    }
    .nav-subgroup-header:hover {
        background: #edebe9;
    }
    .nav-subgroup-header .chevron-sm {
        font-size: 10px;
        color: #605e5c;
        transition: transform .2s ease;
    }
    .nav-subgroup-header[aria-expanded="false"] .chevron-sm {
        transform: rotate(180deg);
    }
    .nav-subgroup-body {
        padding-top: 2px;
    }
    .nav-subgroup-body[hidden] {
        display: none;
    }
    .nav-link.nested {
        margin-left: 6px;
        padding-left: 10px;
    }
    .sidebar-section[data-section="masters"] .nav-link.nested {
        color: #323130;
        font-weight: 600;
    }
    .sidebar-section {
        display: none;
        padding-bottom: 10px;
    }
    .sidebar-section.active {
        display: block;
    }
    .sidebar.sidebar-collapsed {
        width: 54px;
    }
    .sidebar.sidebar-collapsed .sidebar-panel {
        width: 0;
        padding-left: 0;
        padding-right: 0;
        opacity: 0;
        overflow: hidden;
    }
    .sidebar-footer {
        padding: 10px;
        border-top: 1px solid #edebe9;
        margin-top: 10px;
    }
    .btn-logout {
        background: transparent;
        color: #605e5c;
        border: 1px solid #8a8886;
        padding: 7px 14px;
        border-radius: 2px;
        cursor: pointer;
        font-size: 13px;
        font-family: inherit;
        width: 100%;
    }
    .main {
        flex: 1;
        padding: 32px 40px;
        overflow: auto;
    }
    .page-bar {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 22px;
    }
    .page-title {
        margin: 0 0 6px;
        font-size: 22px;
        font-weight: 600;
        color: #201f1e;
    }
    .page-subtitle {
        margin: 0;
        font-size: 13px;
        color: #8a8886;
    }
</style>
