<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountsWorkspace\DashboardController;

Route::middleware(['auth'])->group(function () {
    Route::get('/accounts-workspace', [DashboardController::class, 'index'])->name('accounts_workspace.dashboard');
});

// Accounts Workspace Sub-modules
require __DIR__ . '/accounts.php';
require __DIR__ . '/fixed_asset.php';
