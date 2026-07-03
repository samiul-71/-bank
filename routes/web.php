<?php

use App\Http\Controllers\AccountStatementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Guests: login form + attempt.
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

// Authenticated staff only.
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // View the statement inline, or ?download=1 to save the file.
    Route::get('/accounts/{account}/statement', [AccountStatementController::class, 'download'])
        ->name('statement.download');
});
