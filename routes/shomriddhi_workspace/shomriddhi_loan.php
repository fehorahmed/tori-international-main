<?php

use App\Http\Controllers\ShomriddhiLoan\LoanAccountController;
use App\Http\Controllers\ShomriddhiLoan\LoanDashboardController;
use App\Http\Controllers\ShomriddhiLoan\LoanTransactionController;
use App\Http\Controllers\ShomriddhiLoan\ReportController;
use App\Http\Controllers\ShomriddhiLoan\LoanRescheduleController;
use App\Http\Controllers\ShomriddhiLoan\ProductLoanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin/shomriddhi-loan')->name('shomriddhi-loan.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [LoanDashboardController::class, 'index'])->name('dashboard');
    
    // Loan Accounts
    Route::get('accounts/datatable', [LoanAccountController::class, 'dataTable'])->name('accounts.datatable');
    Route::resource('accounts', LoanAccountController::class);
    Route::post('accounts/{id}/approve', [LoanAccountController::class, 'approve'])->name('accounts.approve');
    Route::get('accounts/{id}/schedule', [LoanAccountController::class, 'schedule'])->name('accounts.schedule');
    
    // Loan Collections / Transactions
    Route::get('collections/datatable', [LoanTransactionController::class, 'dataTable'])->name('collections.datatable');
    Route::get('collections/create/{account_id?}', [LoanTransactionController::class, 'create'])->name('collections.create');
    Route::post('collections/store', [LoanTransactionController::class, 'store'])->name('collections.store');
    Route::get('collections/history', [LoanTransactionController::class, 'index'])->name('collections.index');
    Route::get('collections/{id}', [LoanTransactionController::class, 'show'])->name('collections.show');

    // Reports
    Route::get('reports/loan-ledger', [ReportController::class, 'loanLedger'])->name('reports.loan_ledger');
    Route::get('reports/collection-summary', [ReportController::class, 'collectionSummary'])->name('reports.collection_summary');
    Route::get('reports/overdue-installments', [ReportController::class, 'overdueInstallments'])->name('reports.overdue_installments');

    // Reschedule / Holiday Shift
    Route::get('reschedule', [LoanRescheduleController::class, 'index'])->name('reschedule.index');
    Route::post('reschedule/process', [LoanRescheduleController::class, 'process'])->name('reschedule.process');

    // EMI Sales (Migrated from Sales) -> Now Product Loan
    Route::middleware('permission:product_loans')->group(function () {
        Route::get('product-loans/datatable', [ProductLoanController::class, 'dataTable'])->name('product-loans.datatable');
        Route::get('product-loans/{emi_sale}/print', [ProductLoanController::class, 'printInvoice'])->name('product-loans.print');
        Route::post('product-loans/installment/{schedule}/pay', [ProductLoanController::class, 'payInstallment'])->name('product-loans.installment.pay');
        Route::resource('product-loans', ProductLoanController::class);
    });

});
