<?php

use App\Http\Controllers\CompanyLoan\CompanyLoanController;
use App\Http\Controllers\CompanyLoan\LoanReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('company-loan')->name('company_loan.')->group(function () {
    // Dashboard
    Route::get('dashboard', [\App\Http\Controllers\CompanyLoan\DashboardController::class, 'index'])->name('dashboard');

    // Manage Company Loan (Index, Datatable)
    Route::middleware('permission:manage_company_loan')->group(function () {
        Route::get('/', [CompanyLoanController::class, 'index'])->name('index');
        Route::get('datatable', [CompanyLoanController::class, 'dataTable'])->name('datatable');
    });

    // Create Loan
    Route::middleware('permission:company_loan_create')->group(function () {
        Route::get('create', [CompanyLoanController::class, 'create'])->name('create');
        Route::post('store', [CompanyLoanController::class, 'store'])->name('store');
    });

    // Reports
    Route::get('reports/statement', [LoanReportController::class, 'loanStatement'])
        ->name('reports.statement')->middleware('permission:company_loan_report_statement');
    Route::get('reports/receivable-summary', [LoanReportController::class, 'loanReceivableSummary'])
        ->name('reports.receivable_summary')->middleware('permission:company_loan_report_receivable');
    Route::get('reports/payable-summary', [LoanReportController::class, 'loanPayableSummary'])
        ->name('reports.payable_summary')->middleware('permission:company_loan_report_payable');
    Route::get('reports/due-installments', [LoanReportController::class, 'dueInstallments'])
        ->name('reports.due_installments')->middleware('permission:company_loan_report_due_installments');

    // Payment
    Route::post('installment/{installment}/pay', [CompanyLoanController::class, 'payInstallment'])
        ->name('pay_installment')->middleware('permission:company_loan_payment');

    // Dynamic Parameter Routes (MUST be at the bottom to prevent 404s)
    Route::middleware('permission:manage_company_loan')->get('{loan}/show', [CompanyLoanController::class, 'show'])->name('show');
    
    Route::middleware('permission:company_loan_edit')->group(function () {
        Route::get('{loan}/edit', [CompanyLoanController::class, 'edit'])->name('edit');
        Route::post('{loan}/update', [CompanyLoanController::class, 'update'])->name('update');
    });

    Route::middleware('permission:company_loan_approve')->group(function () {
        Route::get('{loan}/approve', [CompanyLoanController::class, 'approvalPage'])->name('approval_page');
        Route::post('{loan}/approve', [CompanyLoanController::class, 'approve'])->name('approve');
    });
});
