<?php

use App\Http\Controllers\IncentiveManagement\IncentivePaymentController;
use App\Http\Controllers\IncentiveManagement\IncentiveSettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:incentive_management'])->group(function () {

    // ─── Settings ─────────────────────────────────────────────────
    Route::prefix('incentive-settings')->name('incentive-settings.')->group(function () {
        Route::get('/', [IncentiveSettingController::class, 'index'])->name('index');
        Route::get('/create', [IncentiveSettingController::class, 'create'])->name('create');
        Route::get('/datatable', [IncentiveSettingController::class, 'dataTable'])->name('datatable');
        Route::get('/global', [IncentiveSettingController::class, 'global'])->name('global');
        Route::post('/global-store', [IncentiveSettingController::class, 'globalStore'])->name('global-store');
        Route::post('/bulk-store', [IncentiveSettingController::class, 'bulkStore'])->name('bulk-store');
        Route::post('/toggle-status', [IncentiveSettingController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{incentiveSetting}/toggle-role', [IncentiveSettingController::class, 'toggleRole'])->name('toggle-role');
    });

    // ─── Payments ─────────────────────────────────────────────────
    Route::prefix('incentive-payments')->name('incentive-payments.')->group(function () {
        Route::get('/', [IncentivePaymentController::class, 'index'])->name('index');
        Route::post('/', [IncentivePaymentController::class, 'store'])->name('store');
        Route::get('/datatable', [IncentivePaymentController::class, 'dataTable'])->name('datatable');
        Route::get('/employee-balance', [IncentivePaymentController::class, 'getEmployeeBalance'])->name('employee-balance');
        Route::get('/employee-ledger', [IncentivePaymentController::class, 'employeeLedger'])->name('employee-ledger');
        Route::get('/ledger/{employee_id}', [IncentivePaymentController::class, 'employeeLedgerPage'])->name('ledger-page');
        Route::get('/ledger-datatable/{employee_id}', [IncentivePaymentController::class, 'ledgerDatatable'])->name('ledger-datatable');
    });
});
