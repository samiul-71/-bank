<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard &middot; {{ config('statement.institution') }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
        }
        header {
            background: #0f172a;
            color: #fff;
            padding: 14px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        header .who { font-size: 13px; color: #cbd5e1; }
        header form { margin: 0; }
        header button {
            background: #334155; color: #fff; border: 0;
            padding: 7px 12px; border-radius: 6px; font-size: 13px; cursor: pointer;
        }
        header button:hover { background: #475569; }
        main { max-width: 960px; margin: 26px auto; padding: 0 16px; }
        h2 { font-size: 18px; margin: 0 0 16px; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        th, td { text-align: left; padding: 11px 14px; font-size: 14px; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
        tr:last-child td { border-bottom: 0; }
        .btn { display: inline-block; padding: 6px 12px; border-radius: 6px; font-size: 13px; text-decoration: none; }
        .btn-view { background: #e0edff; color: #1d4ed8; }
        .btn-dl   { background: #dcfce7; color: #15803d; }
        .empty { padding: 40px; text-align: center; color: #64748b; }
    </style>
</head>
<body>
    <header>
        <strong>{{ config('statement.institution') }}</strong>
        <div>
            <span class="who">{{ auth()->user()->name }} ({{ auth()->user()->email }})</span>
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit">Log out</button>
            </form>
        </div>
    </header>

    <main>
        <h2>Account Statements</h2>

        <table>
            <thead>
                <tr>
                    <th>Account Name</th>
                    <th>A/C No.</th>
                    <th>Type</th>
                    <th>Txns</th>
                    <th style="text-align:right">Statement</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($accounts as $account)
                    <tr>
                        <td>{{ $account->name }}</td>
                        <td>{{ $account->account_no }}</td>
                        <td>{{ $account->account_type }}</td>
                        <td>{{ $account->transactions_count }}</td>
                        <td style="text-align:right; white-space:nowrap">
                            <a class="btn btn-view" target="_blank"
                               href="{{ route('statement.download', $account) }}">View PDF</a>
                            <a class="btn btn-dl"
                               href="{{ route('statement.download', ['account' => $account, 'download' => 1]) }}">Download</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">No accounts yet. Seed some with <code>php artisan db:seed --class=StatementSeeder</code>.</td></tr>
                @endforelse
            </tbody>
        </table>
    </main>
</body>
</html>
