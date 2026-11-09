<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommonController;

// Sales Controllers
use App\Http\Controllers\Sales\ReportController;
use App\Http\Controllers\Sales\DashboardController;
use App\Http\Controllers\Sales\SalesController;
use App\Http\Controllers\Sales\PreOrderController;

use App\Http\Controllers\Sales\SalesReturnController;

// Customer & Payment Controllers
use App\Http\Controllers\Sales\CustomerController;
use App\Http\Controllers\Sales\CustomerPaymentController;
use App\Http\Controllers\Sales\CorporateCustomerController;
use App\Http\Controllers\Sales\CorporateSaleController;
use App\Http\Controllers\Sales\DealerController;
use App\Http\Controllers\Sales\DealerSaleController;

// Tasks Controller
use App\Http\Controllers\Sales\TaskController;

// Service Controllers
use App\Http\Controllers\Sales\Service\TechnicianController;
use App\Http\Controllers\Sales\Service\ServiceController;
use App\Http\Controllers\Sales\Service\TechnicianWorkController;

/*
|--------------------------------------------------------------------------
| Sales & Service Routes
|--------------------------------------------------------------------------
*/

// Task Management
Route::prefix('tasks/{role}')->group(function () {
    Route::get('/', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('datatable', [TaskController::class, 'dataTable'])->name('tasks.datatable');
    Route::get('distribute', [TaskController::class, 'distribute'])->name('tasks.distribute');
    Route::post('distribute', [TaskController::class, 'storeDistribute'])->name('tasks.store-distribute');
    Route::get('manage/{id}', [TaskController::class, 'manageTasks'])->name('tasks.manage');
    Route::post('manage/{id}', [TaskController::class, 'updateTasks'])->name('tasks.update-manage');
    Route::get('show/{id}', [TaskController::class, 'show'])->name('tasks.show');
});
 
// Main Sales
Route::middleware('permission:sales')->group(function () {
    Route::get('sales-dashboard', [DashboardController::class, 'index'])->name('sales.dashboard');
    Route::get('pos/{saleOrder?}', [SalesController::class, 'pos'])->name('pos');
    Route::get('sales-datatable', [SalesController::class, 'dataTable'])->name('sales.datatable');
    Route::get('sales-details/{sale}', [SalesController::class, 'details'])->name('sales.details');
    Route::get('sales-sm-details/{sale}', [SalesController::class, 'smDetails'])->name('sales.sm_details');
    Route::get('sales/{sale}/print', [SalesController::class, 'printInvoice'])->name('sales.print');
    Route::resource('sales', SalesController::class);
});

// Pre-Orders
Route::middleware('permission:pre_orders')->group(function () {
    Route::get('pre-orders/datatable', [PreOrderController::class, 'dataTable'])->name('pre-orders.datatable');
    Route::get('pre-orders/{pre_order}/delivery', [PreOrderController::class, 'delivery'])->name('pre-orders.delivery');
    Route::post('pre-orders/{pre_order}/delivery', [PreOrderController::class, 'deliveryStore'])->name('pre-orders.delivery_store');
    Route::get('pre-orders/{pre_order}/print', [PreOrderController::class, 'printInvoice'])->name('pre-orders.print');
    Route::resource('pre-orders', PreOrderController::class);
});

// Customer Management
Route::middleware('permission:customer')->group(function () {

    Route::get('customer-datatable', [CustomerController::class, 'dataTable'])->name('customer.datatable');
    Route::resource('customer', CustomerController::class);
});
Route::post('customer-store-direct', [CustomerController::class, 'storeDirect'])->name('customer.store-direct');
Route::get('customer-advance', [CustomerController::class, 'advance'])->name('customer.advance')->middleware('permission:advance_from_customer');

// Customer Payment Receive
Route::middleware('permission:sales_payment_receive')->group(function () {
    Route::get('customer-payment/history/datatable', [CustomerPaymentController::class, 'historyDatatable'])->name('customer-payment.history.datatable');
    Route::get('customer-payment/history', [CustomerPaymentController::class, 'history'])->name('customer-payment.history');
    Route::get('customer-payment/datatable', [CustomerPaymentController::class, 'dataTable'])->name('customer-payment.datatable');
    Route::get('customer-payment', [CustomerPaymentController::class, 'index'])->name('customer-payment.index');
    Route::get('customer-payment/create', [CustomerPaymentController::class, 'create'])->name('customer-payment.create');
    Route::post('customer-payment', [CustomerPaymentController::class, 'store'])->name('customer-payment.store');

    Route::post('sales/payment-receive', [SalesController::class, 'paymentReceive'])->name('sales.customer_payment_receive');
});

// Corporate Management
Route::middleware('permission:corporate_customer')->group(function () {
    Route::get('corporate-customer-datatable', [CorporateCustomerController::class, 'dataTable'])->name('corporate-customer.datatable');
    Route::resource('corporate-customer', CorporateCustomerController::class);
});
Route::middleware('permission:corporate_sales')->group(function () {
    Route::get('corporate-sales-datatable', [CorporateSaleController::class, 'dataTable'])->name('corporate-sales.datatable');
    Route::get('corporate-sales/{corporate_sale}/print', [CorporateSaleController::class, 'printInvoice'])->name('corporate-sales.print');
    Route::resource('corporate-sales', CorporateSaleController::class);
});

// Dealer Management
Route::middleware('permission:dealer')->group(function () {
    Route::get('dealer-datatable', [DealerController::class, 'dataTable'])->name('dealer.datatable');
    Route::resource('dealer', DealerController::class);
});

Route::middleware('permission:dealer_sales')->group(function () {
    Route::get('dealer-sales-datatable', [DealerSaleController::class, 'dataTable'])->name('dealer-sales.datatable');
    Route::get('dealer-sales/{dealer_sale}/print', [DealerSaleController::class, 'printInvoice'])->name('dealer-sales.print');
    Route::resource('dealer-sales', DealerSaleController::class);
});

// Service Management
Route::middleware(['auth'])->prefix('service')->name('service.')->group(function () {
    // Technician CRUD
    Route::middleware('permission:technician')->group(function () {
        Route::get('technician/datatable', [TechnicianController::class, 'dataTable'])->name('technician.datatable');
        Route::resource('technician', TechnicianController::class)->only(['index', 'show']);
    });

    // Service Request
    Route::middleware('permission:service_request')->group(function () {
        Route::get('request/datatable', [ServiceController::class, 'dataTable'])->name('request.datatable');
        Route::get('request/get-orders', [ServiceController::class, 'getOrders'])->name('request.get_orders');
        Route::get('request/get-order-products', [ServiceController::class, 'getOrderProducts'])->name('request.get_order_products');
        Route::get('request/{service}/details', [ServiceController::class, 'details'])->name('request.details');
        Route::post('request/{service}/approve', [ServiceController::class, 'approve'])->name('request.approve');
        Route::resource('request', ServiceController::class);
    });

    // Technician Panel
    Route::middleware('permission:service_panel')->group(function () {
        Route::get('panel', [TechnicianWorkController::class, 'index'])->name('panel.index');
        Route::get('panel/datatable', [TechnicianWorkController::class, 'dataTable'])->name('panel.datatable');
        Route::get('panel/{service}/update', [TechnicianWorkController::class, 'updateForm'])->name('panel.update_form');
        Route::post('panel/{service}/submit', [TechnicianWorkController::class, 'submitWork'])->name('panel.submit_work');
    });
});


// Reports
Route::middleware('permission:sales_reports')->group(function () {
    Route::get('sales-report', [ReportController::class, 'saleReport'])->name('report.sale')->middleware('permission:sale_report');
    Route::get('invoice-profit-loss-report', [ReportController::class, 'invoiceProfitLossReport'])->name('report.invoice_profit_loss')->middleware('permission:sale_report');
    Route::get('product-profit-loss-report', [ReportController::class, 'productProfitLossReport'])->name('report.product_profit_loss')->middleware('permission:sale_report');
    Route::get('customer-due-report', [ReportController::class, 'customerDueReport'])->name('report.customer_due')->middleware('permission:customer_due_report');
    Route::get('customer-ledger', [ReportController::class, 'customerLedger'])->name('report.customer_ledger')->middleware('permission:customer_report');
    Route::get('customer-report', [ReportController::class, 'customerReport'])->name('report.customer')->middleware('permission:customer_report');
});

// Utilities & AJAX Calls
Route::get('search-barcode-autocomplete', [SalesController::class,'searchBarcodeAutocomplete'])->name('search-barcode-autocomplete');
Route::get('pos_product_search', [SalesController::class, 'posProductSearch'])->name('pos_product_search');
Route::get('party-json', [SalesController::class,'partyJson'])->name('party_json');
Route::get('customer-validation-check', [SalesController::class,'customerValidationCheck'])->name('customer_validation_check');

// CommonController
Route::get('get_product_batch', [CommonController::class, 'getProductBatch'])->name('get_product_batch');
Route::get('get_product_stock_details', [CommonController::class, 'getProductStockDetails'])->name('get_product_stock_details');

