<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShomriddhiDps\DpsDashboardController;
use App\Http\Controllers\ShomriddhiDps\DpsSchemeController;
use App\Http\Controllers\ShomriddhiDps\DpsAccountController;
use App\Http\Controllers\ShomriddhiDps\DpsTransactionController;
use App\Http\Controllers\ShomriddhiDps\ReportController;

Route::prefix('shomriddhi-dps')->name('shomriddhi-dps.')->group(function () {

    Route::middleware('permission:shomriddhi_dps_dashboard')->group(function () {
        Route::get('/', [DpsDashboardController::class, 'index'])->name('dashboard');
    });

    // Scheme Management
    Route::middleware('permission:shomriddhi_dps_schemes')->group(function () {
        Route::get('schemes/datatable', [DpsSchemeController::class, 'dataTable'])->name('schemes.datatable');
        Route::resource('schemes', DpsSchemeController::class)->except(['show']);
        Route::post('schemes/{scheme}/toggle-status', [DpsSchemeController::class, 'toggleStatus'])->name('schemes.toggle_status');
    });

    // Accounts Management
    Route::middleware('permission:shomriddhi_dps_accounts')->group(function () {
        Route::get('accounts/datatable', [DpsAccountController::class, 'dataTable'])->name('accounts.datatable');
        Route::resource('accounts', DpsAccountController::class);
        Route::post('accounts/{account}/approve', [DpsAccountController::class, 'approve'])->name('accounts.approve');
        Route::get('accounts/{account}/installment-data', [DpsAccountController::class, 'installmentData'])->name('accounts.installment_data');
        Route::get('accounts/{account}/encash-data', [DpsAccountController::class, 'encashData'])->name('accounts.encash_data');
        Route::post('accounts/{account}/encash', [DpsAccountController::class, 'encash'])->name('accounts.encash');
    });

    // Installment Payment / Transactions
    // (We will use accounts permission for paying installment, but ideally a transaction permission)
    Route::middleware('permission:shomriddhi_dps_accounts')->group(function () {
        Route::get('transactions/datatable', [DpsTransactionController::class, 'dataTable'])->name('transactions.datatable');
        Route::get('transactions', [DpsTransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/collect', [DpsTransactionController::class, 'create'])->name('transactions.create');
        Route::post('transactions/{transaction}/approve', [DpsTransactionController::class, 'approve'])->name('transactions.approve');
        Route::get('transactions/{transaction}/print-voucher', [DpsTransactionController::class, 'printVoucher'])->name('transactions.print_voucher');
        Route::post('accounts/{account}/pay-installment', [DpsTransactionController::class, 'payInstallment'])->name('accounts.pay_installment');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->middleware('permission:shomriddhi_dps_reports')->group(function () {
        Route::get('active-accounts', [ReportController::class, 'activeAccounts'])->name('active-accounts');
        Route::get('matured-accounts', [ReportController::class, 'maturedAccounts'])->name('matured-accounts');
        Route::get('closed-accounts', [ReportController::class, 'closedAccounts'])->name('closed-accounts');
        Route::get('defaulted-accounts', [ReportController::class, 'defaultedAccounts'])->name('defaulted-accounts');
        Route::get('transaction', [ReportController::class, 'transaction'])->name('transaction');
        Route::get('ledger', [ReportController::class, 'ledger'])->name('ledger');
        Route::get('scheme-summary', [ReportController::class, 'schemeSummary'])->name('scheme-summary');
    });
});
