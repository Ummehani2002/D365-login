<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login - D365 Form</title>
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f3f2f1;
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            color: #323130;
        }
        .login-container {
            background: #fff;
            border: 1px solid #edebe9;
            width: 380px;
            padding: 28px;
        }
        h1 {
            margin: 0 0 8px;
            font-size: 28px;
            font-weight: 600;
            color: #201f1e;
        }
        p {
            margin: 0 0 24px;
            color: #605e5c;
            font-size: 14px;
        }
        .microsoft-btn {
            background: #106ebe;
            border: 1px solid #106ebe;
            color: #fff;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            width: 100%;
            text-align: center;
        }
        .microsoft-btn:hover { background: #005a9e; }
        .error {
            background: #fde7e9;
            border: 1px solid #f3a6ad;
            color: #a4262c;
            padding: 10px;
            margin-bottom: 16px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    @include('partials.global-company-selector')
    <div class="login-container">
        <h1>D365 Form</h1>
        <p>Sign in with Microsoft account to continue.</p>

        @if(session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        <a href="{{ route('auth.microsoft') }}" class="microsoft-btn">Sign in with Microsoft</a>
    </div>
</body>
</html>



