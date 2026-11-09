<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HrWorkspace\DashboardController;

Route::middleware(['auth'])->group(function () {
    Route::get('/hr-workspace', [DashboardController::class, 'index'])->name('hr_workspace.dashboard');
});

// HR Workspace Sub-modules
require __DIR__ . '/hrm.php';
require __DIR__ . '/payroll.php';
require __DIR__ . '/contractor_management.php';
