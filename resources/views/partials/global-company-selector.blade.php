@if(isset($globalCompanyOptions) && $globalCompanyOptions->count())
<style>
    .global-company-box {
        position: fixed;
        top: 6px;
        right: 14px;
        z-index: 2000;
        width: auto;
        max-width: calc(100vw - 24px);
        background: rgba(255, 255, 255, 0.98);
        border: 1px solid #d2d0ce;
        border-radius: 999px;
        padding: 4px 8px;
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

    /* Reserve room so fixed selector doesn't cover page actions */
    @media (min-width: 1024px) {
        .toolbar-row,
        .form-header,
        .journal-header,
        .main-header,
        .card-head {
            padding-right: 220px;
        }
    }
</style>

<div class="global-company-box">
    <p class="global-company-label">SELECT COMPANY</p>
    <select id="global-company-select" class="global-company-select">
        @foreach($globalCompanyOptions as $company)
            @php($code = strtoupper((string) $company->d365_id))
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
