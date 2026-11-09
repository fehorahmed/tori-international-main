<?php

use App\Http\Controllers\UserManagement\ReportController;
use App\Http\Controllers\UserManagement\UserController; 
use App\Http\Controllers\UserManagement\RoleController;
use App\Http\Controllers\UserManagement\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['check_head_office','permission:user_management'])->group(function () {
    
    Route::get('user-management/dashboard', [DashboardController::class, 'index'])->name('user_management.dashboard');
    
    Route::get('user-datatable', [UserController::class, 'dataTable'])->name('user.datatable');

    Route::resource('role', RoleController::class)->middleware('permission:role_management');
    Route::get('role-datatable', [RoleController::class, 'dataTable'])->name('role.datatable')->middleware('permission:role_management');

    Route::get('user-permission/{user}', [UserController::class, 'permission'])->name('user.permission')->middleware('permission:user_permission_manage');
    Route::post('user-permission/{user}', [UserController::class, 'permissionUpdate'])->name('user.permission.update')->middleware('permission:user_permission_manage');

    Route::post('generate-otp/{user}', [UserController::class, 'generateOtp'])->name('user.generate.otp');
    Route::post('user-tracking-status/{user}', [UserController::class, 'trackingStatusUpdate'])->name('user.tracking.status');

    Route::middleware('permission:user_management_reports')->group(function () {
        Route::get('user/report', [ReportController::class, 'userReport'])->name('user.report')->middleware('permission:users_report');
    });

    Route::resource('user', UserController::class)->middleware('permission:users');
});