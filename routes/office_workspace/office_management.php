<?php

use App\Http\Controllers\OfficeManagement\NoticeController;
use App\Http\Controllers\OfficeManagement\MeetingSummaryController;
use App\Http\Controllers\OfficeManagement\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['check_head_office','permission:office_management'])->group(function () {
    Route::get('office-management/dashboard', [DashboardController::class, 'index'])->name('office_management.dashboard');
    Route::resource('notices', NoticeController::class)->middleware('permission:notice_management');
    Route::get('notices-datatable', [NoticeController::class, 'dataTable'])->name('notices.datatable');

    Route::resource('meetings', MeetingSummaryController::class)->middleware('permission:meeting_management');
    Route::get('meetings-datatable', [MeetingSummaryController::class, 'dataTable'])->name('meetings.datatable');
});
