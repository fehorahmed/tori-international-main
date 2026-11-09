<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShomriddhiWorkspace\DashboardController;

Route::get('shomriddhi-apps', [DashboardController::class, 'index'])->name('shomriddhi.gateway');

require __DIR__ . '/shomriddhi_dps.php';
require __DIR__ . '/shomriddhi_fdr.php';
require __DIR__ . '/shomriddhi_loan.php';
require __DIR__ . '/shomriddhi_advance.php';
require __DIR__ . '/card_management.php';
