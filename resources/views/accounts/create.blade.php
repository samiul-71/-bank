<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Account &middot; {{ config('statement.institution') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background: #f1f5f9; color: #0f172a; }
        header { background: #0f172a; color: #fff; padding: 14px 22px; }
        header a { color: #cbd5e1; text-decoration: none; font-size: 13px; }
        main { max-width: 760px; margin: 26px auto; padding: 0 16px; }
        h2 { font-size: 18px; margin: 0 0 16px; }
        form { background: #fff; padding: 22px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 9px 11px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; font-family: inherit; }
        .full { grid-column: 1 / -1; }
        button { margin-top: 18px; padding: 11px 20px; background: #2563eb; color: #fff; border: 0; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
        button:hover { background: #1d4ed8; }
        .error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-size: 13px; padding: 10px 12px; border-radius: 8px; margin-bottom: 14px; }
        .error ul { margin: 4px 0 0; padding-left: 18px; }
    </style>
</head>
<body>
    <header><a href="{{ route('dashboard') }}">&larr; Back to dashboard</a></header>
    <main>
        <h2>New Account</h2>

        @if ($errors->any())
            <div class="error"><strong>Please fix:</strong>
                <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('accounts.store') }}">
            @csrf
            <div class="grid">
                <div class="full">
                    <label>Account Name *</label>
                    <input name="name" value="{{ old('name') }}" required>
                </div>
                <div>
                    <label>A/C No. *</label>
                    <input name="account_no" value="{{ old('account_no') }}" required>
                </div>
                <div>
                    <label>Prev. A/C No.</label>
                    <input name="prev_account_no" value="{{ old('prev_account_no') }}">
                </div>
                <div>
                    <label>Customer ID</label>
                    <input name="customer_id" value="{{ old('customer_id') }}">
                </div>
                <div>
                    <label>A/C Type *</label>
                    <select name="account_type">
                        <option value="Current" @selected(old('account_type')==='Current')>Current</option>
                        <option value="Savings" @selected(old('account_type')==='Savings')>Savings</option>
                    </select>
                </div>
                <div>
                    <label>Currency *</label>
                    <input name="currency" value="{{ old('currency', 'BDT') }}" required>
                </div>
                <div>
                    <label>A/C Status *</label>
                    <input name="status" value="{{ old('status', 'Active') }}" required>
                </div>
                <div>
                    <label>Joint Name</label>
                    <input name="joint_name" value="{{ old('joint_name') }}">
                </div>
                <div>
                    <label>F/H/P</label>
                    <input name="fhp" value="{{ old('fhp') }}">
                </div>
                <div class="full">
                    <label>Address</label>
                    <textarea name="address" rows="2">{{ old('address') }}</textarea>
                </div>
                <div>
                    <label>City</label>
                    <input name="city" value="{{ old('city') }}">
                </div>
                <div>
                    <label>Phone</label>
                    <input name="phone" value="{{ old('phone') }}">
                </div>
                <div>
                    <label>Opening Balance</label>
                    <input name="opening_balance" type="number" step="0.01" value="{{ old('opening_balance', '0') }}">
                    <small style="color:#64748b">Auto-set if your Excel starts with a balance row.</small>
                </div>
                <div>
                    <label>Uncleared/ Floating Balance</label>
                    <input name="uncleared_balance" type="number" step="0.01" value="{{ old('uncleared_balance', '0') }}">
                </div>
            </div>
            <button type="submit">Create &amp; continue to import</button>
        </form>
    </main>
</body>
</html>
