<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PurchaseWorkspace\DashboardController;

Route::middleware(['auth'])->group(function () {
    Route::get('/purchase-workspace', [DashboardController::class, 'index'])->name('purchase_workspace.dashboard');
});

// Purchase Workspace Sub-modules
require __DIR__ . '/purchase.php';
require __DIR__ . '/inventory.php';
require __DIR__ . '/product_utilize.php';
require __DIR__ . '/stock_transfer.php';
require __DIR__ . '/stock_conversion.php';
