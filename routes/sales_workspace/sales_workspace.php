<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalesWorkspace\DashboardController;

Route::middleware(['auth'])->group(function () {
    Route::get('/sales-workspace', [DashboardController::class, 'index'])->name('sales_workspace.dashboard');
});

// Sales Workspace Sub-modules
require __DIR__ . '/sales.php';
require __DIR__ . '/ecommerce.php';
