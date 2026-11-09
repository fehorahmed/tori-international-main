<?php


use App\Http\Controllers\CommonController;

use App\Http\Controllers\Crm\CustomerTypeController;
use App\Http\Controllers\Crm\LeadController;
use App\Http\Controllers\Crm\MarketController;
use App\Http\Controllers\Crm\ReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Crm\DashboardController;

Route::middleware(['check_head_office','permission:crm'])->group(function () {

    Route::get('crm-dashboard', [DashboardController::class, 'index'])->name('crm.dashboard');
    Route::resource('customer-types', CustomerTypeController::class)->middleware('permission:customer_type');
    Route::get('customer-types-datatable', [CustomerTypeController::class, 'dataTable'])->name('customer-types.datatable');

    Route::resource('leads', LeadController::class)->except(['edit', 'update']);
    Route::get('leads-datatable', [LeadController::class, 'dataTable'])->name('leads.datatable');
    Route::post('leads/feedback', [LeadController::class, 'feedback'])->name('leads.feedback');
    Route::get('leads/nearby-clients', [LeadController::class, 'getNearbyClients'])->name('leads.nearby_clients');
    Route::get('leads/{lead}/client-info', [LeadController::class, 'getClientInfo'])->name('leads.client_info');
    Route::post('leads/convert', [LeadController::class, 'convert'])->name('leads.convert');

    // Manage Markets
    Route::middleware('permission:manage_markets')->group(function () {
        Route::get('markets-datatable', [MarketController::class, 'dataTable'])->name('markets.datatable');
        Route::resource('markets', MarketController::class)->except(['show']);
    });
    
    Route::middleware('permission:crm_reports')->group(function () {
        Route::get('report/lead', [ReportController::class, 'leadsReport'])->name('report.leads')->middleware('permission:leads_report');
        Route::get('report/outlet-types', [ReportController::class, 'outletTypeReport'])->name('report.outlet_types')->middleware('permission:leads_report');
        Route::get('report/markets', [ReportController::class, 'marketReport'])->name('report.markets')->middleware('permission:leads_report');
    });
});

Route::get('get-districts', [CommonController::class, 'getDistricts'])->name('get_districts');
Route::get('get-upazilas', [CommonController::class, 'getUpazilas'])->name('get_upazilas');
Route::get('get-unions', [CommonController::class, 'getUnions'])->name('get_unions');
