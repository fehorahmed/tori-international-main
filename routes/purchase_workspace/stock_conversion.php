<?php

use App\Http\Controllers\StockConversion\StockConversionController;
use App\Http\Controllers\StockConversion\DashboardController;
use App\Http\Controllers\StockConversion\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('stock-conversions/dashboard', [DashboardController::class, 'index'])->name('stock-conversions.dashboard')->middleware('permission:stock_conversion');

Route::resource('stock-conversions', StockConversionController::class)->middleware('permission:stock_conversion');
Route::get('stock-conversions-datatable', [StockConversionController::class, 'dataTable'])->name('stock-conversions.datatable')->middleware('permission:stock_conversion');
Route::post('stock-conversions/{stock_conversion}/approve', [StockConversionController::class, 'approve'])->name('stock-conversions.approve')->middleware('permission:stock_conversion');

Route::get('stock-conversions-report', [ReportController::class, 'index'])->name('stock-conversions.report')->middleware('permission:stock_conversion_report');
