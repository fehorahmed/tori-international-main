<?php

use App\Http\Controllers\FormsHub\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('forms-hub')->name('forms_hub.')->middleware(['permission:forms_hub'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Forms
    Route::get('/customer', [DashboardController::class, 'customerForm'])->name('customer');
    Route::get('/supplier', [DashboardController::class, 'supplierForm'])->name('supplier');
    Route::get('/dealer', [DashboardController::class, 'dealerForm'])->name('dealer');
    
    Route::get('/dps', [DashboardController::class, 'dpsForm'])->name('dps');
    Route::get('/fdr', [DashboardController::class, 'fdrForm'])->name('fdr');
    
    Route::get('/shomriddhi', [DashboardController::class, 'shomriddhiIndex'])->name('shomriddhi.index');
    Route::get('/shomriddhi/generate-print', [DashboardController::class, 'shomriddhiGeneratePrint'])->name('shomriddhi.generate_print');
    Route::get('/shomriddhi/{id}/print', [DashboardController::class, 'shomriddhiPrint'])->name('shomriddhi.print');
});
