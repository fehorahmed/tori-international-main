<?php

use App\Http\Controllers\SrManagement\DashboardController;
use App\Http\Controllers\SrManagement\ReportController;
use App\Http\Controllers\SrManagement\SrController;
use App\Http\Controllers\SrManagement\SRVisitController;
use App\Http\Controllers\SrManagement\SrLeadCollectionController;
use App\Http\Controllers\SrManagement\SrRoutePlanController;
use Illuminate\Support\Facades\Route;

// SR Management Module
Route::middleware(['auth', 'permission:sr_management'])->group(function () {
    
    // Dashboard
    Route::get('sr-management/dashboard', [DashboardController::class, 'index'])->name('sr_management.dashboard');
    Route::post('sr-management/dashboard/checkin', [DashboardController::class, 'checkin'])->name('sr_management.dashboard.checkin');

    // SR Section
    Route::get('sr-datatable', [SrController::class, 'dataTable'])->name('sr.datatable');
    Route::resource('sr', SrController::class)->middleware('permission:sr');

    // SR Visits Section
    Route::get('sr-visits-datatable', [SRVisitController::class, 'dataTable'])->name('sr-visits.datatable');
    Route::resource('sr-visits', SRVisitController::class)->middleware('permission:sr_visits');
    
    // SR Route Plans
    Route::middleware('permission:sr_route_plan')->group(function () {
        Route::get('sr-route-plans', [SrRoutePlanController::class, 'index'])->name('sr_route_plans.index');
        Route::get('sr-route-plans/datatable', [SrRoutePlanController::class, 'dataTable'])->name('sr_route_plans.datatable');
        Route::get('sr-route-plans/view-pjp', [SrRoutePlanController::class, 'viewSrPjp'])->name('sr_route_plans.view-pjp');
        Route::get('sr-route-plans/{market_id}/map', [SrRoutePlanController::class, 'mapView'])->name('sr_route_plans.map');
    });

    Route::get('get-districts/{divisionId}', [SRVisitController::class, 'getDistricts'])->name('get-districts');
    Route::get('get-upazilas/{districtId}', [SRVisitController::class, 'getUpazilas'])->name('get-upazilas');
    Route::get('get-unions/{upazilaId}', [SRVisitController::class, 'getUnions'])->name('get-unions');
    Route::get('get-active-roads/{srId}', [SRVisitController::class, 'getActiveRoads'])->name('get-active-roads');

    // Social Engagements
    Route::prefix('social-engagements')->group(function () {
        Route::get('/', [\App\Http\Controllers\SrManagement\SrSocialEngagementController::class, 'index'])->name('sr_social.index');
        Route::get('/datatable', [\App\Http\Controllers\SrManagement\SrSocialEngagementController::class, 'dataTable'])->name('sr_social.datatable');
        Route::get('/create', [\App\Http\Controllers\SrManagement\SrSocialEngagementController::class, 'create'])->name('sr_social.create');
        Route::post('/store', [\App\Http\Controllers\SrManagement\SrSocialEngagementController::class, 'store'])->name('sr_social.store');
    });

    // SR Lead Collection
    Route::prefix('sr-lead-collection')->middleware('permission:sr_lead_collection')->group(function () {
        Route::get('datatable', [SrLeadCollectionController::class, 'dataTable'])->name('sr_lead_collection.datatable');
        Route::get('nearby-clients', [SrLeadCollectionController::class, 'getNearbyClients'])->name('sr_lead_collection.nearby_clients');
        Route::post('feedback', [SrLeadCollectionController::class, 'feedback'])->name('sr_lead_collection.feedback');
    });
    Route::resource('sr-lead-collection', SrLeadCollectionController::class)->middleware('permission:sr_lead_collection')->names([
        'index' => 'sr_lead_collection.index',
        'create' => 'sr_lead_collection.create',
        'store' => 'sr_lead_collection.store',
        'edit' => 'sr_lead_collection.edit',
        'update' => 'sr_lead_collection.update',
        'destroy' => 'sr_lead_collection.destroy',
    ]);

    // SR Reports
    Route::middleware('permission:sr_reports')->group(function () {
        Route::get('report/sr-performance', [ReportController::class, 'srPerformanceReport'])->name('report.sr_performance');
        Route::get('report/sr-performance-summary', [ReportController::class, 'srPerformanceSummary'])->name('report.sr_performance_summary');
        Route::get('report/sr-tasks', [ReportController::class, 'srTasksReport'])->name('report.sr_tasks');
        Route::get('report/sr-visits', [ReportController::class, 'srVisitsReport'])->name('report.sr_visits');
        Route::get('report/sr-social-engagements', [ReportController::class, 'srSocialEngagementsReport'])->name('report.sr_social_engagements');
        Route::get('report/sr-leads', [ReportController::class, 'srLeadsReport'])->name('report.sr_leads');
    });

});
