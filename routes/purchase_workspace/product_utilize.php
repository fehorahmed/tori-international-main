<?php


use App\Http\Controllers\Utilize\ProductUtilizeController;
use Illuminate\Support\Facades\Route;

    // Dashboard
Route::middleware(['permission:product_utilize'])->group(function () {
    Route::get('product-utilizes-dashboard', [\App\Http\Controllers\Utilize\DashboardController::class, 'index'])->name('product-utilizes.dashboard');

    // Reports
    Route::get('product-utilizes-report', [\App\Http\Controllers\Utilize\ReportController::class, 'index'])->name('product-utilizes.report');

    // Product Utilize
    Route::get('product-utilizes-datatable', [ProductUtilizeController::class, 'dataTable'])->name('product-utilizes.datatable');
    Route::get('product-utilizes-details/{product_utilize}', [ProductUtilizeController::class, 'details'])->name('product-utilizes.details');
    
    // Resource route MUST be at the bottom
    Route::resource('product-utilizes', ProductUtilizeController::class);
});
