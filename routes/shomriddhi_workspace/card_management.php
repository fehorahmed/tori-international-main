<?php

use App\Http\Controllers\CardManagement\AgentController;
use App\Http\Controllers\CardManagement\CardCollectionController;
use App\Http\Controllers\CardManagement\CardSettingController;
use App\Http\Controllers\CardManagement\CashoutController;
use App\Http\Controllers\CardManagement\DashboardController;
use App\Http\Controllers\CardManagement\EmployeeCardController;
use App\Http\Controllers\CardManagement\KycReviewController;
use App\Http\Controllers\CardManagement\MemberCardController;
use App\Http\Controllers\CardManagement\ReferralController;
use App\Http\Controllers\CardManagement\ReportController;
use App\Http\Controllers\CardManagement\TransactionSettingController;
use App\Http\Controllers\CardManagement\TransferController;
use Illuminate\Support\Facades\Route;

Route::prefix('card-management')->name('card-management.')->group(function () {
    Route::prefix('transaction-settings')->name('transaction-settings.')->controller(TransactionSettingController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('{group}', 'update')->whereIn('group', ['cashout', 'transfer', 'membership', 'membership_discount', 'referral', 'referral_milestones', 'transaction_window', 'agent_cashin', 'agent_cashout'])->name('update');
    });

    Route::get('agents', [AgentController::class, 'index'])->name('agents.index');
    Route::post('agents', [AgentController::class, 'store'])->name('agents.store');
    Route::get('agents/{id}/qr', [AgentController::class, 'qr'])->whereNumber('id')->name('agents.qr');
    Route::post('agents/{id}/status', [AgentController::class, 'status'])->whereNumber('id')->name('agents.status');
    Route::get('agent-transactions', [AgentController::class, 'transactions'])->name('agents.transactions');

    Route::get('referrals', [ReferralController::class, 'index'])->name('referrals.index');
    Route::post('referrals/{reward}/pay', [ReferralController::class, 'pay'])->whereNumber('reward')->name('referrals.pay');

    Route::prefix('transfers')->name('transfers.')->controller(TransferController::class)->group(function () {
        Route::get('/', 'index')->name('index');
    });

    Route::prefix('cashout')->name('cashout.')->controller(CashoutController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('booth', 'booth')->name('booth');
        Route::get('qr', 'qr')->name('qr');
        Route::post('{cashout}/{action}', 'change')->name('change');
    });

    Route::get('kyc', [KycReviewController::class, 'index'])->name('kyc.index');
    Route::get('kyc/{kyc}/documents/{kind}', [KycReviewController::class, 'document'])->name('kyc.document');
    Route::post('kyc/{kyc}/review', [KycReviewController::class, 'review'])->name('kyc.review');
    // Collection Group
    Route::prefix('collection')->name('collection.')->group(function () {
        Route::get('/', [CardCollectionController::class, 'index'])->name('index');
        Route::get('/datatable', [CardCollectionController::class, 'dataTable'])->name('datatable');
        Route::post('/receive', [CardCollectionController::class, 'receivePayment'])->name('receive');
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [CardSettingController::class, 'index'])->name('index');
        Route::post('/update', [CardSettingController::class, 'update'])->name('update');
    });

    // Member Cards Group
    Route::prefix('member')->name('member.')->group(function () {
        Route::get('/', [MemberCardController::class, 'index'])->name('index');
        Route::get('/datatable', [MemberCardController::class, 'dataTable'])->name('datatable');
        Route::get('/create', [MemberCardController::class, 'create'])->name('create');
        Route::post('/store', [MemberCardController::class, 'store'])->name('store');
        Route::get('/{card}/print-preview', [MemberCardController::class, 'printPreview'])->name('print_preview');
        Route::post('/{card}/log-print', [MemberCardController::class, 'logPrint'])->name('log_print');
        Route::post('/{card}/update-status', [MemberCardController::class, 'updateStatus'])->name('update_status');
        Route::delete('/{card}/destroy', [MemberCardController::class, 'destroy'])->name('destroy');
        Route::get('/logs', [MemberCardController::class, 'printLogs'])->name('logs');
        Route::get('/logs/datatable', [MemberCardController::class, 'printLogsDataTable'])->name('logs.datatable');
        Route::post('/bulk-log-print', [MemberCardController::class, 'bulkLogPrint'])->name('bulk_log_print');
        Route::get('/bulk-print-preview', [MemberCardController::class, 'bulkPrintPreview'])->name('bulk_print_preview');
    });

    // Employee Cards Group
    Route::prefix('employee')->name('employee.')->group(function () {
        Route::get('/', [EmployeeCardController::class, 'index'])->name('index');
        Route::get('/datatable', [EmployeeCardController::class, 'dataTable'])->name('datatable');
        Route::get('/create', [EmployeeCardController::class, 'create'])->name('create');
        Route::post('/store', [EmployeeCardController::class, 'store'])->name('store');
        Route::get('/{card}/print-preview', [EmployeeCardController::class, 'printPreview'])->name('print_preview');
        Route::post('/{card}/log-print', [EmployeeCardController::class, 'logPrint'])->name('log_print');
        Route::post('/{card}/update-status', [EmployeeCardController::class, 'updateStatus'])->name('update_status');
        Route::get('/logs', [EmployeeCardController::class, 'printLogs'])->name('logs');
        Route::get('/logs/datatable', [EmployeeCardController::class, 'printLogsDataTable'])->name('logs.datatable');
        Route::post('/bulk-log-print', [EmployeeCardController::class, 'bulkLogPrint'])->name('bulk_log_print');
        Route::get('/bulk-print-preview', [EmployeeCardController::class, 'bulkPrintPreview'])->name('bulk_print_preview');
    });

    // Reports Group
    Route::prefix('reports')->name('reports.')->group(function () {

        // Collection Report
        Route::get('/collection', [ReportController::class, 'collectionReport'])->name('collection');

        // Member Card Reports
        Route::prefix('member')->name('member.')->group(function () {
            Route::get('/status-summary', [ReportController::class, 'memberStatusSummary'])->name('status_summary');
            Route::get('/print-history', [ReportController::class, 'memberPrintHistory'])->name('print_history');
            Route::get('/lost-damaged', [ReportController::class, 'memberLostDamaged'])->name('lost_damaged');
        });

        // Employee Card Reports
        Route::prefix('employee')->name('employee.')->group(function () {
            Route::get('/status-summary', [ReportController::class, 'employeeStatusSummary'])->name('status_summary');
            Route::get('/print-history', [ReportController::class, 'employeePrintHistory'])->name('print_history');
            Route::get('/lost-damaged', [ReportController::class, 'employeeLostDamaged'])->name('lost_damaged');
        });
    });

});
