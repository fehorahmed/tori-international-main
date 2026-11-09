<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShomriddhiFdr\FdrDashboardController;
use App\Http\Controllers\ShomriddhiFdr\FdrSchemeController;
use App\Http\Controllers\ShomriddhiFdr\FdrAccountController;
use App\Http\Controllers\ShomriddhiFdr\FdrTransactionController;
use App\Http\Controllers\ShomriddhiFdr\ReportController;

Route::prefix('shomriddhi-fdr')->name('shomriddhi-fdr.')->group(function () {
    
    Route::get('/', [FdrDashboardController::class, 'index'])->name('dashboard');

    // Scheme Management
    Route::get('schemes/datatable', [FdrSchemeController::class, 'dataTable'])->name('schemes.datatable');
    Route::resource('schemes', FdrSchemeController::class)->except(['show']);
    Route::post('schemes/{scheme}/toggle-status', [FdrSchemeController::class, 'toggleStatus'])->name('schemes.toggle_status');

    // Accounts Management
    Route::get('accounts/datatable', [FdrAccountController::class, 'dataTable'])->name('accounts.datatable');
    Route::resource('accounts', FdrAccountController::class);
    Route::post('accounts/{account}/approve', [FdrAccountController::class, 'approve'])->name('accounts.approve');
    Route::get('accounts/{account}/encash-data', [FdrAccountController::class, 'encashData'])->name('accounts.encash_data');
    Route::post('accounts/{account}/encash', [FdrAccountController::class, 'encash'])->name('accounts.encash');

    // Transactions Management
    Route::get('transactions/datatable', [FdrTransactionController::class, 'dataTable'])->name('transactions.datatable');
    Route::get('transactions', [FdrTransactionController::class, 'index'])->name('transactions.index');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('maturity', [ReportController::class, 'maturity'])->name('maturity');
        Route::get('transaction', [ReportController::class, 'transaction'])->name('transaction');
        Route::get('ledger', [ReportController::class, 'ledger'])->name('ledger');
        Route::get('active-accounts', [ReportController::class, 'activeAccounts'])->name('active-accounts');
        Route::get('closed-accounts', [ReportController::class, 'closedAccounts'])->name('closed-accounts');
        Route::get('scheme-summary', [ReportController::class, 'schemeSummary'])->name('scheme-summary');
    });
});
