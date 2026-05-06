<style>
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f3f2f1; color: #323130; display: flex; min-height: 100vh; }
    .sidebar { width: 260px; min-height: 100vh; background: #fff; border-right: 1px solid #edebe9; padding: 16px 0; flex-shrink: 0; }
    .sidebar-brand { padding: 10px 16px 18px; border-bottom: 1px solid #edebe9; margin-bottom: 8px; font-weight: 700; font-size: 15px; }
    .nav-section-label { padding: 10px 16px 4px; color: #8a8886; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; }
    .nav-link { display: block; padding: 9px 16px; color: #323130; text-decoration: none; font-size: 14px; border-radius: 2px; margin: 1px 8px; }
    .nav-link:hover { background: #f3f2f1; }
    .nav-link.active { background: #deecf9; color: #005a9e; font-weight: 500; }
    .nav-sub { margin-left: 16px; border-left: 2px solid #edebe9; padding-left: 4px; }
    .nav-sub .nav-link { font-size: 13px; padding: 7px 12px; }
    .sidebar-footer { padding: 16px; border-top: 1px solid #edebe9; margin-top: 8px; }
    .btn-logout { background: transparent; color: #605e5c; border: 1px solid #8a8886; padding: 7px 14px; border-radius: 2px; cursor: pointer; font-size: 13px; font-family: inherit; width: 100%; }
    .main { flex: 1; padding: 32px 40px; overflow: auto; }
    .page-title { margin: 0 0 6px; font-size: 22px; font-weight: 600; color: #201f1e; }
</style>

