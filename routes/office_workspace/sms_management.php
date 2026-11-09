<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsManagement\SmsDashboardController;
use App\Http\Controllers\SmsManagement\SmsGatewayController;
use App\Http\Controllers\SmsManagement\SmsTemplateController;
use App\Http\Controllers\SmsManagement\SmsLogController;
use App\Http\Controllers\SmsManagement\SmsCampaignController;

Route::middleware(['auth'])->prefix('admin/sms-management')->name('sms_management.')->group(function () {
    
    // Dashboard
    Route::get('/', [SmsDashboardController::class, 'index'])->name('dashboard');

    // Gateways
    Route::get('/gateways', [SmsGatewayController::class, 'index'])->name('gateways.index');
    Route::post('/gateways', [SmsGatewayController::class, 'store'])->name('gateways.store');
    Route::put('/gateways/{gateway}', [SmsGatewayController::class, 'update'])->name('gateways.update');
    Route::post('/gateways/{gateway}/make-active', [SmsGatewayController::class, 'makeActive'])->name('gateways.make_active');
    
    // Templates
    Route::get('/templates', [SmsTemplateController::class, 'index'])->name('templates.index');
    Route::post('/templates', [SmsTemplateController::class, 'store'])->name('templates.store');
    Route::put('/templates/{template}', [SmsTemplateController::class, 'update'])->name('templates.update');
    Route::post('/templates/{template}/toggle-status', [SmsTemplateController::class, 'toggleStatus'])->name('templates.toggle_status');

    // Logs
    Route::get('/logs', [SmsLogController::class, 'index'])->name('logs.index');
    Route::get('/logs/data', [SmsLogController::class, 'dataTable'])->name('logs.data');

    // Campaign (Bulk SMS)
    Route::get('/campaign', [SmsCampaignController::class, 'index'])->name('campaign.index');
    Route::post('/campaign/send', [SmsCampaignController::class, 'sendBulk'])->name('campaign.send');

});
