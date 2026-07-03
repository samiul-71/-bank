<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in &middot; {{ config('statement.institution') }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #0f172a;
            color: #0f172a;
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: #fff;
            width: 100%;
            max-width: 380px;
            border-radius: 12px;
            padding: 32px 28px;
            box-shadow: 0 20px 50px rgba(0,0,0,.35);
        }
        .brand { text-align: center; margin-bottom: 22px; }
        .brand h1 { font-size: 18px; margin: 0 0 4px; }
        .brand p  { font-size: 12px; color: #64748b; margin: 0; }
        label { display: block; font-size: 13px; font-weight: 600; margin: 14px 0 6px; }
        input[type=email], input[type=password] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
        }
        input:focus { outline: 2px solid #2563eb; border-color: #2563eb; }
        .row { display: flex; align-items: center; gap: 6px; margin-top: 14px; font-size: 13px; color: #475569; }
        button {
            width: 100%;
            margin-top: 20px;
            padding: 11px;
            background: #2563eb;
            color: #fff;
            border: 0;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover { background: #1d4ed8; }
        .error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            font-size: 13px;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            <h1>{{ config('statement.institution') }}</h1>
            <p>Staff sign in</p>
        </div>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            <label class="row">
                <input type="checkbox" name="remember" value="1"> Remember me
            </label>

            <button type="submit">Sign in</button>
        </form>
    </div>
</body>
</html>
