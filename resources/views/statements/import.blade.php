<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Import Transactions &middot; {{ config('statement.institution') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background: #f1f5f9; color: #0f172a; }
        header { background: #0f172a; color: #fff; padding: 14px 22px; }
        header a { color: #cbd5e1; text-decoration: none; font-size: 13px; }
        main { max-width: 720px; margin: 26px auto; padding: 0 16px; }
        h2 { font-size: 18px; margin: 0 0 4px; }
        .sub { color: #64748b; font-size: 14px; margin: 0 0 18px; }
        form { background: #fff; padding: 22px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
        input[type=file] { width: 100%; padding: 10px; border: 1px dashed #94a3b8; border-radius: 8px; background: #f8fafc; }
        .check { display: flex; align-items: center; gap: 8px; margin-top: 16px; font-size: 14px; }
        button { margin-top: 18px; padding: 11px 20px; background: #16a34a; color: #fff; border: 0; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
        button:hover { background: #15803d; }
        .sample-link { display: inline-block; margin-top: 8px; font-size: 13px; color: #1d4ed8; text-decoration: none; font-weight: 600; }
        .sample-link:hover { text-decoration: underline; }
        .error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-size: 13px; padding: 10px 12px; border-radius: 8px; margin-bottom: 14px; }
        .cols { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; font-size: 13px; padding: 12px 14px; border-radius: 8px; margin-bottom: 18px; }
        code { background: #dbeafe; padding: 1px 5px; border-radius: 4px; }
    </style>
</head>
<body>
    <header><a href="{{ route('dashboard') }}">&larr; Back to dashboard</a></header>
    <main>
        <h2>Import Transactions</h2>
        <p class="sub">{{ $account->name }} &middot; A/C {{ $account->account_no }}</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <div class="cols">
            Expected columns (header row): <code>Trans. Date</code> <code>Cheque#.</code>
            <code>Ref.</code> <code>Narration</code> <code>Trans. Details</code>
            <code>Debit</code> <code>Credit</code>.<br>
            The running <strong>Balance</strong> is calculated automatically, so you don't
            provide it — the opening balance comes from this account's settings
            (opening balance: <strong>{{ number_format($account->opening_balance, 2) }}</strong>).
            If your file still has a Balance column, that's fine — it's ignored.
            <br>
            <a class="sample-link" href="{{ route('import.sample') }}">&#8681; Download sample template (.xlsx)</a>
        </div>

        <form method="POST" action="{{ route('accounts.import.store', $account) }}" enctype="multipart/form-data">
            @csrf
            <label for="file">Excel / CSV file (.xlsx, .xls, .csv)</label>
            <input id="file" type="file" name="file" accept=".xlsx,.xls,.csv" required>

            <label class="check">
                <input type="checkbox" name="replace" value="1">
                Replace existing transactions on this account (otherwise append)
            </label>

            <button type="submit">Import</button>
        </form>
    </main>
</body>
</html>
