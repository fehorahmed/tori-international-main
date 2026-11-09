<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CrmWorkspace\DashboardController;

Route::middleware(['auth'])->group(function () {
    Route::get('/crm-workspace', [DashboardController::class, 'index'])->name('crm_workspace.dashboard');
});

// CRM Workspace Sub-modules
require __DIR__ . '/crm.php';
require __DIR__ . '/monitoring.php';
require __DIR__ . '/sr_management.php';
require __DIR__ . '/route_plan.php';
require __DIR__ . '/incentive_management.php';
