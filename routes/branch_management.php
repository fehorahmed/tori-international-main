<?php

use App\Http\Controllers\BranchManagement\FinancialReportController;
use App\Http\Controllers\BranchManagement\ReportController;
use App\Http\Controllers\BranchManagement\SisterConcernController;
use App\Http\Controllers\BranchManagement\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['check_head_office','permission:branch_management'])->group(function () {
    Route::get('branch-management/dashboard', [DashboardController::class, 'index'])->name('branch_management.dashboard');
    Route::get('sister-concerns-datatable', [SisterConcernController::class, 'dataTable'])->name('sister-concerns.datatable');
    Route::post('sister-concerns/update-business-date', [SisterConcernController::class, 'updateBusinessDate'])->name('sister-concerns.update-business-date');
    Route::get('sister-concerns/{sister_concern}/certificate', [SisterConcernController::class, 'certificate'])->name('sister-concerns.certificate');

    Route::middleware('permission:branch_management_reports')->group(function () {
        Route::get('sister-concerns/report', [ReportController::class, 'branchReport'])->name('sister-concerns.report')->middleware('permission:branch_report');
        Route::get('branch/employee-report', [ReportController::class, 'employeeReport'])->name('branch.employee_report')->middleware('permission:branch_report');
        
        // Financial Reports
        Route::get('branch/report/cash-and-bank-book', [FinancialReportController::class, 'cashBankBook'])->name('branch_report.cash_or_bank_book');
        Route::get('branch/report/receipt-and-payment', [FinancialReportController::class, 'receiptAndPayment'])->name('branch_report.receipt_and_payment');
        Route::get('branch/report/ledger', [FinancialReportController::class, 'ledger'])->name('branch_report.ledger');
        Route::get('branch/report/trial-balance', [FinancialReportController::class, 'trialBalance'])->name('branch_report.trial_balance');
        Route::get('branch/report/income-statement', [FinancialReportController::class, 'incomeStatement'])->name('branch_report.income_statement');
        Route::get('branch/report/daily-income-statement', [FinancialReportController::class, 'dailyIncomeStatement'])->name('branch_report.daily_income_statement');
        Route::get('branch/report/balance-sheet', [FinancialReportController::class, 'balanceSheet'])->name('branch_report.balance_sheet');
    });
    
    Route::resource('sister-concerns', SisterConcernController::class);
});
