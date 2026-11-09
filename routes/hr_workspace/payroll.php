<?php


use App\Http\Controllers\Payroll\SalaryProcessController;
use App\Http\Controllers\Payroll\SalaryReportController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;



Route::middleware(['permission:payroll'])->group(function () {
    Route::get('payroll-dashboard', [\App\Http\Controllers\Payroll\DashboardController::class, 'index'])->name('payroll.dashboard');

    Route::post('salary-processes/preview', [SalaryProcessController::class, 'preview'])->name('salary-processes.preview')->middleware('permission:payroll');
    Route::post('salary-processes/disburse', [SalaryProcessController::class, 'disburseSelected'])->name('salary-processes.disburse')->middleware('permission:salary_process_item_edit');
    Route::resource('salary-processes', SalaryProcessController::class)->middleware('permission:payroll');

    // Salary Structures
    Route::get('salary-structures/datatable', [\App\Http\Controllers\Payroll\SalaryStructureController::class, 'dataTable'])->name('salary-structures.datatable');
    Route::post('salary-structures/status/{salary_structure}', [\App\Http\Controllers\Payroll\SalaryStructureController::class, 'status'])->name('salary-structures.status');
    Route::resource('salary-structures', \App\Http\Controllers\Payroll\SalaryStructureController::class);

    // Employee Advance Summary
    Route::get('employee-advances/datatable', [\App\Http\Controllers\Payroll\EmployeeAdvanceController::class, 'dataTable'])->name('employee-advances.datatable');
    
    // Employee Advance Requests
    Route::get('employee-advances/requests', [\App\Http\Controllers\Payroll\EmployeeAdvanceController::class, 'requests'])->name('employee-advances.requests');
    Route::get('employee-advances/requests-datatable', [\App\Http\Controllers\Payroll\EmployeeAdvanceController::class, 'requestsDataTable'])->name('employee-advances.requests-datatable');
    
    Route::post('employee-advances/{employee_advance}/approve', [\App\Http\Controllers\Payroll\EmployeeAdvanceController::class, 'approve'])->name('employee-advances.approve');
    Route::post('employee-advances/{employee_advance}/reject', [\App\Http\Controllers\Payroll\EmployeeAdvanceController::class, 'reject'])->name('employee-advances.reject');
    Route::resource('employee-advances', \App\Http\Controllers\Payroll\EmployeeAdvanceController::class);

    Route::get('salary-processes/item-edit/{salary_process_item}', [SalaryProcessController::class, 'salaryItemEdit'])->name('salary-processes.item_edit')->middleware('permission:salary_process_item_edit');
    Route::get('salary-processes/item-view/{salary_process_item}', [SalaryProcessController::class, 'salaryItemView'])->name('salary-processes.item_view')->middleware('permission:salary_process_items');
    Route::get('salary-processes/item-history/{salary_process_item}', [SalaryProcessController::class, 'salaryItemHistory'])->name('salary-processes.item_history')->middleware('permission:salary_process_items');
    Route::post('salary-processes/item-update/{salary_process_item}', [SalaryProcessController::class, 'salaryItemUpdate'])->name('salary-processes.item_update')->middleware('permission:salary_process_item_edit');

    Route::get('salary-processes-datatable', [SalaryProcessController::class, 'dataTable'])->name('salary-processes.datatable')->middleware('permission:salary_process_lists');
    Route::get('salary-processes-details/{salary_process?}', [SalaryProcessController::class, 'details'])->name('salary-processes.details')->middleware('permission:salary_process_items');
    Route::get('salary-processes-details-datatable/{salary_process?}', [SalaryProcessController::class, 'detailsDataTable'])->name('salary-processes.details.datatable')->middleware('permission:salary_process_items');
    Route::delete('salary-processes-details-destroy/{salary_process_item}', [SalaryProcessController::class, 'detailsItemDestroy'])->name('salary-processes.details.destroy')->middleware('permission:salary_process_item_delete');

    Route::middleware(['permission:payroll_reports'])->group(function () {
        Route::get('report/salary-sheet', [SalaryReportController::class, 'salarySheet'])->name('report.salary-sheet')->middleware('permission:salary_sheet');
        Route::get('report/employee-advance', [SalaryReportController::class, 'employeeAdvanceReport'])->name('report.employee-advance');
    });
});
