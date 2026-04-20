<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Settings - D365 Token</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            background: #f3f2f1;
            color: #323130;
        }
        .page {
            max-width: 980px;
            margin: 24px auto;
            padding: 0 16px;
        }
        .card {
            background: #fff;
            border: 1px solid #edebe9;
            border-radius: 4px;
            padding: 24px;
        }
        .card h2 {
            margin: 0 0 20px;
            color: #201f1e;
        }
        .token-head {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }
        .token-head h3 {
            margin: 0;
            font-size: 20px;
            color: #201f1e;
        }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 14px;
            font-size: 12px;
            font-weight: 600;
            background: #f3f2f1;
            color: #605e5c;
        }
        .badge-valid {
            background: #dff6dd;
            color: #107c10;
        }
        .badge-expired {
            background: #fde7e9;
            color: #a4262c;
        }
        .toolbar {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-bottom: 16px;
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
        .btn-primary:disabled {
            background: #c8c6c4;
            border-color: #c8c6c4;
            cursor: not-allowed;
        }
        .countdown-wrap {
            display: flex;
            justify-content: center;
            margin: 8px 0 16px;
        }
        .countdown-ring {
            position: relative;
            width: 150px;
            height: 150px;
        }
        .countdown-ring svg {
            transform: rotate(-90deg);
        }
        .ring-bg {
            fill: none;
            stroke: #edebe9;
            stroke-width: 8;
        }
        .ring-fg {
            fill: none;
            stroke: #005a9e;
            stroke-width: 8;
            stroke-linecap: round;
            transition: stroke-dashoffset 1s linear, stroke 0.3s;
        }
        .countdown-center {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .countdown-time {
            font-size: 38px;
            font-weight: 700;
            color: #201f1e;
            line-height: 1;
        }
        .countdown-label {
            margin-top: 2px;
            font-size: 12px;
            color: #8a8886;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .info-table tr {
            border-bottom: 1px solid #edebe9;
        }
        .info-table th,
        .info-table td {
            text-align: left;
            padding: 10px 8px;
            vertical-align: top;
            font-size: 14px;
        }
        .info-table th {
            width: 160px;
            color: #8a8886;
            font-weight: 600;
        }
        .duration-note {
            margin-top: 4px;
            font-size: 12px;
            color: #8a8886;
        }
        .token-box {
            font-family: Consolas, "Courier New", monospace;
            font-size: 11px;
            line-height: 1.5;
            border: 1px solid #edebe9;
            border-radius: 3px;
            padding: 10px;
            background: #f8f7f6;
            max-height: 72px;
            overflow: hidden;
            word-break: break-all;
            transition: max-height 0.2s;
        }
        .token-box.expanded {
            max-height: 320px;
            overflow-y: auto;
        }
        .token-actions {
            margin-top: 8px;
            display: flex;
            gap: 8px;
        }
        .btn-sm {
            border: 1px solid #8a8886;
            background: #fff;
            color: #323130;
            border-radius: 2px;
            padding: 4px 10px;
            font-size: 12px;
            cursor: pointer;
        }
        .alert {
            margin-top: 14px;
            padding: 10px 12px;
            border-radius: 2px;
            font-size: 13px;
        }
        .alert-success {
            background: #dff6dd;
            color: #107c10;
            border: 1px solid #9fd89f;
        }
        .alert-error {
            background: #fde7e9;
            color: #a4262c;
            border: 1px solid #f1707b;
        }
        .back {
            margin-top: 10px;
            display: inline-block;
            text-decoration: none;
            color: #106ebe;
        }
    </style>
</head>
<body>
<div class="page">
    <div class="card">
        <h2>Settings</h2>
        @php
            $totalSeconds = 3599;
            $remaining = $token ? $token->secondsRemaining() : 0;
            $durationSecs = $token ? (int) $token->created_at->diffInSeconds($token->expires_at) : 0;
            $durationMins = (int) round($durationSecs / 60);
        @endphp
        <div class="token-head">
            <h3>D365 Token</h3>
            @if($token && !$token->isExpired())
                <span class="badge badge-valid">Valid</span>
            @elseif($token && $token->isExpired())
                <span class="badge badge-expired">Expired</span>
            @else
                <span class="badge">No token</span>
            @endif
        </div>
        <div class="toolbar">
            <button id="generate-btn" class="btn btn-primary" type="button">Generate New Token</button>
        </div>

        <div class="countdown-wrap">
            <div class="countdown-ring">
                <svg width="150" height="150" viewBox="0 0 150 150">
                    <circle class="ring-bg" cx="75" cy="75" r="60"></circle>
                    <circle id="ring-fg" class="ring-fg" cx="75" cy="75" r="60"></circle>
                </svg>
                <div class="countdown-center">
                    <div id="countdown-time" class="countdown-time">{{ gmdate('H:i:s', max(0, $remaining)) }}</div>
                    <div class="countdown-label">remaining</div>
                </div>
            </div>
        </div>

        <table class="info-table">
            <tr>
                <th>Generated at</th>
                <td id="info-generated">{{ $token ? $token->created_at->format('d M Y H:i:s') : '—' }}</td>
            </tr>
            <tr>
                <th>Expires at</th>
                <td>
                    <div id="info-expires">{{ $token ? $token->expires_at->format('d M Y H:i:s') : '—' }}</div>
                    <div id="info-duration" class="duration-note">
                        @if($token)
                            Valid for {{ $durationMins }} min — Azure usually issues 3599 seconds tokens.
                        @endif
                    </div>
                </td>
            </tr>
            <tr>
                <th>Generated by</th>
                <td id="info-by">{{ $token->generated_by ?? '—' }}</td>
            </tr>
            <tr>
                <th>Full token</th>
                <td>
                    <div id="token-box" class="token-box">{{ $token?->access_token ?? '—' }}</div>
                    <div class="token-actions">
                        <button id="toggle-btn" type="button" class="btn-sm">Show full token</button>
                        <button id="copy-btn" type="button" class="btn-sm">Copy</button>
                    </div>
                </td>
            </tr>
        </table>

        <div id="alert-box" style="display:none;"></div>
        <a class="back" href="{{ route('dashboard') }}">Back to Dashboard</a>
    </div>
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const generateBtn = document.getElementById('generate-btn');
    const copyBtn = document.getElementById('copy-btn');
    const toggleBtn = document.getElementById('toggle-btn');
    const tokenBox = document.getElementById('token-box');
    const alertBox = document.getElementById('alert-box');
    const infoGenerated = document.getElementById('info-generated');
    const infoExpires = document.getElementById('info-expires');
    const infoBy = document.getElementById('info-by');
    const infoDuration = document.getElementById('info-duration');
    const countdownTime = document.getElementById('countdown-time');
    const ringFg = document.getElementById('ring-fg');
    const CIRC = 2 * Math.PI * 60;
    const TOTAL = {{ $totalSeconds }};
    let intervalId = null;

    ringFg.style.strokeDasharray = String(CIRC);

    const setAlert = (message, type = 'success') => {
        alertBox.className = `alert alert-${type}`;
        alertBox.textContent = message;
        alertBox.style.display = 'block';
    };

    const formatTime = (seconds) => {
        const safe = Math.max(0, Number(seconds) || 0);
        const h = String(Math.floor(safe / 3600)).padStart(2, '0');
        const m = String(Math.floor((safe % 3600) / 60)).padStart(2, '0');
        const s = String(safe % 60).padStart(2, '0');
        return `${h}:${m}:${s}`;
    };

    const setRing = (seconds) => {
        const ratio = Math.max(0, Math.min(1, seconds / TOTAL));
        ringFg.style.strokeDashoffset = String(CIRC * (1 - ratio));
        ringFg.style.stroke = seconds > 600 ? '#005a9e' : (seconds > 180 ? '#d83b01' : '#a4262c');
        countdownTime.textContent = formatTime(seconds);
    };

    const clearTicker = () => {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    };

    const startTicker = (seconds) => {
        clearTicker();
        let remaining = Number(seconds) || 0;
        const tick = () => {
            if (remaining <= 0) {
                clearTicker();
                setRing(0);
                return;
            }
            setRing(remaining);
            remaining -= 1;
        };
        tick();
        intervalId = setInterval(tick, 1000);
    };

    toggleBtn.addEventListener('click', () => {
        const expanded = tokenBox.classList.toggle('expanded');
        toggleBtn.textContent = expanded ? 'Collapse token' : 'Show full token';
    });

    generateBtn.addEventListener('click', async () => {
        generateBtn.disabled = true;
        alertBox.style.display = 'none';
        clearTicker();
        try {
            const response = await fetch("{{ route('settings.token.generate') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
            });
            const payload = await response.json().catch(() => ({}));
            if (!response.ok || payload?.status !== true) {
                throw new Error(payload?.message || 'Token generation failed.');
            }

            tokenBox.textContent = payload.full_token || '—';
            tokenBox.classList.remove('expanded');
            toggleBtn.textContent = 'Show full token';
            infoGenerated.textContent = payload.generated_at_human ?? '—';
            infoExpires.textContent = payload.expires_at_human ?? '—';
            infoBy.textContent = payload.generated_by ?? '—';
            infoDuration.textContent = `Valid for ${payload.duration_minutes ?? 60} min — Azure usually issues 3599 seconds tokens.`;
            startTicker(payload.seconds_remaining || 0);
            setAlert(`Token generated - valid for ${formatTime(payload.seconds_remaining || 0)}.`, 'success');
        } catch (error) {
            setAlert(error.message, 'error');
        } finally {
            generateBtn.disabled = false;
        }
    });

    copyBtn.addEventListener('click', async () => {
        const tokenValue = tokenBox.textContent.trim();
        if (!tokenValue || tokenValue === '—') {
            setAlert('No token to copy.', 'error');
            return;
        }
        try {
            await navigator.clipboard.writeText(tokenValue);
            setAlert('Token copied.', 'success');
        } catch (error) {
            setAlert('Copy failed.', 'error');
        }
    });

    @if($token)
    startTicker({{ $token->secondsRemaining() }});
    @else
    setRing(0);
    @endif
</script>
</body>
</html>
