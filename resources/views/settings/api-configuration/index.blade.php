<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>API Configuration</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-bearer-token" content="{{ $apiBearerToken }}">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            background: #f3f2f1;
            color: #323130;
            min-height: 100vh;
            display: flex;
        }
        .sidebar {
            width: 280px;
            min-height: 100vh;
            background: #fff;
            border-right: 1px solid #edebe9;
            padding: 16px 0;
            flex-shrink: 0;
        }
        .sidebar-brand {
            padding: 8px 16px 20px;
            border-bottom: 1px solid #edebe9;
            margin-bottom: 8px;
        }
        .sidebar-brand h1 {
            margin: 0;
            font-size: 1.15rem;
            color: #201f1e;
        }
        .nav-group { margin-bottom: 4px; }
        .nav-group-header {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            margin: 0 8px;
            border: none;
            border-radius: 2px;
            background: #deecf9;
            color: #005a9e;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-align: left;
            font-family: inherit;
        }
        .nav-group-header .nav-label { flex: 1; }
        .nav-group-header .chevron { font-size: 10px; transition: transform 0.2s; }
        .nav-group-header[aria-expanded="false"] .chevron { transform: rotate(180deg); }
        .nav-group-body { padding: 4px 0 8px 8px; }
        .nav-group-body[hidden] { display: none; }
        .nav-link {
            display: block;
            padding: 8px 14px 8px 36px;
            margin: 2px 8px;
            border-radius: 2px;
            color: #323130;
            text-decoration: none;
            font-size: 14px;
        }
        .nav-link:hover { background: #f3f2f1; color: #201f1e; }
        .nav-link.active {
            background: #deecf9;
            color: #005a9e;
            font-weight: 500;
        }
        .nav-subgroup { margin-top: 4px; }
        .nav-subgroup-header {
            width: calc(100% - 16px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px 8px 28px;
            margin: 0 8px;
            border: none;
            border-radius: 2px;
            background: #f3f2f1;
            color: #605e5c;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-align: left;
            font-family: inherit;
        }
        .nav-subgroup-header .chevron-sm { font-size: 9px; transition: transform 0.2s; }
        .nav-subgroup-header[aria-expanded="false"] .chevron-sm { transform: rotate(180deg); }
        .nav-link.nested { padding-left: 44px; font-size: 13px; }
        .nav-subgroup-body[hidden] { display: none; }
        .logout-row { padding: 16px; margin-top: 8px; }
        .btn-logout {
            background: transparent;
            color: #605e5c;
            border: 1px solid #8a8886;
            padding: 8px 14px;
            border-radius: 2px;
            cursor: pointer;
            font-size: 13px;
            font-family: inherit;
        }
        .main {
            flex: 1;
            padding: 24px 32px;
            overflow: auto;
        }
        .card {
            max-width: 760px;
            background: #fff;
            border: 1px solid #edebe9;
            border-radius: 2px;
            padding: 16px;
        }
        .token-row {
            display: grid;
            grid-template-columns: 170px 1fr auto;
            gap: 10px;
            align-items: center;
        }
        .token-output {
            background: #f3f2f1;
            font-family: Consolas, "Courier New", monospace;
            width: 100%;
            border: 1px solid #8a8886;
            border-radius: 2px;
            padding: 8px;
        }
        .btn {
            border: 1px solid #8a8886;
            background: #fff;
            color: #323130;
            border-radius: 2px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-primary {
            border-color: #106ebe;
            background: #106ebe;
            color: #fff;
        }
        .help {
            margin-top: 10px;
            color: #605e5c;
            font-size: 12px;
        }
        .help.warn {
            color: #a4262c;
            font-weight: 600;
        }
    </style>
</head>
<body>
<aside class="sidebar" aria-label="Main navigation">
    <div class="sidebar-brand"><h1>MENU</h1></div>
    <nav>
        <div class="nav-group">
            <button type="button" class="nav-group-header" data-nav-target="nav-masters" aria-expanded="false">
                <span class="nav-label">Masters</span><span class="chevron" aria-hidden="true">▲</span>
            </button>
            <div class="nav-group-body" id="nav-masters" hidden>
                <a class="nav-link" href="{{ route('masters.company.index') }}">Companies</a>
                <a class="nav-link" href="{{ route('masters.categories.index') }}">Categories</a>
                <a class="nav-link" href="{{ route('masters.items.index') }}">Items</a>
                <a class="nav-link" href="{{ route('masters.project.index') }}">Projects</a>
            </div>
        </div>
        <div class="nav-group">
            <button type="button" class="nav-group-header" data-nav-target="nav-modules" aria-expanded="false">
                <span class="nav-label">Modules</span><span class="chevron" aria-hidden="true">▲</span>
            </button>
            <div class="nav-group-body" id="nav-modules" hidden>
                <div class="nav-subgroup">
                    <button type="button" class="nav-subgroup-header" data-nav-target="nav-pm" aria-expanded="false">
                        Project Management <span class="chevron-sm" aria-hidden="true">▲</span>
                    </button>
                    <div class="nav-subgroup-body" id="nav-pm" hidden>
                        <a class="nav-link nested" href="{{ route('modules.project-management.item-issue') }}">Item Issue</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="nav-group">
            <button type="button" class="nav-group-header" data-nav-target="nav-settings" aria-expanded="true">
                <span class="nav-label">Settings</span><span class="chevron" aria-hidden="true">▲</span>
            </button>
            <div class="nav-group-body" id="nav-settings">
                <a class="nav-link active" href="{{ route('settings.api-configuration') }}">API Configuration</a>
            </div>
        </div>
    </nav>
    <div class="logout-row">
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">Log out</button>
        </form>
    </div>
</aside>
<main class="main">
    <div class="card">
        <h2 style="margin-top:0;">API Configuration</h2>
        <div class="token-row">
            <button id="generate-token-btn" class="btn btn-primary" type="button">Generate 1-hour token</button>
            <input id="generated-token" class="token-output" type="text" placeholder="Click generate token" readonly>
            <button id="copy-token-btn" class="btn" type="button">Copy</button>
        </div>
        <p id="token-status" class="help">Use this token in Postman: Authorization -> Bearer Token.</p>
        <p id="token-countdown" class="help">Token not generated yet.</p>
    </div>
</main>
<script>
    document.querySelectorAll('.nav-group-header[data-nav-target]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-nav-target');
            var body = document.getElementById(id);
            if (!body) return;
            var open = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', open ? 'false' : 'true');
            body.hidden = open;
        });
    });
    document.querySelectorAll('.nav-subgroup-header[data-nav-target]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-nav-target');
            var body = document.getElementById(id);
            if (!body) return;
            var open = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', open ? 'false' : 'true');
            body.hidden = open;
        });
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const generateTokenBtn = document.getElementById('generate-token-btn');
    const copyTokenBtn = document.getElementById('copy-token-btn');
    const generatedTokenInput = document.getElementById('generated-token');
    const tokenStatus = document.getElementById('token-status');
    const tokenCountdown = document.getElementById('token-countdown');
    let countdownTimer = null;

    const clearCountdown = () => {
        if (!countdownTimer) return;
        clearInterval(countdownTimer);
        countdownTimer = null;
    };

    const formatRemaining = (secondsLeft) => {
        const hours = Math.floor(secondsLeft / 3600);
        const minutes = Math.floor((secondsLeft % 3600) / 60);
        const seconds = Math.floor(secondsLeft % 60);
        return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    };

    const startCountdown = (expiresAtIso) => {
        clearCountdown();
        const expiresAtMs = new Date(expiresAtIso).getTime();
        if (Number.isNaN(expiresAtMs)) {
            tokenCountdown.textContent = 'Unable to read expiry time.';
            tokenCountdown.classList.remove('warn');
            return;
        }

        const tick = () => {
            const remainingMs = expiresAtMs - Date.now();
            if (remainingMs <= 0) {
                clearCountdown();
                tokenCountdown.textContent = 'Token expired. Generate a new token.';
                tokenCountdown.classList.add('warn');
                return;
            }

            const secondsLeft = Math.ceil(remainingMs / 1000);
            tokenCountdown.textContent = `Token expires in ${formatRemaining(secondsLeft)}.`;
            tokenCountdown.classList.toggle('warn', secondsLeft <= 300);
        };

        tick();
        countdownTimer = setInterval(tick, 1000);
    };

    generateTokenBtn.addEventListener('click', async () => {
        clearCountdown();
        tokenCountdown.textContent = 'Calculating expiry...';
        tokenCountdown.classList.remove('warn');
        tokenStatus.textContent = 'Generating token...';
        try {
            const response = await fetch("{{ route('settings.api-configuration.generate-token') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
            });

            const payload = await response.json().catch(() => ({}));
            if (!response.ok || !payload?.token) {
                throw new Error(payload?.message || 'Token generation failed.');
            }

            generatedTokenInput.value = payload.token;
            const expiresAt = payload.expires_at ? new Date(payload.expires_at).toLocaleString() : 'in 1 hour';
            tokenStatus.textContent = `Token generated. Expires at: ${expiresAt}`;
            startCountdown(payload.expires_at);
        } catch (error) {
            tokenStatus.textContent = error.message;
            tokenCountdown.textContent = 'Token not generated.';
            tokenCountdown.classList.remove('warn');
        }
    });

    copyTokenBtn.addEventListener('click', async () => {
        const value = generatedTokenInput.value.trim();
        if (!value) {
            tokenStatus.textContent = 'Generate a token first.';
            return;
        }

        try {
            await navigator.clipboard.writeText(value);
            tokenStatus.textContent = 'Token copied to clipboard.';
        } catch (error) {
            tokenStatus.textContent = 'Copy failed. Please copy manually.';
        }
    });
</script>
</body>
</html>
