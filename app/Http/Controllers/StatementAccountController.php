<?php

namespace App\Http\Controllers;

use App\Models\StatementAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatementAccountController extends Controller
{
    /** Show the "create account" form. */
    public function create(): View
    {
        return view('accounts.create');
    }

    /** Persist a new account (the header/meta shown atop the statement). */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'account_no'        => ['required', 'string', 'max:255', 'unique:statement_accounts,account_no'],
            'customer_id'       => ['nullable', 'string', 'max:255'],
            'prev_account_no'   => ['nullable', 'string', 'max:255'],
            'account_type'      => ['required', 'string', 'max:255'],
            'currency'          => ['required', 'string', 'max:8'],
            'status'            => ['required', 'string', 'max:255'],
            'joint_name'        => ['nullable', 'string', 'max:255'],
            'fhp'               => ['nullable', 'string', 'max:255'],
            'address'           => ['nullable', 'string', 'max:1000'],
            'city'              => ['nullable', 'string', 'max:255'],
            'phone'             => ['nullable', 'string', 'max:255'],
            'opening_balance'   => ['nullable', 'numeric'],
            'uncleared_balance' => ['nullable', 'numeric'],
        ]);

        $data['opening_balance']   = $data['opening_balance']   ?? 0;
        $data['uncleared_balance'] = $data['uncleared_balance'] ?? 0;

        $account = StatementAccount::create($data);

        return redirect()
            ->route('accounts.import.create', $account)
            ->with('status', "Account created. Now import its transactions from Excel.");
    }
}
