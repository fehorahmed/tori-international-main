<?php

use App\Http\Controllers\RoutePlan\DashboardController;

use App\Http\Controllers\RoutePlan\ReportController;
use App\Http\Controllers\RoutePlan\RoutePlanController;
use Illuminate\Support\Facades\Route;

// Route Plan Module
Route::middleware(['auth', 'permission:route_plan_management'])->group(function () {

    // Dashboard
    Route::get('market-management/dashboard', [DashboardController::class, 'index'])->name('route_plan.dashboard');



    // SR Route Plans
    Route::middleware('permission:route_plans')->group(function () {
        Route::get('route-plans-datatable', [RoutePlanController::class, 'dataTable'])->name('route-plans.datatable');
        Route::get('route-plans/ajax-get-plans', [RoutePlanController::class, 'getSrPlans'])->name('route-plans.get-plans');
        Route::get('route-plans/ajax-view-pjp', [RoutePlanController::class, 'viewSrPjp'])->name('route-plans.view-pjp');
        Route::resource('route-plans', RoutePlanController::class)->except(['show']);
    });

    // Reports
    Route::middleware('permission:route_plan_reports')->group(function () {
        Route::get('reports/route-wise-leads', [ReportController::class, 'routeWiseLeads'])->name('report.route_leads');
        Route::get('reports/sr-route-coverage', [ReportController::class, 'routeCoverage'])->name('report.route_coverage');
        Route::get('reports/weekly-route-plans', [ReportController::class, 'routePlans'])->name('report.route_plans');
    });

});
