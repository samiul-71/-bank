<?php

use App\Http\Controllers\AccountStatementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StatementAccountController;
use App\Http\Controllers\TransactionImportController;
use Illuminate\Support\Facades\Route;

// Guests: login form + attempt.
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

// Authenticated staff only.
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Create a new account (statement header/meta).
    Route::get('/accounts/create', [StatementAccountController::class, 'create'])->name('accounts.create');
    Route::post('/accounts', [StatementAccountController::class, 'store'])->name('accounts.store');

    // Import transactions from a local Excel/CSV file into an account.
    Route::get('/import/sample-template', [TransactionImportController::class, 'sample'])->name('import.sample');
    Route::get('/accounts/{account}/import', [TransactionImportController::class, 'create'])->name('accounts.import.create');
    Route::post('/accounts/{account}/import', [TransactionImportController::class, 'store'])->name('accounts.import.store');

    // View the statement inline, or ?download=1 to save the file.
    Route::get('/accounts/{account}/statement', [AccountStatementController::class, 'download'])
        ->name('statement.download');
});
