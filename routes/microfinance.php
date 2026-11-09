<?php

use App\Http\Controllers\Microfinance\DashboardController;
use App\Http\Controllers\Microfinance\DayClosingController;
use App\Http\Controllers\Microfinance\FieldOfficerAssignmentController;
use App\Http\Controllers\Microfinance\FieldOfficerController;
use App\Http\Controllers\Microfinance\MemberController;
use App\Http\Controllers\Microfinance\SomitiController;
use App\Http\Controllers\Microfinance\SavingsProductController;
use App\Http\Controllers\Microfinance\SavingsAccountController;
use App\Http\Controllers\Microfinance\LoanProductController;
use App\Http\Controllers\Microfinance\LoanController;
use App\Http\Controllers\Microfinance\CollectionController;
use App\Http\Controllers\Microfinance\ReportController;
use App\Http\Controllers\Microfinance\OpeningBalanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('microfinance')->name('microfinance.')->middleware(['permission:microfinance'])->group(function () {

    // ── Dashboard ─────────────────────────────────────────────────
    Route::get('microfinance/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // ── Members ───────────────────────────────────────────────────
    Route::get('member/print-empty-form', [MemberController::class, 'printEmptyForm'])->name('member.print_empty_form')->middleware('permission:mf_member');
    Route::get('member/get-nominees/{id}', [MemberController::class, 'getNominees'])->name('member.get_nominees')->middleware('permission:mf_member');
    Route::get('member-datatable', [MemberController::class, 'dataTable'])->name('member.datatable')->middleware('permission:mf_member');
    Route::post('member/{member}/toggle-status', [MemberController::class, 'toggleStatus'])->name('member.toggle_status')->middleware('permission:mf_member');
    Route::post('member/{member}/transfer', [MemberController::class, 'transfer'])->name('member.transfer')->middleware('permission:mf_member');
    Route::resource('member', MemberController::class)->middleware('permission:mf_member');

    // ── Somitis ───────────────────────────────────────────────────
    Route::resource('somiti', SomitiController::class)->middleware('permission:mf_somiti');

    // ── Field Officers ─────────────────────────────────────────────
    Route::get('field-officer-datatable', [FieldOfficerController::class, 'dataTable'])->name('field-officer.datatable')->middleware('permission:mf_field_officer');
    Route::resource('field-officer', FieldOfficerController::class)->only(['index', 'show'])->middleware('permission:mf_field_officer');
    
    // Field Officer Assignments & Bulk Transfers
    Route::get('field-officer-assignment', [FieldOfficerAssignmentController::class, 'index'])->name('field-officer-assignment.index')->middleware('permission:mf_field_officer');
    Route::get('field-officer-assignment/get-somitis', [FieldOfficerAssignmentController::class, 'getSomitis'])->name('field-officer-assignment.get-somitis')->middleware('permission:mf_field_officer');
    Route::post('field-officer-assignment/transfer', [FieldOfficerAssignmentController::class, 'transfer'])->name('field-officer-assignment.transfer')->middleware('permission:mf_field_officer');

    // ── Savings Products ──────────────────────────────────────────
    Route::get('savings-product-datatable', [SavingsProductController::class, 'dataTable'])->name('savings-product.datatable')->middleware('permission:mf_savings_product');
    Route::get('savings-product/get-next-code', [SavingsProductController::class, 'getNextProductCode'])->name('savings-product.get-next-code')->middleware('permission:mf_savings_product');
    Route::resource('savings-product', SavingsProductController::class)->middleware('permission:mf_savings_product');

    // ── Savings Accounts ──────────────────────────────────────────
    Route::get('savings-account/print-empty-dps-form', [SavingsAccountController::class, 'printEmptyDpsForm'])->name('savings-account.print_empty_dps_form')->middleware('permission:mf_savings_account');
    Route::get('savings-account/print-empty-fdr-form', [SavingsAccountController::class, 'printEmptyFdrForm'])->name('savings-account.print_empty_fdr_form')->middleware('permission:mf_savings_account');
    Route::get('savings-account/get-product-info/{id}', [SavingsAccountController::class, 'getProductInfo'])->name('savings-account.get_product_info')->middleware('permission:mf_savings_account');
    Route::get('savings-account/get-member-info/{id}', [SavingsAccountController::class, 'getMemberInfo'])->name('savings-account.get_member_info')->middleware('permission:mf_savings_account');
    Route::get('savings-account-datatable', [SavingsAccountController::class, 'dataTable'])->name('savings-account.datatable')->middleware('permission:mf_savings_account');
    Route::post('savings-account/{savingsAccount}/approve', [SavingsAccountController::class, 'approve'])->name('savings-account.approve')->middleware('permission:mf_savings_account');
    Route::post('savings-account/{savingsAccount}/withdraw', [SavingsAccountController::class, 'withdraw'])->name('savings-account.withdraw')->middleware('permission:mf_savings_account');
    Route::post('savings-account/{savingsAccount}/post-interest', [SavingsAccountController::class, 'postInterest'])->name('savings-account.post_interest')->middleware('permission:mf_savings_account');
    Route::post('savings-account/{savingsAccount}/close', [SavingsAccountController::class, 'closeAccount'])->name('savings-account.close')->middleware('permission:mf_savings_account');
    Route::post('savings-account/store-nominee', [SavingsAccountController::class, 'storeNominee'])->name('savings-account.store-nominee')->middleware('permission:mf_savings_account');
    Route::post('savings-account/{savingsAccount}/renew', [SavingsAccountController::class, 'renew'])->name('savings-account.renew')->middleware('permission:mf_savings_account');
    Route::resource('savings-account', SavingsAccountController::class)->middleware('permission:mf_savings_account');

    // ── Loan Products ─────────────────────────────────────────────
    Route::get('loan-product/get-next-code', [LoanProductController::class, 'getNextProductCode'])->name('loan-product.get-next-code')->middleware('permission:mf_loan_product');
    Route::get('loan-product-datatable', [LoanProductController::class, 'dataTable'])->name('loan-product.datatable')->middleware('permission:mf_loan_product');
    Route::resource('loan-product', LoanProductController::class)->middleware('permission:mf_loan_product');

    // ── Loans ─────────────────────────────────────────────────────
    Route::get('loan/print-empty-form', [LoanController::class, 'printEmptyForm'])->name('loan.print_empty_form')->middleware('permission:mf_loan');
    Route::get('loan-datatable', [LoanController::class, 'dataTable'])->name('loan.datatable')->middleware('permission:mf_loan');
    Route::post('loan/{loan}/approve', [LoanController::class, 'approve'])->name('loan.approve')->middleware('permission:mf_loan');
    Route::post('loan/{loan}/disburse', [LoanController::class, 'disburse'])->name('loan.disburse')->middleware('permission:mf_loan');
    Route::post('loan/{loan}/reject', [LoanController::class, 'reject'])->name('loan.reject')->middleware('permission:mf_loan');
    Route::post('loan/{loan}/close', [LoanController::class, 'close'])->name('loan.close')->middleware('permission:mf_loan');
    Route::get('loan/{loan}/repayment-schedule', [LoanController::class, 'repaymentSchedule'])->name('loan.repayment_schedule')->middleware('permission:mf_loan');
    Route::resource('loan', LoanController::class)->middleware('permission:mf_loan');

    // ── Collection ────────────────────────────────────────────────
    Route::get('collection', [CollectionController::class, 'index'])->name('collection.index')->middleware('permission:mf_collection|mf_collection_view');
    Route::get('collection-datatable', [CollectionController::class, 'dueInstallmentsDataTable'])->name('collection.datatable')->middleware('permission:mf_collection|mf_collection_view');
    Route::post('collection/collect', [CollectionController::class, 'collect'])->name('collection.collect')->middleware('permission:mf_collection|mf_collection_view');

    // Collection Sheet (Unified)
    Route::get('collection-sheet', [CollectionController::class, 'sheet'])->name('collection.sheet')->middleware('permission:mf_collection_sheet');
    Route::get('collection-sheet/fetch', [CollectionController::class, 'fetchSheet'])->name('collection.fetch_sheet')->middleware('permission:mf_collection_sheet');
    Route::post('collection-sheet/save', [CollectionController::class, 'saveSheet'])->name('collection.save_sheet')->middleware('permission:mf_collection_sheet');

    // ── Reports ───────────────────────────────────────────────────
    Route::get('report/member-ledger', [ReportController::class, 'memberLedger'])->name('report.member-ledger')->middleware('permission:mf_report_member_ledger');
    Route::get('report/collection-sheet', [ReportController::class, 'collectionSheet'])->name('report.collection-sheet')->middleware('permission:mf_report_collection_sheet');
    Route::get('report/loan-portfolio', [ReportController::class, 'loanPortfolio'])->name('report.loan-portfolio')->middleware('permission:mf_report_loan_portfolio');
    Route::get('report/savings-portfolio', [ReportController::class, 'savingsPortfolio'])->name('report.savings-portfolio')->middleware('permission:mf_report_savings_portfolio');
    Route::get('report/due-overdue', [ReportController::class, 'dueOverdue'])->name('report.due-overdue')->middleware('permission:mf_report_due_overdue');
    Route::get('report/loan-disbursement', [ReportController::class, 'loanDisbursement'])->name('report.loan-disbursement')->middleware('permission:mf_report_loan_disbursement');

    // ── Day Closing ────────────────────────────────────────────────
    Route::get('day-closing', [DayClosingController::class, 'index'])->name('day-closing.index')->middleware('permission:mf_day_closing');
    Route::post('day-closing', [DayClosingController::class, 'store'])->name('day-closing.store')->middleware('permission:mf_day_closing');

    // ── Opening Balance (Migration) ────────────────────────────────
    Route::get('opening-balance/general-savings', [OpeningBalanceController::class, 'generalSavingsForm'])->name('opening-balance.general-savings')->middleware('permission:mf_opening_balance');
    Route::post('opening-balance/general-savings', [OpeningBalanceController::class, 'storeGeneralSavings'])->name('opening-balance.general-savings.store')->middleware('permission:mf_opening_balance');
    Route::get('opening-balance/dps', [OpeningBalanceController::class, 'dpsForm'])->name('opening-balance.dps')->middleware('permission:mf_opening_balance');
    Route::post('opening-balance/dps', [OpeningBalanceController::class, 'storeDps'])->name('opening-balance.dps.store')->middleware('permission:mf_opening_balance');
    Route::get('opening-balance/fdr', [OpeningBalanceController::class, 'fdrForm'])->name('opening-balance.fdr')->middleware('permission:mf_opening_balance');
    Route::post('opening-balance/fdr', [OpeningBalanceController::class, 'storeFdr'])->name('opening-balance.fdr.store')->middleware('permission:mf_opening_balance');
    Route::get('opening-balance/loan', [OpeningBalanceController::class, 'loanForm'])->name('opening-balance.loan')->middleware('permission:mf_opening_balance');
    Route::post('opening-balance/loan', [OpeningBalanceController::class, 'storeLoan'])->name('opening-balance.loan.store')->middleware('permission:mf_opening_balance');
});

