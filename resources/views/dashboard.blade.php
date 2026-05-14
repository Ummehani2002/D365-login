<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('settings.rbac.partials.styles')
    <style>
        .main {
            flex: 1;
            padding: 20px 24px 28px;
            overflow: auto;
            min-height: 100vh;
            background:
                radial-gradient(circle at 8% 12%, rgba(255, 197, 120, 0.12), transparent 36%),
                radial-gradient(circle at 90% 8%, rgba(178, 227, 255, 0.12), transparent 34%),
                linear-gradient(128deg, #162230 0%, #1f3448 46%, #294237 100%);
            position: relative;
        }
        .main::before {
            content: "";
            position: fixed;
            inset: 0 0 0 54px;
            pointer-events: none;
            background:
                repeating-linear-gradient(120deg, rgba(255, 255, 255, 0.035) 0 1px, transparent 1px 32px),
                repeating-linear-gradient(160deg, rgba(255, 255, 255, 0.02) 0 1px, transparent 1px 24px);
            z-index: 0;
        }
        .dashboard-shell {
            max-width: 1320px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        .main-header {
            margin-bottom: 14px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }
        .main-header h2 {
            margin: 0;
            font-size: 30px;
            letter-spacing: 0.2px;
            color: #fff;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
        }
        .main-subtitle {
            margin-top: 2px;
            color: rgba(255, 255, 255, 0.88);
            font-size: 13px;
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
        .company-hero {
            margin-top: 16px;
            max-width: none;
            min-height: 460px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            position: relative;
            background:
                radial-gradient(circle at 12% 20%, rgba(255, 197, 120, 0.15), transparent 46%),
                radial-gradient(circle at 90% 18%, rgba(178, 227, 255, 0.13), transparent 42%),
                linear-gradient(128deg, #1a2a3b 0%, #23445a 42%, #2e4a3f 100%);
            box-shadow: 0 14px 30px rgba(16, 24, 40, 0.24);
            z-index: 1;
        }
        .company-hero.company-hero--custom-bg {
            background: linear-gradient(128deg, #152028 0%, #1a2835 100%);
        }
        .company-hero-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            z-index: 0;
            user-select: none;
        }
        .company-hero-bg-scrim {
            position: absolute;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            background:
                radial-gradient(circle at 12% 20%, rgba(255, 197, 120, 0.12), transparent 46%),
                radial-gradient(circle at 90% 18%, rgba(178, 227, 255, 0.1), transparent 42%),
                linear-gradient(128deg, rgba(26, 42, 59, 0.82) 0%, rgba(35, 68, 90, 0.78) 42%, rgba(46, 74, 63, 0.8) 100%);
        }
        .company-hero::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 2;
            background:
                repeating-linear-gradient(120deg, rgba(255, 255, 255, 0.07) 0 1px, transparent 1px 28px),
                repeating-linear-gradient(160deg, rgba(255, 255, 255, 0.05) 0 1px, transparent 1px 20px);
            pointer-events: none;
        }
        .company-hero-content {
            position: absolute;
            inset: 0;
            z-index: 3;
            padding: 28px;
            display: grid;
            grid-template-columns: 1fr 360px;
            align-items: center;
            gap: 22px;
        }
        .company-hero-top {
            max-width: 640px;
        }
        .company-hero-title {
            margin: 0;
            color: #fff;
            font-size: 42px;
            font-weight: 700;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
        }
        .company-hero-meta {
            margin-top: 10px;
            color: rgba(255, 255, 255, 0.96);
            font-size: 15px;
            letter-spacing: 0.3px;
        }
        .company-hero-chip {
            margin-top: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            color: #fff;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.26);
        }
        .company-hero-brand {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(3px);
        }
        .company-hero-brand img {
            width: 100%;
            height: 170px;
            object-fit: contain;
            display: block;
            border-radius: 8px;
            background: #fff;
            padding: 8px;
        }
        .company-hero-brand-placeholder {
            min-height: 120px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 14px 10px;
            text-align: center;
            font-size: 12px;
            line-height: 1.45;
            color: rgba(255, 255, 255, 0.92);
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.2);
        }
        .company-hero-brand-placeholder strong {
            color: #fff;
            font-size: 11px;
            word-break: break-all;
        }
        .company-hero-brand-hint {
            display: block;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.75);
        }
        .company-hero-user {
            width: 100%;
            padding: 15px 16px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            background: rgba(11, 22, 30, 0.45);
            backdrop-filter: blur(2px);
            color: #fff;
        }
        .company-hero-user h3 {
            margin: 0 0 8px;
            font-size: 16px;
            font-weight: 600;
        }
        .company-hero-user p {
            margin: 4px 0;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.94);
        }
        .company-hero-right {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 14px;
            width: 100%;
        }
        @media (max-width: 1024px) {
            .main::before {
                inset: 0;
            }
        }
        @media (max-width: 860px) {
            .company-hero-content {
                grid-template-columns: 1fr;
                align-items: start;
            }
            .company-hero-title {
                font-size: 34px;
            }
            .company-hero-right {
                max-width: 420px;
            }
            .company-hero-user {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    @include('partials.global-company-selector')
    @php
        $companyCode = strtoupper((string) ($currentCompanyCode ?? $globalSelectedCompany ?? request()->query('company', '')));
        $companyQuery = $companyCode !== '' ? ['company' => $companyCode] : [];
    @endphp
    @php
        $authCanAccessMasters = $authCanAccessMasters ?? ($authShowMastersSettingsNav ?? false);
        $authIsSuperAdmin = $authIsSuperAdmin ?? ($authShowMastersSettingsNav ?? false);
    @endphp
    @include('settings.rbac.partials.sidebar')

    <main class="main">
        <div class="dashboard-shell">
        <div class="main-header">
            <div>
                <h2>Dashboard</h2>
                <div class="main-subtitle">Company workspace overview</div>
            </div>
        </div>

        @if (session('warning'))
            <div style="max-width:520px;padding:10px 14px;margin-bottom:16px;background:#fff4ce;border:1px solid #e0d0a0;border-radius:2px;font-size:13px;color:#8a6d3b;">
                {{ session('warning') }}
            </div>
        @endif

        @php
            $selectedCompany = collect($companies ?? [])->first(function ($company) use ($companyCode) {
                return strtoupper((string) ($company->d365_id ?? '')) === $companyCode;
            });
            $selectedCompanyName = trim((string) ($selectedCompany->name ?? 'PROSCAPE LLC'));
            if ($selectedCompanyName === '') {
                $selectedCompanyName = 'PROSCAPE LLC';
            }
            $companyLogoUrl = null;
            $companyHeroBgUrl = null;
            if ($companyCode !== '') {
                $logoStem = $companyCode;
                $logoAliases = config('company_logos.logo_stem_aliases', []);
                if (is_array($logoAliases) && isset($logoAliases[$companyCode])) {
                    $logoStem = strtoupper((string) $logoAliases[$companyCode]);
                }
                foreach (['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif'] as $ext) {
                    $relPath = 'images/companies/' . $logoStem . '.' . $ext;
                    $absPath = public_path($relPath);
                    if (file_exists($absPath)) {
                        $companyLogoUrl = asset($relPath) . '?v=' . (string) filemtime($absPath);
                        break;
                    }
                }
                foreach (['gif', 'webp', 'png', 'jpg', 'jpeg'] as $ext) {
                    $heroPath = 'images/companies/' . $companyCode . '_bg.' . $ext;
                    $heroAbs = public_path($heroPath);
                    if (file_exists($heroAbs)) {
                        $companyHeroBgUrl = asset($heroPath) . '?v=' . (string) filemtime($heroAbs);
                        break;
                    }
                }
            }
        @endphp

        <section class="company-hero{{ $companyHeroBgUrl ? ' company-hero--custom-bg' : '' }}" aria-label="Selected company dashboard hero">
            @if ($companyHeroBgUrl)
                <img class="company-hero-bg" src="{{ $companyHeroBgUrl }}" alt="{{ $companyCode }} workspace background">
                <div class="company-hero-bg-scrim" aria-hidden="true"></div>
            @endif
            <div class="company-hero-content">
                <div class="company-hero-top">
                    <h3 class="company-hero-title">{{ $selectedCompanyName }}</h3>
                    <div class="company-hero-meta">
                        Company ID: <strong>{{ $companyCode !== '' ? $companyCode : '—' }}</strong>
                    </div>
                    <div class="company-hero-chip">Active Workspace</div>
                </div>

                <div class="company-hero-right">
                    <div class="company-hero-brand">
                        @if ($companyLogoUrl)
                            <img
                                src="{{ $companyLogoUrl }}"
                                alt="{{ $companyCode }} logo"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                            >
                        @endif
                        <div class="company-hero-brand-placeholder" style="{{ $companyLogoUrl ? 'display:none;' : '' }}">
                            Each company uses its own logo file (not shared with others).
                            <strong>public/images/companies/{{ $companyCode ?: 'CODE' }}.png</strong>
                            <span class="company-hero-brand-hint">Same pattern for other IDs, e.g. ML.jpg, TS.webp. Optional hero: {{ $companyCode ?: 'CODE' }}_bg.gif</span>
                        </div>
                    </div>

                    <div class="company-hero-user">
                        <h3>User Details</h3>
                        <p><strong>Name:</strong> {{ auth()->user()->name }}</p>
                        <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
                        <p><strong>User ID:</strong> {{ auth()->user()->id }}</p>
                    </div>
                </div>
            </div>
        </section>
        </div>
    </main>

</body>
</html>

