<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Settings - API Token</title>
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
            width: 260px; min-height: 100vh; background: #fff;
            border-right: 1px solid #edebe9; padding: 16px 0; flex-shrink: 0;
        }
        .sidebar-brand { padding: 10px 16px 18px; border-bottom: 1px solid #edebe9; margin-bottom: 8px; font-weight: 700; font-size: 15px; }
        .nav-section-label { padding: 10px 16px 4px; color: #8a8886; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; }
        .nav-link { display: block; padding: 9px 16px; color: #323130; text-decoration: none; font-size: 14px; border-radius: 2px; margin: 1px 8px; }
        .nav-link:hover { background: #f3f2f1; }
        .nav-link.active { background: #deecf9; color: #005a9e; font-weight: 500; }
        .nav-sub { margin-left: 16px; border-left: 2px solid #edebe9; padding-left: 4px; }
        .nav-sub .nav-link { font-size: 13px; padding: 7px 12px; }
        .sidebar-footer { padding: 16px; border-top: 1px solid #edebe9; margin-top: 8px; }
        .btn-logout { background: transparent; color: #605e5c; border: 1px solid #8a8886; padding: 7px 14px; border-radius: 2px; cursor: pointer; font-size: 13px; font-family: inherit; width: 100%; }
        .btn-logout:hover { background: #f3f2f1; }

        .main { flex: 1; padding: 32px 40px; overflow: auto; }
        .page-title { margin: 0 0 6px; font-size: 22px; font-weight: 600; color: #201f1e; }
        .page-subtitle { margin: 0 0 28px; font-size: 13px; color: #8a8886; }

        .card {
            background: #fff; border: 1px solid #edebe9; border-radius: 4px;
            max-width: 640px; padding: 28px 32px;
        }
        .card-title { margin: 0 0 20px; font-size: 15px; font-weight: 600; color: #201f1e; display: flex; align-items: center; gap: 10px; }

        .badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-valid   { background: #dff6dd; color: #107c10; }
        .badge-expired { background: #fde7e9; color: #a4262c; }
        .badge-none    { background: #f3f2f1; color: #8a8886; }

        .countdown-wrap { margin: 20px 0; text-align: center; }
        .countdown-ring { position: relative; display: inline-block; }
        .countdown-ring svg { transform: rotate(-90deg); }
        .countdown-ring .bg { fill: none; stroke: #edebe9; }
        .countdown-ring .fg { fill: none; stroke: #005a9e; stroke-linecap: round; transition: stroke-dashoffset 1s linear, stroke .5s; }
        .countdown-center { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .countdown-time  { font-size: 1.6rem; font-weight: 700; color: #201f1e; line-height: 1; }
        .countdown-label { font-size: 11px; color: #8a8886; margin-top: 2px; }

        .info-row { display: flex; align-items: flex-start; gap: 12px; padding: 11px 0; border-bottom: 1px solid #f3f2f1; font-size: 13px; }
        .info-row:last-of-type { border-bottom: none; }
        .info-label { width: 140px; flex-shrink: 0; color: #8a8886; font-weight: 500; }
        .info-value { color: #323130; word-break: break-all; }
        .duration-note { font-size: 11px; color: #8a8886; margin-top: 2px; }

        .token-box { margin-top: 6px; }
        .token-text {
            font-family: 'Courier New', monospace; font-size: 11px; color: #605e5c;
            background: #f8f7f6; border: 1px solid #edebe9; border-radius: 2px;
            padding: 10px 12px; word-break: break-all; line-height: 1.6;
            max-height: 60px; overflow: hidden; transition: max-height .3s ease;
        }
        .token-text.expanded { max-height: 360px; overflow-y: auto; }
        .token-actions { display: flex; gap: 8px; margin-top: 6px; }
        .btn-sm { padding: 4px 12px; font-size: 12px; border-radius: 2px; border: 1px solid #8a8886; background: #fff; color: #323130; cursor: pointer; font-family: inherit; }
        .btn-sm:hover { background: #f3f2f1; }
        .btn-sm.copied { background: #dff6dd; color: #107c10; border-color: #9fd89f; }
    </style>
</head>
<body>
    @include('partials.global-company-selector')

<aside class="sidebar">
    <div class="sidebar-brand">TI Web App</div>
    <nav>
        <div class="nav-section-label">Menu</div>
        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
        <a class="nav-link" href="{{ route('masters.company.index') }}">Masters</a>
        <a class="nav-link" href="{{ route('modules.project-management.item-issue') }}">Modules</a>

        <div class="nav-section-label" style="margin-top:8px;">Settings</div>
        <div class="nav-sub">
            <a class="nav-link active" href="{{ route('settings.token') }}">API Token Timer</a>
            <a class="nav-link" href="{{ route('settings.credentials') }}">D365 Credentials</a>
        </div>
    </nav>
    <div class="sidebar-footer">
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">Log out</button>
        </form>
    </div>
</aside>

<main class="main">
    <h1 class="page-title">API Token Timer</h1>
    <p class="page-subtitle">Manage the Azure AD bearer token used for all D365 API calls.</p>

    <div class="card">
        <div class="card-title">
            Bearer Token
            @if($token && !$token->isExpired())
                <span class="badge badge-valid">Valid</span>
            @elseif($token && $token->isExpired())
                <span class="badge badge-expired">Expired</span>
            @else
                <span class="badge badge-none">No token</span>
            @endif
        </div>

        @php
            $totalSeconds = 3599;
            $remaining    = $token ? $token->secondsRemaining() : 0;
            $durationSecs = $token ? (int) $token->created_at->diffInSeconds($token->expires_at) : 0;
            $durationMins = (int) round($durationSecs / 60);
        @endphp

        <div class="countdown-wrap">
            <div class="countdown-ring">
                <svg width="130" height="130" viewBox="0 0 130 130">
                    <circle class="bg" cx="65" cy="65" r="55" stroke-width="7"/>
                    <circle class="fg" id="ring-fg" cx="65" cy="65" r="55" stroke-width="7"
                        style="stroke-dasharray:{{ 2 * M_PI * 55 }};stroke-dashoffset:{{ $remaining > 0 ? (2 * M_PI * 55) * (1 - $remaining / $totalSeconds) : (2 * M_PI * 55) }}"/>
                </svg>
                <div class="countdown-center">
                    <div class="countdown-time" id="countdown-display">
                        {{ $remaining > 0 ? gmdate('H:i:s', $remaining) : '00:00:00' }}
                    </div>
                    <div class="countdown-label">remaining</div>
                </div>
            </div>
        </div>

        <div class="info-row">
            <span class="info-label">Generated at</span>
            <span class="info-value" id="info-generated">{{ $token ? $token->created_at->format('d M Y  H:i:s') : '--' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Expires at</span>
            <div class="info-value">
                <div id="info-expires">{{ $token ? $token->expires_at->format('d M Y  H:i:s') : '--' }}</div>
                @if($token)
                    <div class="duration-note" id="info-duration">Valid for {{ $durationMins }} min ({{ $durationSecs }} sec)</div>
                @endif
            </div>
        </div>
        <div class="info-row">
            <span class="info-label">Generated by</span>
            <span class="info-value" id="info-by">{{ $token->generated_by ?? '--' }}</span>
        </div>
        <div class="info-row" style="align-items:flex-start;">
            <span class="info-label" style="padding-top:4px;">Full token</span>
            <div class="info-value" style="flex:1;">
                <div class="token-box">
                    <div class="token-text" id="token-text">{{ $token ? $token->access_token : '--' }}</div>
                </div>
                <div class="token-actions">
                    <button type="button" class="btn-sm" id="btn-toggle">Show full token</button>
                    <button type="button" class="btn-sm" id="btn-copy">Copy</button>
                </div>
            </div>
        </div>
    </div>

    <div id="auto-status" style="display:none; max-width:640px; margin-top:16px; padding:12px 16px; border-radius:4px; font-size:13px; display:flex; align-items:center; gap:10px;"></div>
</main>

<script>
    const TOTAL        = {{ $totalSeconds }};
    const SECS_AT_LOAD = {{ $remaining }};
    const PAGE_LOAD_MS = Date.now();
    const CIRC         = 2 * Math.PI * 55;

    const display     = document.getElementById('countdown-display');
    const ringFg      = document.getElementById('ring-fg');
    const tokenText   = document.getElementById('token-text');
    const autoStatus  = document.getElementById('auto-status');
    const badgeEl     = document.querySelector('.badge');

    function pad(n) { return String(n).padStart(2, '0'); }
    function fmt(s) { s = Math.max(0, s); return pad(Math.floor(s/3600)) + ':' + pad(Math.floor((s%3600)/60)) + ':' + pad(s%60); }
    function now() { return new Date().toLocaleTimeString(); }

    function updateRing(rem) {
        const offset = CIRC * (1 - Math.max(0, Math.min(1, rem / TOTAL)));
        ringFg.style.strokeDashoffset = offset;
        ringFg.style.stroke = rem > 600 ? '#005a9e' : rem > 180 ? '#d83b01' : '#a4262c';
        display.textContent = fmt(rem);
    }

    function setAutoStatus(msg, color, bg) {
        autoStatus.style.cssText = `display:flex; align-items:center; gap:10px; max-width:640px; margin-top:16px; padding:12px 16px; border-radius:4px; font-size:13px; background:${bg}; border:1px solid ${color}; color:${color};`;
        autoStatus.innerHTML = `<span style="width:8px;height:8px;border-radius:50%;background:${color};flex-shrink:0;"></span><span>${msg}</span>`;
    }
    function hideAutoStatus() { autoStatus.style.display = 'none'; }

    let countdownTimer = null;
    let tokenBase = { startSecs: SECS_AT_LOAD, startMs: PAGE_LOAD_MS };

    function getRemaining() {
        return Math.max(0, tokenBase.startSecs - Math.floor((Date.now() - tokenBase.startMs) / 1000));
    }

    function startCountdown() {
        if (countdownTimer) clearInterval(countdownTimer);
        countdownTimer = setInterval(function () {
            const rem = getRemaining();
            updateRing(rem);
            if (rem <= 0) {
                clearInterval(countdownTimer);
                countdownTimer = null;
                triggerAutoGenerate();
            }
        }, 1000);
    }

    let autoGenerating = false;

    function triggerAutoGenerate() {
        if (autoGenerating) return;
        autoGenerating = true;
        setAutoStatus('Auto-refreshing token...', '#605e5c', '#f3f2f1');
        if (badgeEl) { badgeEl.className = 'badge badge-none'; badgeEl.textContent = 'Refreshing...'; }

        fetch('{{ route("settings.token.generate") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept':       'application/json',
                'Content-Type':'application/json',
            },
        })
        .then(r => r.json())
        .then(function (data) {
            autoGenerating = false;

            if (data.status) {
                document.getElementById('info-generated').textContent = data.generated_at_human;
                document.getElementById('info-expires').textContent   = data.expires_at_human;
                const dur = document.getElementById('info-duration');
                if (dur) dur.textContent = 'Valid for ' + data.duration_minutes + ' min (' + data.seconds_remaining + ' sec)';
                document.getElementById('info-by').textContent = data.generated_by;
                tokenText.textContent = data.full_token;
                tokenText.classList.remove('expanded');
                document.getElementById('btn-toggle').textContent = 'Show full token';

                tokenBase = { startSecs: data.seconds_remaining, startMs: Date.now() };
                updateRing(data.seconds_remaining);
                startCountdown();

                if (badgeEl) { badgeEl.className = 'badge badge-valid'; badgeEl.textContent = 'Valid'; }
                setAutoStatus('Token auto-refreshed at ' + now() + ' - next refresh in ~' + data.duration_minutes + ' min', '#107c10', '#dff6dd');
                setTimeout(hideAutoStatus, 8000);
            } else {
                if (badgeEl) { badgeEl.className = 'badge badge-expired'; badgeEl.textContent = 'Error'; }
                setAutoStatus('Auto-refresh failed: ' + data.message + ' - retrying in 30 s', '#a4262c', '#fde7e9');
                setTimeout(triggerAutoGenerate, 30000);
            }
        })
        .catch(function () {
            autoGenerating = false;
            if (badgeEl) { badgeEl.className = 'badge badge-expired'; badgeEl.textContent = 'Error'; }
            setAutoStatus('Auto-refresh request failed - retrying in 30 s', '#a4262c', '#fde7e9');
            setTimeout(triggerAutoGenerate, 30000);
        });
    }

    if (SECS_AT_LOAD > 0) {
        updateRing(SECS_AT_LOAD);
        startCountdown();
        setAutoStatus('Token active - auto-refresh scheduled when timer reaches 0', '#005a9e', '#eff6fc');
        setTimeout(hideAutoStatus, 5000);
    } else {
        updateRing(0);
        triggerAutoGenerate();
    }

    document.getElementById('btn-toggle').addEventListener('click', function () {
        const exp = tokenText.classList.toggle('expanded');
        this.textContent = exp ? 'Collapse' : 'Show full token';
    });
    document.getElementById('btn-copy').addEventListener('click', function () {
        const t = tokenText.textContent.trim();
        if (!t || t === '--') return;
        navigator.clipboard.writeText(t).then(() => {
            this.textContent = 'Copied';
            this.classList.add('copied');
            setTimeout(() => { this.textContent = 'Copy'; this.classList.remove('copied'); }, 2000);
        });
    });

</script>
</body>
</html>
