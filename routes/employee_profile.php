<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeProfile\DashboardController;
use App\Http\Controllers\EmployeeProfile\ProfileController;
use App\Http\Controllers\EmployeeProfile\PayrollController;
use App\Http\Controllers\EmployeeProfile\LeaveController;

Route::prefix('employee-profile')->name('employee_profile.')->group(function () {
    // We will later add a specific middleware to ensure the user is an employee
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update-basic', [ProfileController::class, 'updateBasic'])->name('profile.update_basic');
    
    // Educations
    Route::post('/profile/education', [ProfileController::class, 'storeEducation'])->name('profile.education.store');
    Route::delete('/profile/education/{id}', [ProfileController::class, 'destroyEducation'])->name('profile.education.destroy');
    
    // Experiences
    Route::post('/profile/experience', [ProfileController::class, 'storeExperience'])->name('profile.experience.store');
    Route::delete('/profile/experience/{id}', [ProfileController::class, 'destroyExperience'])->name('profile.experience.destroy');
    
    // Documents
    Route::post('/profile/document', [ProfileController::class, 'storeDocument'])->name('profile.document.store');
    Route::delete('/profile/document/{id}', [ProfileController::class, 'destroyDocument'])->name('profile.document.destroy');
    
    // Payroll & Leave (Placeholders for now)
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/leave', [LeaveController::class, 'index'])->name('leave.index');
});
