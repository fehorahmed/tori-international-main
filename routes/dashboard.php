<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:dashboard');
Route::get('global-search', [DashboardController::class, 'globalSearch'])->name('global.search');

Route::prefix('performance-dashboard')->group(function () {
    Route::get('/sr', [\App\Http\Controllers\PerformanceDashboardController::class, 'srPerformance'])->name('performance.sr');
    Route::get('/branch-manager', [\App\Http\Controllers\PerformanceDashboardController::class, 'branchManagerPerformance'])->name('performance.bm');
    Route::get('/area-manager', [\App\Http\Controllers\PerformanceDashboardController::class, 'areaManagerPerformance'])->name('performance.am');
});
