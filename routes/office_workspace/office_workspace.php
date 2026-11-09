<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OfficeWorkspace\DashboardController;

Route::middleware(['auth'])->group(function () {
    Route::get('/office-workspace', [DashboardController::class, 'index'])->name('office_workspace.dashboard');
});

// Office Workspace Sub-modules
require __DIR__ . '/office_management.php';
require __DIR__ . '/user_management.php';
require __DIR__ . '/manager_management.php';
require __DIR__ . '/trainee_management.php';
require __DIR__ . '/company_loan.php';
require __DIR__ . '/sms_management.php';
require __DIR__ . '/forms_hub.php';
