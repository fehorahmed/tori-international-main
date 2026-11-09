<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShomriddhiAdvance\AdvanceController;

use App\Http\Controllers\ShomriddhiAdvance\DashboardController;
use App\Http\Controllers\ShomriddhiAdvance\ReportController;
use App\Http\Controllers\ShomriddhiAdvance\SlabController;
use App\Http\Controllers\ShomriddhiAdvance\AccountController;
use App\Http\Controllers\ShomriddhiAdvance\PassbookController;
use App\Http\Controllers\ShomriddhiAdvance\GroupController;
use App\Http\Controllers\ShomriddhiAdvance\MemberController;
use App\Http\Controllers\ShomriddhiAdvance\FieldOfficerController;
use App\Http\Controllers\ShomriddhiAdvance\FieldOfficerAssignmentController;

Route::prefix('shomriddhi-advance')->name('shomriddhi-advance.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:shomriddhi_advance_dashboard');

    // Field Officers
    Route::get('/field-officers', [FieldOfficerController::class, 'index'])->name('field-officers.index');
    Route::get('/field-officers/datatable', [FieldOfficerController::class, 'dataTable'])->name('field-officers.datatable');

    // Field Officer Assignments (Bulk Transfer)
    Route::get('/field-officer-assignment', [FieldOfficerAssignmentController::class, 'index'])->name('field-officer-assignment.index');
    Route::get('/field-officer-assignment/get-groups', [FieldOfficerAssignmentController::class, 'getGroups'])->name('field-officer-assignment.get-groups');
    Route::post('/field-officer-assignment/transfer', [FieldOfficerAssignmentController::class, 'transfer'])->name('field-officer-assignment.transfer');

    // Groups
    Route::get('groups-datatable', [GroupController::class, 'dataTable'])->name('groups.datatable');
    Route::resource('groups', GroupController::class);

    // Members (Customers for this module)
    Route::get('members-datatable', [MemberController::class, 'dataTable'])->name('members.datatable');
    Route::resource('members', MemberController::class);

    // Deposit/Withdraw Entry
    Route::middleware('permission:shomriddhi_advance_transaction_create')->group(function () {
        Route::get('/create', [AdvanceController::class, 'create'])->name('create');
        Route::post('/store', [AdvanceController::class, 'store'])->name('store');
        Route::get('/customer-info', [AdvanceController::class, 'getCustomerInfo'])->name('customer_info');
    });

    // Pending Transactions List
    Route::middleware('permission:shomriddhi_advance_transaction_approve')->group(function () {
        Route::get('/', [AdvanceController::class, 'index'])->name('index');
        Route::get('/datatable', [AdvanceController::class, 'dataTable'])->name('datatable');
        Route::post('/{advance}/approve', [AdvanceController::class, 'approve'])->name('approve');
    });
    
    Route::post('/{advance}/reject', [AdvanceController::class, 'reject'])->name('reject')->middleware('permission:shomriddhi_advance_transaction_reject');
    Route::delete('/{advance}/delete', [AdvanceController::class, 'destroy'])->name('destroy');

    // Reports
    Route::prefix('reports')->name('reports.')->middleware('permission:shomriddhi_advance_reports')->group(function () {
        Route::get('/ledger', [ReportController::class, 'ledger'])->name('ledger')->middleware('permission:shomriddhi_advance_ledger_report');
        Route::get('/get-filtered-accounts', [ReportController::class, 'getFilteredAccounts'])->name('get_filtered_accounts')->middleware('permission:shomriddhi_advance_ledger_report');
        Route::get('/collection-sheet', [ReportController::class, 'collectionSheet'])->name('collection_sheet');
        Route::get('/summary', [ReportController::class, 'summary'])->name('summary')->middleware('permission:shomriddhi_advance_summary_report');
        Route::get('/summary/datatable', [ReportController::class, 'summaryDataTable'])->name('summary.datatable')->middleware('permission:shomriddhi_advance_summary_report');
        Route::get('/daily-interest', [ReportController::class, 'dailyInterestLogs'])->name('daily_interest')->middleware('permission:shomriddhi_advance_summary_report');
        Route::get('/daily-interest/datatable', [ReportController::class, 'dailyInterestLogsDataTable'])->name('daily_interest.datatable')->middleware('permission:shomriddhi_advance_summary_report');
    });

    // Interest Slabs Settings
    Route::get('/slabs', [SlabController::class, 'index'])->name('slabs.index');
    Route::post('/slabs/store', [SlabController::class, 'store'])->name('slabs.store');
    Route::post('/slabs/{slab}/update', [SlabController::class, 'update'])->name('slabs.update');
    Route::delete('/slabs/{slab}/delete', [SlabController::class, 'destroy'])->name('slabs.destroy');

    // Accounts List
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/datatable', [AccountController::class, 'dataTable'])->name('accounts.datatable');
    Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create')->middleware('permission:shomriddhi_advance_transaction_create');
    Route::post('/accounts/store', [AccountController::class, 'store'])->name('accounts.store')->middleware('permission:shomriddhi_advance_transaction_create');
    Route::get('/accounts/{account}/edit', [AccountController::class, 'edit'])->name('accounts.edit')->middleware('permission:shomriddhi_advance_transaction_create');
    Route::put('/accounts/{account}', [AccountController::class, 'update'])->name('accounts.update')->middleware('permission:shomriddhi_advance_transaction_create');
    Route::post('/accounts/store-nominee', [AccountController::class, 'storeNominee'])->name('accounts.store_nominee');
    Route::get('/accounts/customer-details', [AccountController::class, 'getCustomerDetails'])->name('accounts.customer_details');
    Route::get('/accounts/{account}/show', [AccountController::class, 'show'])->name('accounts.show');
    Route::get('/accounts/{account}/blank-passbook', [AccountController::class, 'blankPassbook'])->name('accounts.blank_passbook');
    
    // Passbook Management
    Route::get('/passbooks', [PassbookController::class, 'index'])->name('passbooks.index');
    Route::get('/passbooks/datatable', [PassbookController::class, 'dataTable'])->name('passbooks.datatable');
    Route::post('/passbooks/store', [PassbookController::class, 'store'])->name('passbooks.store');
    Route::post('/passbooks/{passbook}/status', [PassbookController::class, 'updateStatus'])->name('passbooks.update_status');
    
    // Accounts Opening Balance (Deprecated - Handled during creation)
    // Route::get('/accounts/opening-balance', [AccountController::class, 'openingBalance'])->name('accounts.opening_balance')->middleware('permission:shomriddhi_advance_transaction_create');
    // Route::post('/accounts/opening-balance/store', [AccountController::class, 'storeOpeningBalance'])->name('accounts.store_opening_balance')->middleware('permission:shomriddhi_advance_transaction_create');
});
