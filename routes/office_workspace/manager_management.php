<?php

use App\Http\Controllers\ManagerManagement\AreaManagerController;
use App\Http\Controllers\ManagerManagement\BranchManagerController;
use App\Http\Controllers\ManagerManagement\ManagerAssignmentController;
use App\Http\Controllers\ManagerManagement\DashboardController;
use App\Http\Controllers\ManagerManagement\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:manager_management'])->group(function () {
    Route::get('managers-dashboard', [DashboardController::class, 'index'])->name('manager_management.dashboard');

    Route::resource('area-manager', AreaManagerController::class)->only(['index', 'show'])->middleware('permission:area_manager');
    Route::get('area-manager-datatable', [AreaManagerController::class, 'dataTable'])->name('area-manager.datatable')->middleware('permission:area_manager');

    Route::resource('branch-manager', BranchManagerController::class)->only(['index', 'show'])->middleware('permission:branch_manager');
    Route::get('branch-manager-datatable', [BranchManagerController::class, 'dataTable'])->name('branch-manager.datatable')->middleware('permission:branch_manager');

    Route::get('area-manager-assignments', [ManagerAssignmentController::class, 'index'])->name('area-manager-assignments.index')->middleware('permission:area_manager_assignments');
    Route::get('branch-manager-assignments', [ManagerAssignmentController::class, 'index'])->name('branch-manager-assignments.index')->middleware('permission:branch_manager_assignments');

    Route::get('manager-assignments-datatable', [ManagerAssignmentController::class, 'dataTable'])->name('manager-assignments.datatable');
    Route::get('manager-assignments/create', [ManagerAssignmentController::class, 'create'])->name('manager-assignments.create');
    Route::post('manager-assignments', [ManagerAssignmentController::class, 'store'])->name('manager-assignments.store');
    Route::post('manager-assignments/release', [ManagerAssignmentController::class, 'release'])->name('manager-assignments.release');

    // Manager Reports
    Route::middleware('permission:manager_management_reports')->group(function () {
        Route::get('report/area-manager', [ReportController::class, 'areaManagerReport'])->name('report.area_manager')->middleware('permission:area_manager_list_report');
        Route::get('report/branch-manager', [ReportController::class, 'branchManagerReport'])->name('report.branch_manager')->middleware('permission:branch_manager_list_report');
        Route::get('report/area-manager-history', [ReportController::class, 'areaManagerHistoryReport'])->name('report.area_manager_history')->middleware('permission:area_manager_history_report');
        Route::get('report/branch-manager-history', [ReportController::class, 'branchManagerHistoryReport'])->name('report.branch_manager_history')->middleware('permission:branch_manager_history_report');
    });
});
