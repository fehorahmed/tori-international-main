<?php

use App\Http\Controllers\Purchase\PurchaseOrderController;
use App\Http\Controllers\Purchase\SupplierReceiptController;
use App\Http\Controllers\Purchase\SupplierController;
use App\Http\Controllers\Purchase\DashboardController;
use App\Http\Controllers\Purchase\ReportController;

use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:purchase')->group(function () {
    Route::get('purchase-dashboard', [DashboardController::class, 'index'])->name('purchase.dashboard');

    Route::resource('supplier', SupplierController::class)->middleware('permission:supplier');
    Route::get('supplier-datatable', [SupplierController::class, 'dataTable'])->name('supplier.datatable');

    //Purchase
    Route::resource('purchase', PurchaseOrderController::class)->middleware('permission:purchase');
    Route::get('purchase-datatable', [PurchaseOrderController::class, 'dataTable'])->name('purchase.datatable');

    Route::get('purchase-details/{purchase}', [PurchaseOrderController::class, 'details'])->name('purchase.details')->middleware('permission:purchase_list');
    Route::get('purchase-details/{purchase}/print', [PurchaseOrderController::class, 'detailsPrint'])->name('purchase.details.print')->middleware('permission:purchase_list');
    Route::post('purchase/payment', [PurchaseOrderController::class, 'payment'])->name('purchase.supplier_payment')->middleware('permission:purchase_payment');
    Route::get('purchase-supplier-validation-check', [PurchaseOrderController::class, 'supplierValidationCheck'])->name('purchase.supplier_validation_check');
    
    //Supplier Payments
    Route::get('supplier-payments', [PurchaseOrderController::class, 'supplierPayments'])->name('purchase.supplier_payments_list')->middleware('permission:supplier_payment');
    Route::get('supplier-payments-datatable', [PurchaseOrderController::class, 'supplierPaymentsDatatable'])->name('purchase.supplier_payments.datatable');
    
    Route::get('supplier-payments-create', [PurchaseOrderController::class, 'supplierPaymentCreate'])->name('purchase.supplier_payment.create');
    Route::post('supplier-payments-store', [PurchaseOrderController::class, 'supplierPaymentStore'])->name('purchase.supplier_payment.store');
    
    Route::get('supplier-receipt-create', [SupplierReceiptController::class, 'create'])->name('purchase.supplier_receipt.create');
    Route::post('supplier-receipt-store', [SupplierReceiptController::class, 'store'])->name('purchase.supplier_receipt.store');
    
    Route::get('supplier-due-orders', [PurchaseOrderController::class, 'supplierDueOrders'])->name('purchase.supplier_due_orders');
    Route::get('supplier-payment-history/{supplier}', [PurchaseOrderController::class, 'supplierPaymentHistory'])->name('purchase.supplier_payment_history')->middleware('permission:supplier_payment');
    Route::get('supplier-payment-history-datatable', [PurchaseOrderController::class, 'supplierPaymentHistoryDatatable'])->name('purchase.supplier_payment_history.datatable');

    // Reports
    Route::middleware('permission:purchase_reports')->group(function () {
        Route::get('supplier-report', [ReportController::class, 'supplierReport'])->name('supplier.report')->middleware('permission:supplier_report');
        Route::get('supplier-ledger', [ReportController::class, 'supplierLedger'])->name('supplier.ledger')->middleware('permission:supplier_report');
        Route::get('purchase-report', [ReportController::class, 'purchaseReport'])->name('purchase.report')->middleware('permission:purchase_report');
        Route::get('purchase-details-report', [ReportController::class, 'purchaseDetailsReport'])->name('purchase_details.report')->middleware('permission:purchase_details_report');
    });

    //CommonController
    Route::get('get_product_details', [CommonController::class, 'getProductDetails'])->name('get_product_details');
    Route::get('get_account_head', [CommonController::class, 'getAccountHead'])->name('get_account_head');
    Route::get('get_stock_info', [CommonController::class, 'getStockInfo'])->name('get_stock_info');
    Route::get('get_sales_orders', [CommonController::class, 'getSalesOrders'])->name('get_sales_orders');
    Route::get('get_sales_order_details', [CommonController::class, 'getSalesOrderDetails'])->name('get_sales_order_details');
    Route::get('get_distribution_product_info', [CommonController::class, 'getDistributionProductInfo'])->name('get_distribution_product_info');
    Route::get('get_collection_amount', [CommonController::class, 'getCollectionAmount'])->name('get_collection_amount');
    Route::get('get_category_group_wise_product', [CommonController::class, 'getCategoryGroupWiseProduct'])->name('get_category_group_wise_product');
    Route::get('get_category_group_wise_category', [CommonController::class, 'getCategoryGroupWiseCategory'])->name('get_category_group_wise_category');
    Route::get('get_sub_categories', [CommonController::class, 'getSubCategories'])->name('get_sub_categories');
});
