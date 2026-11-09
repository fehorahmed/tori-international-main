<?php

use App\Http\Controllers\ReturnsManagement\SalesReturnController;
use App\Http\Controllers\ReturnsManagement\PurchaseReturnController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReturnsManagement\DashboardController;
use App\Http\Controllers\ReturnsManagement\ReportController;
use App\Http\Controllers\ReturnsManagement\DefectStockController;

Route::prefix('returns-management')->name('returns-management.')->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Sales Returns
    Route::get('sales-returns/get-customer-invoices', [SalesReturnController::class, 'getCustomerInvoices'])->name('sales-returns.get-customer-invoices');
    Route::get('sales-returns/get-invoice-items', [SalesReturnController::class, 'getInvoiceItems'])->name('sales-returns.get-invoice-items');
    Route::get('sales-returns/datatable', [SalesReturnController::class, 'dataTable'])->name('sales-returns.datatable');
    Route::resource('sales-returns', SalesReturnController::class);

    // Purchase Returns 
    Route::get('purchase-returns/get-supplier-orders', [PurchaseReturnController::class, 'getSupplierOrders'])->name('getSupplierOrders');
    Route::get('purchase-returns/get-order-items', [PurchaseReturnController::class, 'getOrderItems'])->name('getOrderItems');
    Route::get('purchase-returns/datatable', [PurchaseReturnController::class, 'dataTable'])->name('purchase-returns.datatable');
    Route::resource('purchase-returns', PurchaseReturnController::class);

    // Defect Stock
    Route::get('defect-stock/datatable', [DefectStockController::class, 'dataTable'])->name('defect-stock.datatable');
    Route::get('defect-stock/details', [DefectStockController::class, 'details'])->name('defect-stock.details');
    Route::get('defect-stock', [DefectStockController::class, 'index'])->name('defect-stock.index');

    // Reports
    Route::get('reports/sales-returns', [ReportController::class, 'salesReturnReport'])->name('report.sales_returns');
    Route::get('reports/purchase-returns', [ReportController::class, 'purchaseReturnReport'])->name('report.purchase_returns');
    Route::get('reports/defect-stock', [ReportController::class, 'defectStockReport'])->name('report.defect_stock');
    Route::get('reports/return-summary', [ReportController::class, 'returnSummaryReport'])->name('report.return_summary');
});
