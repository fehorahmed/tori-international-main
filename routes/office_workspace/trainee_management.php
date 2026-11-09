<?php

use App\Http\Controllers\TraineeManagement\TraineeController;
use App\Http\Controllers\TraineeManagement\TraineeTaskController;
use App\Http\Controllers\TraineeManagement\TraineeVisitController;
use App\Http\Controllers\TraineeManagement\TraineeVisitTypeController;
use App\Http\Controllers\TraineeManagement\ReportController;
use App\Http\Controllers\TraineeManagement\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:trainee_management'])->group(function () {
    
    Route::get('trainee-management/dashboard', [DashboardController::class, 'index'])->name('trainee_management.dashboard');
    
    Route::middleware('permission:trainee')->group(function () {
        Route::get('trainee/datatable', [TraineeController::class, 'dataTable'])->name('trainee.datatable');
    });

    // Trainee Task Management
    Route::prefix('trainee/tasks')->middleware('permission:trainee_tasks')->group(function () {
        Route::get('/', [TraineeTaskController::class, 'index'])->name('trainee.tasks.index');
        Route::get('datatable', [TraineeTaskController::class, 'dataTable'])->name('trainee.tasks.datatable');
        Route::get('distribute', [TraineeTaskController::class, 'distribute'])->name('trainee.tasks.distribute');
        Route::post('distribute', [TraineeTaskController::class, 'storeDistribute'])->name('trainee.tasks.store_distribute');
        Route::get('{id}/manage', [TraineeTaskController::class, 'manageTasks'])->name('trainee.tasks.manage');
        Route::post('{id}/manage', [TraineeTaskController::class, 'updateTasks'])->name('trainee.tasks.update_tasks');
        Route::get('{id}/show', [TraineeTaskController::class, 'show'])->name('trainee.tasks.show');
    });

    // Trainee Visit Type Management (Admin)
    Route::prefix('trainee/visit-type')->middleware('permission:trainee_visits')->group(function () {
        Route::get('/', [TraineeVisitTypeController::class, 'index'])->name('trainee.visit_type.index');
        Route::get('datatable', [TraineeVisitTypeController::class, 'dataTable'])->name('trainee.visit_type.datatable');
        Route::get('create', [TraineeVisitTypeController::class, 'create'])->name('trainee.visit_type.create');
        Route::post('store', [TraineeVisitTypeController::class, 'store'])->name('trainee.visit_type.store');
        Route::get('{traineeVisitType}/edit', [TraineeVisitTypeController::class, 'edit'])->name('trainee.visit_type.edit');
        Route::put('{traineeVisitType}/update', [TraineeVisitTypeController::class, 'update'])->name('trainee.visit_type.update');
    });

    // Trainee Visit Log Management
    Route::prefix('trainee/visits')->middleware('permission:trainee_visits')->group(function () {
        Route::get('/', [TraineeVisitController::class, 'index'])->name('trainee.visits.index');
        Route::get('datatable', [TraineeVisitController::class, 'dataTable'])->name('trainee.visits.datatable');
        Route::get('create', [TraineeVisitController::class, 'create'])->name('trainee.visits.create');
        Route::post('store', [TraineeVisitController::class, 'store'])->name('trainee.visits.store');
        Route::get('{visit}', [TraineeVisitController::class, 'show'])->name('trainee.visits.show');
    });

    // Trainee Reports
    Route::middleware('permission:trainee_reports')->group(function () {
        Route::get('report/trainee-visits', [ReportController::class, 'traineeVisitsReport'])->name('report.trainee_visits');
        Route::get('report/trainee-performance', [ReportController::class, 'traineePerformanceReport'])->name('report.trainee_performance');
        Route::get('report/trainee-performance-summary', [ReportController::class, 'traineePerformanceSummary'])->name('report.trainee_performance_summary');
    });

    Route::resource('trainee', TraineeController::class)->only(['index', 'show'])->middleware('permission:trainee');
});
