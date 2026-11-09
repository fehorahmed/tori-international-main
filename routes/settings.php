<?php
use App\Http\Controllers\NotificationController;

use App\Http\Controllers\Settings\DashboardController;
use App\Http\Controllers\Settings\BrandController;
use App\Http\Controllers\Settings\ProductCategoryController;
use App\Http\Controllers\Settings\ProductController;
use App\Http\Controllers\Settings\SubCategoryController;
use App\Http\Controllers\Settings\UnitController;
use App\Http\Controllers\Settings\PriceBookController;
use App\Http\Controllers\Settings\ReportController;

use App\Http\Controllers\Settings\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['check_head_office','permission:settings'])->group(function () {
    Route::get('settings-dashboard', [DashboardController::class, 'index'])->name('settings.dashboard');

    Route::resource('warehouses', WarehouseController::class)->middleware('permission:warehouse');
    Route::get('warehouses-datatable', [WarehouseController::class, 'dataTable'])->name('warehouses.datatable')->middleware('permission:warehouse');

    // Unit
    Route::resource('unit', UnitController::class)->middleware('permission:product_unit');
    Route::get('unit-datatable', [UnitController::class, 'dataTable'])->name('unit.datatable');

    // Brand
    Route::resource('brand', BrandController::class)->middleware('permission:brand');
    Route::get('brand-datatable', [BrandController::class, 'dataTable'])->name('brand.datatable');

    // Product Category
    Route::resource('product-categories', ProductCategoryController::class)->middleware('permission:product_category');
    Route::get('product-categories-datatable', [ProductCategoryController::class, 'dataTable'])->name('product-categories.datatable');
    Route::get('product-categories/toggle-status/{product_category}', [ProductCategoryController::class, 'toggleStatus'])->name('product-categories.toggle_status');
    Route::get('product-categories/add-edit-filter/{product_category}', [ProductCategoryController::class, 'addEditFilter'])->name('product-categories.add_edit_filter');
    Route::post('product-categories/add-edit-filter/{product_category}', [ProductCategoryController::class, 'addEditFilterPost']);

    // Product Sub Category
    Route::resource('sub-category', SubCategoryController::class)->middleware('permission:product_category');
    Route::get('sub-category-datatable', [SubCategoryController::class, 'dataTable'])->name('sub-category.datatable');
    Route::get('sub-category/toggle-status/{sub_category}', [SubCategoryController::class, 'toggleStatus'])->name('sub-category.toggle_status');

    // Product
    Route::resource('product', ProductController::class)->middleware('permission:product');
    Route::get('product-datatable', [ProductController::class, 'dataTable'])->name('product.datatable');
    Route::get('product-by-category', [ProductController::class, 'getProductsByCategory'])->name('product.by_category');

    // Price Book
    Route::get('price-book', [PriceBookController::class, 'index'])->name('price_book.index')->middleware('permission:price_book');
    Route::get('price-book-datatable', [PriceBookController::class, 'dataTable'])->name('price_book.datatable');
    Route::post('price-book-update', [PriceBookController::class, 'update'])->name('price_book.update');

    // Settings Reports
    Route::middleware('permission:settings_report')->group(function () {
        Route::get('report/brand', [ReportController::class, 'brandReport'])->name('report.brand')->middleware('permission:report_brand');
        Route::get('report/category', [ReportController::class, 'categoryReport'])->name('report.category')->middleware('permission:report_category');
        Route::get('report/sub-category', [ReportController::class, 'subCategoryReport'])->name('report.sub_category')->middleware('permission:report_sub_category');
        Route::get('report/product', [ReportController::class, 'productReport'])->name('report.product')->middleware('permission:report_product');
        Route::get('report/price-book', [ReportController::class, 'priceBookReport'])->name('report.price_book')->middleware('permission:report_price_book_history');
        Route::post('report/price-book/print', [ReportController::class, 'priceBookPrint'])->name('report.price_book.print')->middleware('permission:report_price_book_history');
        Route::get('report/price-book-history', [ReportController::class, 'priceBookHistoryReport'])->name('report.price_book_history')->middleware('permission:report_price_book_history');
    });
});





Route::get('notification', [NotificationController::class,'notification'])->name('notification');
Route::get('notification-datatable', [NotificationController::class,'datatable'])->name('notification.datatable');
Route::get('notification/mark-read', [NotificationController::class,'markRead'])->name('notification_mark_read');
Route::get('notification/{id}/read', [NotificationController::class,'readAndRedirect'])->name('notification.read');
Route::get('notification/scroll-data', [NotificationController::class,'scrollData'])->name('notification_scroll_data');

// Location Settings (Dynamic CRUD for Divisions, Districts, Upazilas, Pourashavas, Unions, Villages)
Route::group(['prefix' => 'locations/{type}', 'as' => 'locations.', 'controller' => \App\Http\Controllers\Settings\LocationController::class, 'middleware' => 'permission:location_setup'], function() {
    Route::get('/', 'index')->name('index');
    Route::get('/datatable', 'dataTable')->name('datatable');
    Route::get('/create', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::get('/{id}/edit', 'edit')->name('edit');
    Route::put('/{id}', 'update')->name('update');
    Route::delete('/{id}', 'destroy')->name('destroy');
});

// AJAX helper — fetch unions by upazila (for dynamic filter in Villages)
Route::get('locations-unions-by-upazila', [\App\Http\Controllers\Settings\LocationController::class, 'unionsByUpazila'])->name('locations.unions_by_upazila');
