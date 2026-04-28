@if(isset($globalCompanyOptions) && $globalCompanyOptions->count())
<style>
    .global-company-box {
        position: fixed;
        top: 12px;
        right: 14px;
        z-index: 2000;
        width: 320px;
        max-width: calc(100vw - 24px);
        background: #fff;
        border: 1px solid #d2d0ce;
        border-radius: 2px;
        padding: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    .global-company-label {
        margin: 0 0 8px;
        font-size: 13px;
        color: #605e5c;
        font-weight: 600;
        letter-spacing: 0.4px;
        text-transform: uppercase;
    }
    .global-company-select {
        width: 100%;
        border: 1px solid #8a8886;
        border-radius: 2px;
        padding: 8px 10px;
        font-size: 18px;
        font-family: inherit;
        color: #323130;
        background: #fff;
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
