<?php
use App\Http\Controllers\Account\AccountGroupController;
use App\Http\Controllers\Account\AccountHeadController;
use App\Http\Controllers\Account\DashboardController;
use App\Http\Controllers\Account\InterCompanySettlementController;
use App\Http\Controllers\Account\ReceiptPaymentController;
use App\Http\Controllers\Account\ReportController;
use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Route;


Route::middleware('permission:accounts')->group(function () {
    // Dashboard
    Route::get('accounts-dashboard', [DashboardController::class, 'index'])->name('accounts.dashboard');

    //Account Group
    Route::resource('account-group', AccountGroupController::class)->middleware('permission:account_group');
    Route::get('account-group-datatable', [AccountGroupController::class, 'dataTable'])->name('account-group.datatable');

    //Account Head
    Route::resource('account-head', AccountHeadController::class);
    Route::get('account-head-datatable', [AccountHeadController::class, 'dataTable'])->name('account-head.datatable');

    //Voucher
    Route::resource('voucher', ReceiptPaymentController::class);
    Route::get('voucher-datatable', [ReceiptPaymentController::class, 'dataTable'])->name('voucher.datatable');
    Route::get('voucher-details/{voucher}', [ReceiptPaymentController::class, 'details'])->name('voucher.details');
    Route::get('voucher-print/{voucher}', [ReceiptPaymentController::class, 'print'])->name('voucher.print');
});

Route::middleware('permission:accounts_reports')->group(function () {

    //Report
    Route::get('report/cash-and-bank-book', [ReportController::class, 'cashBankBook'])->name('report.cash_or_bank_book')->middleware('permission:cash_book');
    Route::get('report/cash-and-bank-transaction-details', [ReportController::class, 'cashBankTransactionDetails'])->name('report.cash_or_bank_transaction_details')->middleware('permission:cash_book');
    Route::get('report/receipt-and-payment', [ReportController::class, 'receiptAndPayment'])->name('report.receipt_and_payment')->middleware('permission:receipt_and_payment');
});

Route::middleware('auth')->group(function () {

    Route::get('report/ledger', [ReportController::class, 'ledger'])->name('report.ledger')->middleware('permission:ledger');
    Route::get('report/trial-balance', [ReportController::class, 'trialBalance'])->name('report.trial_balance')->middleware('permission:trial_balance');
    Route::get('report/income-statement', [ReportController::class, 'incomeStatement'])->name('report.income_statement')->middleware('permission:income_statement');
    Route::get('report/balance-sheet', [ReportController::class, 'balanceSheet'])->name('report.balance_sheet')->middleware('permission:balance_sheet');
    Route::get('report/daily-transactions', [ReportController::class, 'dailyTransactions'])->name('report.daily_transactions')->middleware('permission:daily_transactions_report');
    Route::get('report/daily-transactions/details', [ReportController::class, 'dailyTransactionsDetails'])->name('report.daily_transactions.details')->middleware('permission:daily_transactions_report');

    // Inter-Company Ledger Report
    Route::get('report/inter-company-ledger', [ReportController::class, 'interCompanyLedger'])->name('report.inter_company_ledger');

    // Inter-Company Settlements (Payments & Receipts)
    Route::get('inter-company-settlements', [InterCompanySettlementController::class, 'index'])->name('inter_company_settlements.index');
    Route::get('inter-company-settlements/datatable', [InterCompanySettlementController::class, 'dataTable'])->name('inter_company_settlements.datatable');
    Route::get('inter-company-settlements/create', [InterCompanySettlementController::class, 'create'])->name('inter_company_settlements.create');
    Route::post('inter-company-settlements/store', [InterCompanySettlementController::class, 'store'])->name('inter_company_settlements.store');
    Route::get('inter-company-settlements/get-payable-balance', [InterCompanySettlementController::class, 'getPayableBalance'])->name('inter_company_settlements.get_payable_balance');
    Route::get('inter-company-settlements/receive-list', [InterCompanySettlementController::class, 'receiveList'])->name('inter_company_settlements.receive_list');
    Route::get('inter-company-settlements/receive-datatable', [InterCompanySettlementController::class, 'receiveDataTable'])->name('inter_company_settlements.receive_datatable');
    Route::post('inter-company-settlements/receive-store', [InterCompanySettlementController::class, 'receiveStore'])->name('inter_company_settlements.receive_store');
    Route::post('inter-company-settlements/reject', [InterCompanySettlementController::class, 'rejectStore'])->name('inter_company_settlements.reject');
    Route::post('inter-company-settlements/cancel/{id}', [InterCompanySettlementController::class, 'cancel'])->name('inter_company_settlements.cancel');

});

Route::get('get_sister_concern_wise_supplier', [CommonController::class, 'getSisterConcernWiseSupplier'])->name('get_sister_concern_wise_supplier');
Route::get('get_sister_concern_wise_customer', [CommonController::class, 'getSisterConcernWiseCustomer'])->name('get_sister_concern_wise_customer');
Route::get('get_sister_concern_wise_project', [CommonController::class, 'getSisterConcernWiseProject'])->name('get_sister_concern_wise_project');