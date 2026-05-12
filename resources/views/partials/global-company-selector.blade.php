@php
    $companyOptions = isset($globalCompanyOptions) ? $globalCompanyOptions : collect();
    $hasCompanyOptions = $companyOptions->count() > 0;

    $authUser = auth()->user();
    $hasAuthUser = $authUser !== null;
    $userName = $hasAuthUser ? trim((string) ($authUser->name ?? 'User')) : 'User';
    $userEmail = $hasAuthUser ? trim((string) ($authUser->email ?? '')) : '';

    $initials = 'U';
    if ($hasAuthUser) {
        $pieces = preg_split('/\s+/', $userName) ?: [];
        $letters = [];
        foreach ($pieces as $piece) {
            $piece = trim((string) $piece);
            if ($piece === '') {
                continue;
            }
            $letters[] = strtoupper(substr($piece, 0, 1));
            if (count($letters) >= 2) {
                break;
            }
        }
        if (count($letters) > 0) {
            $initials = implode('', $letters);
        }
    }
@endphp

<style>
    :root {
        --app-font: "Segoe UI", "Inter", "Roboto", Arial, sans-serif;
        --app-text: #1f2937;
        --app-muted: #6b7280;
        --app-bg: #f5f6f8;
        --app-border: #e5e7eb;
        --app-primary: #0f6cbd;
        --app-surface: #ffffff;
    }

    html, body {
        font-family: var(--app-font);
        color: var(--app-text);
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    body {
        background: var(--app-bg);
        line-height: 1.45;
    }

    h1, h2, h3, h4, h5, h6 {
        color: #111827;
        letter-spacing: 0.1px;
    }

    p, small, label, .label {
        color: var(--app-muted);
    }

    input, select, textarea, button {
        font-family: inherit;
    }

    input, select, textarea {
        border: 1px solid #cfd4dc;
        border-radius: 6px;
        background: #fff;
        color: var(--app-text);
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    input:focus, select:focus, textarea:focus {
        outline: none;
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
    }

    button, .btn {
        border-radius: 6px;
    }

    .btn-primary {
        background: var(--app-primary);
        border-color: var(--app-primary);
    }

    table {
        background: var(--app-surface);
    }

    th {
        color: #4b5563;
        font-weight: 600;
    }

    td {
        color: #1f2937;
    }

    .card, .page-shell, .form-wrap {
        border-color: var(--app-border);
        border-radius: 8px;
    }

    .sidebar {
        border-right-color: var(--app-border);
    }

    .menu-link {
        border-radius: 8px;
    }

    .global-company-box {
        position: fixed;
        top: 6px;
        right: 66px;
        z-index: 2000;
        width: auto;
        min-width: 200px;
        max-width: min(420px, calc(100vw - 120px));
        height: 44px;
        background: #fff;
        border: 1px solid #d0d7de;
        border-radius: 999px;
        padding: 5px 10px 5px 12px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    .global-company-icon {
        width: 20px;
        height: 20px;
        color: #0f6cbd;
        flex-shrink: 0;
    }
    .global-company-select {
        width: min(340px, calc(100vw - 200px));
        min-width: 220px;
        height: 34px;
        border: 1px solid #8a8886;
        border-radius: 999px;
        padding: 5px 32px 5px 12px;
        font-size: 14px;
        line-height: 1.2;
        color: #323130;
        background: #fff;
        cursor: pointer;
    }
    .global-company-select:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(15, 108, 189, 0.25);
    }

    .global-user-box {
        position: fixed;
        top: 6px;
        right: 14px;
        z-index: 2100;
    }
    .global-user-trigger {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        border: 1px solid #0f6cbd;
        background: #deecf9;
        color: #005a9e;
        font-size: 13px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .global-user-menu {
        position: absolute;
        top: 44px;
        right: 0;
        width: min(280px, calc(100vw - 24px));
        background: #fff;
        border: 1px solid #d2d0ce;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        padding: 10px;
        display: none;
    }
    .global-user-menu.open {
        display: block;
    }
    .global-user-name {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: #201f1e;
        line-height: 1.3;
    }
    .global-user-email {
        margin: 2px 0 10px;
        font-size: 13px;
        color: #323130;
        line-height: 1.3;
        word-break: break-word;
    }
    .global-user-signout {
        width: 100%;
        height: 34px;
        border: 1px solid #0f6cbd;
        background: #fff;
        color: #0f6cbd;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        text-align: left;
        padding: 0 10px;
    }
    .global-user-signout:hover {
        background: #deecf9;
    }
</style>

@if($hasCompanyOptions)
<div class="global-company-box">
    <svg class="global-company-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
        <rect x="3" y="3" width="18" height="18" rx="2"></rect>
        <path d="M3 9h18"></path>
        <path d="M9 21V9"></path>
    </svg>
    <select id="global-company-select" class="global-company-select" aria-label="Select company" title="Change company">
        @foreach($companyOptions as $company)
            @php($code = strtoupper((string) ($company->d365_id ?? $company->company_id ?? '')))
            <option value="{{ $code }}" {{ $globalSelectedCompany === $code ? 'selected' : '' }}>
                {{ $code }} - {{ $company->name }}
            </option>
        @endforeach
    </select>
</div>
@endif

@if($hasAuthUser)
<div class="global-user-box" id="global-user-box">
    <button
        type="button"
        id="global-user-trigger"
        class="global-user-trigger"
        aria-label="User menu"
        aria-expanded="false"
        aria-controls="global-user-menu"
    >{{ $initials }}</button>

    <div id="global-user-menu" class="global-user-menu" role="menu" aria-hidden="true">
        <p class="global-user-name">{{ $userName }}</p>
        <p class="global-user-email">{{ $userEmail !== '' ? $userEmail : 'No email' }}</p>
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="global-user-signout">Sign out</button>
        </form>
    </div>
</div>
@endif

<script>
(() => {
    const selector = document.getElementById('global-company-select');
    if (selector) {
        const url = new URL(window.location.href);
        const selected = (selector.value || '').trim().toUpperCase();
        const current = (url.searchParams.get('company') || '').trim().toUpperCase();

        if (selected && selected !== current) {
            url.searchParams.set('company', selected);
            window.location.replace(url.toString());
            return;
        }

        selector.addEventListener('change', () => {
            const company = (selector.value || '').trim();
            const nextUrl = new URL(window.location.href);
            if (company) {
                nextUrl.searchParams.set('company', company);
            } else {
                nextUrl.searchParams.delete('company');
            }
            window.location.href = nextUrl.toString();
        });
    }

    const root = document.getElementById('global-user-box');
    const trigger = document.getElementById('global-user-trigger');
    const menu = document.getElementById('global-user-menu');
    if (!root || !trigger || !menu) return;

    const setOpen = (open) => {
        menu.classList.toggle('open', open);
        trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        menu.setAttribute('aria-hidden', open ? 'false' : 'true');
    };

    trigger.addEventListener('click', (event) => {
        event.stopPropagation();
        setOpen(!menu.classList.contains('open'));
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            setOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });
})();
</script>
