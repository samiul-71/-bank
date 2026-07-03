<?php

namespace App\Http\Controllers;

use App\Models\StatementAccount;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $accounts = StatementAccount::query()
            ->withCount('transactions')
            ->orderBy('name')
            ->get();

        return view('dashboard', compact('accounts'));
    }
}
