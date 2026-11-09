<?php

use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Inventory\ReportController;
use App\Http\Controllers\Inventory\DashboardController;
use Illuminate\Support\Facades\Route;

// Inventory Dashboard
Route::get('inventory-dashboard', [DashboardController::class, 'index'])->name('inventory.dashboard');

//Inventory Operations
Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::get('inventory-datatable', [InventoryController::class, 'dataTable'])->name('inventory.datatable');
Route::get('inventory-details', [InventoryController::class, 'details'])->name('inventory.details');
Route::get('inventory-log-datatable', [InventoryController::class, 'inventoryLogDataTable'])->name('inventory_log.datatable');
Route::get('get-products-by-category-group', [InventoryController::class, 'getProductsByCategoryGroup'])->name('get_products_by_category_group');

// Opening Stock
Route::get('inventory/opening-stock', [InventoryController::class, 'openingStockCreate'])->name('inventory.opening_stock.create');
Route::post('inventory/opening-stock', [InventoryController::class, 'openingStockStore'])->name('inventory.opening_stock.store');
Route::get('inventory/opening-stock-datatable', [InventoryController::class, 'openingStockDataTable'])->name('inventory.opening_stock.datatable');
Route::post('inventory/opening-stock/destroy/{id}', [InventoryController::class, 'openingStockDestroy'])->name('inventory.opening_stock.destroy');

// Inventory Reports
Route::middleware('permission:inventory_reports')->group(function () {
    Route::get('report/stock', [ReportController::class, 'stockReport'])->name('report.stock')->middleware('permission:stock_report');
    Route::get('report/batch-stock', [ReportController::class, 'batchStockReport'])->name('report.batch_stock')->middleware('permission:batch_stock_report');
    Route::get('report/stock-valuation', [ReportController::class, 'stockValuationReport'])->name('report.stock_valuation')->middleware('permission:stock_valuation_report');
    Route::get('report/batch-stock-valuation', [ReportController::class, 'batchStockValuationReport'])->name('report.batch_stock_valuation')->middleware('permission:batch_stock_valuation_report');
    Route::get('report/product-ledger', [ReportController::class, 'productLedger'])->name('report.product_ledger');
});
