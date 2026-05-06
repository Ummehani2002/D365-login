@if(isset($globalCompanyOptions))
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
        right: 14px;
        z-index: 2000;
        width: auto;
        max-width: min(420px, calc(100vw - 24px));
        background: rgba(255, 255, 255, 0.98);
        border: 1px solid #d2d0ce;
        border-radius: 999px;
        padding: 4px 12px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .global-company-label {
        margin: 0;
        font-size: 11px;
        color: #605e5c;
        font-weight: 600;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .global-company-select {
        width: 160px;
        border: 1px solid #8a8886;
        border-radius: 999px;
        padding: 4px 24px 4px 10px;
        font-size: 12px;
        font-family: inherit;
        color: #323130;
        background: #fff;
        height: 30px;
    }

</style>

@if($globalCompanyOptions->count())
<div class="global-company-box">
    <p class="global-company-label">SELECT COMPANY</p>
    <select id="global-company-select" class="global-company-select">
        @foreach($globalCompanyOptions as $company)
            @php($code = strtoupper((string) $company->company_id))
            <option value="{{ $code }}" {{ $globalSelectedCompany === $code ? 'selected' : '' }}>
                {{ $code }} - {{ $company->name }}
            </option>
        @endforeach
    </select>
</div>

<script>
(() => {
    const selector = document.getElementById('global-company-select');
    if (!selector) return;

    selector.addEventListener('change', () => {
        const company = (selector.value || '').trim();
        const url = new URL(window.location.href);
        if (company) {
            url.searchParams.set('company', company);
        } else {
            url.searchParams.delete('company');
        }
        window.location.href = url.toString();
    });
})();
</script>
@endif
@endif
