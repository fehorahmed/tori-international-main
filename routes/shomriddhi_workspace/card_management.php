<?php

use App\Http\Controllers\CardManagement\DashboardController;
use App\Http\Controllers\CardManagement\MemberCardController;
use App\Http\Controllers\CardManagement\EmployeeCardController;
use App\Http\Controllers\CardManagement\ReportController;
use App\Http\Controllers\CardManagement\CardSettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('card-management')->name('card-management.')->group(function () {
    // Collection Group
    Route::prefix('collection')->name('collection.')->group(function () {
        Route::get('/', [\App\Http\Controllers\CardManagement\CardCollectionController::class, 'index'])->name('index');
        Route::get('/datatable', [\App\Http\Controllers\CardManagement\CardCollectionController::class, 'dataTable'])->name('datatable');
        Route::post('/receive', [\App\Http\Controllers\CardManagement\CardCollectionController::class, 'receivePayment'])->name('receive');
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
