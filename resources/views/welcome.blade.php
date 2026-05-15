<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login - Tanseeq Web App</title>
    <style>
        :root {
            --brand-blue: #0f2f57;
            --brand-gold: #b88a1d;
            --card-bg: rgba(10, 19, 30, 0.72);
            --card-border: rgba(255, 255, 255, 0.24);
        }
        * { box-sizing: border-box; }
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            color: #fff;
            display: grid;
            place-items: center;
            position: relative;
            background: radial-gradient(circle at 12% 12%, #1b3858 0%, #11253c 45%, #0b1828 100%);
        }
        .login-container {
            position: relative;
            z-index: 1;
            width: min(440px, calc(100vw - 32px));
            padding: 26px 24px;
            border-radius: 16px;
            border: 1px solid var(--card-border);
            background: var(--card-bg);
            backdrop-filter: blur(4px);
            box-shadow: 0 20px 38px rgba(0, 0, 0, 0.35);
            text-align: center;
        }
        .brand-logo-wrap {
            width: 148px;
            height: 148px;
            margin: 0 auto 14px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.94);
            display: grid;
            place-items: center;
            padding: 10px;
        }
        .brand-logo-wrap img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            display: block;
        }
        h1 {
            margin: 0 0 8px;
            font-size: 30px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        p {
            margin: 0 0 22px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
        }
        .microsoft-btn {
            background: #fff;
            border: 1px solid #d1d5db;
            color: #111827;
            padding: 11px 14px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            border-radius: 10px;
            transition: transform .12s ease, box-shadow .12s ease;
        }
        .microsoft-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.18);
        }
        .ms-dot {
            width: 9px;
            height: 9px;
            border-radius: 2px;
            background: linear-gradient(45deg, var(--brand-blue), var(--brand-gold));
            display: inline-block;
        }
        .error {
            background: rgba(220, 38, 38, 0.18);
            border: 1px solid rgba(248, 113, 113, 0.45);
            color: #fee2e2;
            padding: 10px;
            margin-bottom: 16px;
            font-size: 13px;
            border-radius: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="brand-logo-wrap">
            <img src="{{ asset('images/companies/PS.jpg') }}" alt="Tanseeq logo">
        </div>

        <h1>Tanseeq Web App</h1>
        <p>Sign in with Microsoft to continue.</p>

        @if(session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        <a href="{{ route('auth.microsoft') }}" class="microsoft-btn">
            <span class="ms-dot" aria-hidden="true"></span>
            Sign in with Microsoft
        </a>
    </div>
</body>
</html>
