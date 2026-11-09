<?php

use App\Http\Controllers\Hrm\ReportController;
use App\Http\Controllers\Hrm\AttendanceController;
use App\Http\Controllers\Hrm\CommonController;
use App\Http\Controllers\Hrm\DepartmentController;
use App\Http\Controllers\Hrm\DesignationController;
use App\Http\Controllers\Hrm\EmployeeController;
use App\Http\Controllers\Hrm\EmployeeMovementController;
use App\Http\Controllers\Hrm\EmployeeStatusController;
use App\Http\Controllers\Hrm\HolidayController;
use App\Http\Controllers\Hrm\HrmSettingController;
use App\Http\Controllers\Hrm\LeaveController;
use App\Http\Controllers\Hrm\LeaveTypeController;
use App\Http\Controllers\Hrm\ShiftController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\Hrm\DashboardController;

Route::middleware(['permission:hrm'])->group(function () {
    
    Route::get('hrm-dashboard', [DashboardController::class, 'index'])->name('hrm.dashboard');
    Route::resource('departments', DepartmentController::class)->middleware('permission:department');
    Route::get('departments-datatable', [DepartmentController::class, 'dataTable'])->name('departments.datatable')->middleware('permission:department');

    Route::resource('designations', DesignationController::class)->middleware('permission:designation');
    Route::get('designations-datatable', [DesignationController::class, 'dataTable'])->name('designations.datatable')->middleware('permission:designation');

    Route::resource('holidays', HolidayController::class)->middleware('permission:holiday');
    Route::get('holidays-datatable', [HolidayController::class, 'dataTable'])->name('holidays.datatable')->middleware('permission:holiday');

    Route::resource('shifts', ShiftController::class)->middleware('permission:shift');
    Route::get('shifts-datatable', [ShiftController::class, 'dataTable'])->name('shifts.datatable')->middleware('permission:shift');

    //Employee Status
    Route::resource('employee-statuses', EmployeeStatusController::class)->middleware('permission:employee_status');
    Route::get('employee-statuses-datatable', [EmployeeStatusController::class, 'dataTable'])->name('employee-statuses.datatable');


    Route::resource('employees', EmployeeController::class)->middleware('permission:employee');
    Route::get('employees-datatable', [EmployeeController::class, 'dataTable'])->name('employees.datatable')->middleware('permission:employee');
    Route::get('get-employee-address-details', [EmployeeController::class, 'getEmployeeAddressDetails'])->name('get_employee_address_details')->middleware('permission:employee');
    Route::get('get_designation',[CommonController::class, 'getDesignation'])->name('get_designation');

    Route::resource('leave-types', LeaveTypeController::class)->middleware('permission:leave_type');
    Route::get('leave-types-datatable', [LeaveTypeController::class, 'dataTable'])->name('leave-types.datatable')->middleware('permission:leave_type');

    Route::resource('leave', LeaveController::class)->middleware('permission:leave');
    Route::get('leave-datatable', [LeaveController::class, 'dataTable'])->name('leave.datatable')->middleware('permission:leave');

    Route::resource('employee-movements', EmployeeMovementController::class)->middleware('permission:employee_movement');
    Route::get('employee-movements-datatable', [EmployeeMovementController::class, 'dataTable'])->name('employee-movements.datatable')->middleware('permission:employee_movement');

    Route::get('employees-attendance', [EmployeeController::class, 'employeeAttendance'])->name('employees.attendance')->middleware('permission:employee_attendance');
    Route::post('employees-attendance', [EmployeeController::class, 'employeeAttendancePost'])->middleware('permission:employee_attendance');
    Route::get('employees-attendance/success', [EmployeeController::class, 'employeeAttendanceSuccess'])->name('employees.attendance.success')->middleware('permission:employee_attendance');

    Route::middleware('permission:hrm_reports')->group(function () {
        Route::get('report/attendance-sheet', [ReportController::class, 'attendanceSheet'])->name('report.attendance-sheet')->middleware('permission:attendance_sheet');
        Route::get('report/daily-attendance-sheet', [ReportController::class, 'dailyAttendanceSheet'])->name('report.daily-attendance-sheet')->middleware('permission:attendance_sheet');
        Route::get('report/attendance-summary', [ReportController::class, 'attendanceSummary'])->name('report.attendance-summary')->middleware('permission:attendance_summary');
        Route::get('report/attendance-reminder', [ReportController::class, 'reminderReport'])->name('report.attendance-reminder')->middleware('permission:attendance_reminder');
        Route::get('report/live-attendance', [ReportController::class, 'liveAttendanceReport'])->name('report.live_attendance')->middleware('permission:live_attendance');
        
        // HRM Reports
        Route::get('report/department', [ReportController::class, 'departmentReport'])->name('report.department')->middleware('permission:department_report');
        Route::get('report/designation', [ReportController::class, 'designationReport'])->name('report.designation')->middleware('permission:designation_report');
        Route::get('report/holiday', [ReportController::class, 'holidayReport'])->name('report.holiday')->middleware('permission:holiday_report');
        Route::get('report/employee', [ReportController::class, 'employeeReport'])->name('report.employee')->middleware('permission:employee_report');
    });

    // HRM Settings
    Route::get('hrm-settings', [HrmSettingController::class, 'index'])->name('hrm-settings.index')->middleware('permission:hrm_settings');
    Route::post('hrm-settings', [HrmSettingController::class, 'update'])->name('hrm-settings.update')->middleware('permission:hrm_settings');
});