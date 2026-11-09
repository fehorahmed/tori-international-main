<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContractorManagement\ContractorTypeController;
use App\Http\Controllers\ContractorManagement\ContractorController;
use App\Http\Controllers\ContractorManagement\DashboardController;
use App\Http\Controllers\ContractorManagement\ExpenseHeadController;
use App\Http\Controllers\ContractorManagement\ContractorBillController;
use App\Http\Controllers\ContractorManagement\ContractorPaymentController;
use App\Http\Controllers\ContractorManagement\ReportController;

Route::middleware(['check_head_office', 'permission:contractor_management'])->prefix('contractor-management')->name('contractor_management.')->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Contractor Types
    Route::get('types/datatable', [ContractorTypeController::class, 'datatable'])->name('types.datatable');
    Route::resource('types', ContractorTypeController::class)->except(['show']);

    // Contractors
    Route::get('contractors/address-details', [ContractorController::class, 'getAddressDetails'])->name('contractors.address_details');
    Route::get('contractors/datatable', [ContractorController::class, 'datatable'])->name('contractors.datatable');
    Route::resource('contractors', ContractorController::class);

    // Expense Heads
    Route::get('expense-heads/datatable', [ExpenseHeadController::class, 'dataTable'])->name('expense-heads.datatable');
    Route::resource('expense-heads', ExpenseHeadController::class)->except(['show']);

    // Income Heads
    Route::get('income-heads/datatable', [App\Http\Controllers\ContractorManagement\IncomeHeadController::class, 'dataTable'])->name('income-heads.datatable');
    Route::resource('income-heads', App\Http\Controllers\ContractorManagement\IncomeHeadController::class)->except(['show']);

    // Contractor Bills
    Route::get('bills/datatable', [ContractorBillController::class, 'dataTable'])->name('bills.datatable');
    Route::resource('bills', ContractorBillController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

    // Contractor Payments
    Route::get('payments/datatable', [ContractorPaymentController::class, 'dataTable'])->name('payments.datatable');
    Route::resource('payments', ContractorPaymentController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

    // Reports
    Route::match(['get', 'post'], 'reports/income-heads-list', [ReportController::class, 'incomeHeadsList'])->name('reports.income_heads_list');
    Route::match(['get', 'post'], 'reports/expense-heads-list', [ReportController::class, 'expenseHeadsList'])->name('reports.expense_heads_list');
    Route::match(['get', 'post'], 'reports/expense-heads', [ReportController::class, 'expenseHeads'])->name('reports.expense_heads');
    Route::match(['get', 'post'], 'reports/ledger', [ReportController::class, 'ledger'])->name('reports.ledger');
    Route::match(['get', 'post'], 'reports/outstanding', [ReportController::class, 'outstanding'])->name('reports.outstanding');
    Route::match(['get', 'post'], 'reports/bill-summary', [ReportController::class, 'billSummary'])->name('reports.bill_summary');
});
