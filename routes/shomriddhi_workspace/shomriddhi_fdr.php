<?php

use App\Http\Controllers\ShomriddhiFdr\EncashmentRequestController;
use App\Http\Controllers\ShomriddhiFdr\FdrAccountController;
use App\Http\Controllers\ShomriddhiFdr\FdrDashboardController;
use App\Http\Controllers\ShomriddhiFdr\FdrSchemeController;
use App\Http\Controllers\ShomriddhiFdr\FdrTransactionController;
use App\Http\Controllers\ShomriddhiFdr\ReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('shomriddhi-fdr')->name('shomriddhi-fdr.')->middleware('permission:shomriddhi_fdr')->group(function () {

    Route::get('/', [FdrDashboardController::class, 'index'])->name('dashboard')->middleware('permission:shomriddhi_fdr_dashboard');

    // Scheme Management
    Route::get('schemes/datatable', [FdrSchemeController::class, 'dataTable'])->name('schemes.datatable')->middleware('permission:shomriddhi_fdr_schemes');
    Route::resource('schemes', FdrSchemeController::class)->except(['show'])->middleware('permission:shomriddhi_fdr_schemes');
    Route::post('schemes/{scheme}/toggle-status', [FdrSchemeController::class, 'toggleStatus'])->name('schemes.toggle_status')->middleware('permission:shomriddhi_fdr_scheme_edit');

    Route::post('schemes/{scheme}/toggle-visibility', [FdrSchemeController::class, 'toggleVisibility'])->name('schemes.visibility')->middleware('permission:shomriddhi_fdr_scheme_edit');
    Route::get('encashment-requests/datatable', [EncashmentRequestController::class, 'dataTable'])->name('encashment-requests.datatable')->middleware('permission:shomriddhi_fdr_accounts');
    Route::get('encashment-requests', [EncashmentRequestController::class, 'index'])->name('encashment-requests')->middleware('permission:shomriddhi_fdr_accounts');
    Route::post('encashment-requests/{id}/review', [EncashmentRequestController::class, 'review'])->name('encashment-requests.review')->middleware('permission:shomriddhi_fdr_account_approve');
    // Accounts Management
    Route::get('accounts/datatable', [FdrAccountController::class, 'dataTable'])->name('accounts.datatable')->middleware('permission:shomriddhi_fdr_accounts');
    Route::resource('accounts', FdrAccountController::class)->middleware('permission:shomriddhi_fdr_accounts');
    Route::post('accounts/{account}/approve', [FdrAccountController::class, 'approve'])->name('accounts.approve')->middleware('permission:shomriddhi_fdr_account_approve');
    Route::get('accounts/{account}/encash-data', [FdrAccountController::class, 'encashData'])->name('accounts.encash_data')->middleware('permission:shomriddhi_fdr_accounts');
    Route::post('accounts/{account}/encash', [FdrAccountController::class, 'encash'])->name('accounts.encash')->middleware('permission:shomriddhi_fdr_account_approve');

    // Transactions Management
    Route::get('transactions/datatable', [FdrTransactionController::class, 'dataTable'])->name('transactions.datatable')->middleware('permission:shomriddhi_fdr_accounts');
    Route::get('transactions', [FdrTransactionController::class, 'index'])->name('transactions.index')->middleware('permission:shomriddhi_fdr_accounts');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('maturity', [ReportController::class, 'maturity'])->name('maturity')->middleware('permission:shomriddhi_fdr_reports');
        Route::get('transaction', [ReportController::class, 'transaction'])->name('transaction')->middleware('permission:shomriddhi_fdr_reports');
        Route::get('ledger', [ReportController::class, 'ledger'])->name('ledger')->middleware('permission:shomriddhi_fdr_reports');
        Route::get('active-accounts', [ReportController::class, 'activeAccounts'])->name('active-accounts')->middleware('permission:shomriddhi_fdr_reports');
        Route::get('closed-accounts', [ReportController::class, 'closedAccounts'])->name('closed-accounts')->middleware('permission:shomriddhi_fdr_reports');
        Route::get('scheme-summary', [ReportController::class, 'schemeSummary'])->name('scheme-summary')->middleware('permission:shomriddhi_fdr_reports');
    });
});
