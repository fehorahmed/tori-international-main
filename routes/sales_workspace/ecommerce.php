<?php

use App\Http\Controllers\Ecommerce\API\CouponController;
use App\Http\Controllers\Ecommerce\API\PageController;
use App\Http\Controllers\Ecommerce\BannerController;
use App\Http\Controllers\Ecommerce\BlogController;
use App\Http\Controllers\Ecommerce\ContactController;
use App\Http\Controllers\Ecommerce\EcomProductController;
use App\Http\Controllers\Ecommerce\GalleryController;

use App\Http\Controllers\Ecommerce\OrderController;
use App\Http\Controllers\Ecommerce\SettingController;
use App\Http\Controllers\Ecommerce\FilterController;
use App\Http\Controllers\Ecommerce\DashboardController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::middleware('permission:ecommerce')->group(function () {
    Route::get('ecommerce/dashboard', [DashboardController::class, 'index'])->name('ecom_dashboard');
});

//Settings
Route::middleware('permission:ecommerce_settings_edit')->group(function () {
    Route::get('ecommerce/settings/update/{setting}', [SettingController::class, 'update'])->name('ecom_settings.update');
    Route::post('ecommerce/settings/update/{setting}', [SettingController::class, 'updatePost']);
});


// Banner
Route::middleware('permission:ecommerce_banner')->group(function () {
    Route::get('ecommerce/banner', [BannerController::class, 'index'])->name('ecom_banner');
    Route::get('ecommerce/banner-datatable', [BannerController::class, 'dataTable'])->name('ecom_banner.datatable');
});
Route::middleware('permission:ecommerce_banner_create')->group(function () {
    Route::get('ecommerce/banner/add', [BannerController::class, 'add'])->name('ecom_banner.add');
    Route::post('ecommerce/banner/add', [BannerController::class, 'addPost']);
});
Route::middleware('permission:ecommerce_banner_edit')->group(function () {
    Route::get('ecommerce/banner/edit/{banner}', [BannerController::class, 'edit'])->name('ecom_banner.edit');
    Route::post('ecommerce/banner/edit/{banner}', [BannerController::class, 'editPost']);
});

// Gallery
Route::middleware('permission:ecommerce_gallery')->group(function () {
    Route::get('ecommerce/gallery', [GalleryController::class, 'index'])->name('ecom_gallery');
    Route::get('ecommerce/gallery-datatable', [GalleryController::class, 'dataTable'])->name('ecom_gallery.datatable');
});
Route::middleware('permission:ecommerce_gallery_create')->group(function () {
    Route::get('ecommerce/gallery/add', [GalleryController::class, 'add'])->name('ecom_gallery.add');
    Route::post('ecommerce/gallery/add', [GalleryController::class, 'addPost']);
});
Route::middleware('permission:ecommerce_gallery_edit')->group(function () {
    Route::get('ecommerce/gallery/edit/{gallery}', [GalleryController::class, 'edit'])->name('ecom_gallery.edit');
    Route::post('ecommerce/gallery/edit/{gallery}', [GalleryController::class, 'editPost']);
});

// Page
Route::middleware('permission:ecommerce_page')->group(function () {
    Route::get('ecommerce/page', [PageController::class, 'index'])->name('ecom_page');
    Route::get('ecommerce/page-datatable', [PageController::class, 'dataTable'])->name('ecom_page.datatable');
});
Route::middleware('permission:ecommerce_page_create')->group(function () {
    Route::get('ecommerce/page/add', [PageController::class, 'add'])->name('ecom_page.add');
    Route::post('ecommerce/page/add', [PageController::class, 'addPost']);
});
Route::middleware('permission:ecommerce_page_edit')->group(function () {
    Route::get('ecommerce/page/edit/{page}', [PageController::class, 'edit'])->name('ecom_page.edit');
    Route::post('ecommerce/page/edit/{page}', [PageController::class, 'editPost']);
});

// Coupon
Route::prefix('ecommerce/coupon')->group(function () {
    Route::middleware('permission:ecommerce_coupon')->group(function () {
        Route::get('/', [CouponController::class, 'index'])->name('ecom_coupon');
        Route::get('/datatable', [CouponController::class, 'dataTable'])->name('ecom_coupon.datatable');
    });
    Route::middleware('permission:ecommerce_coupon_create')->group(function () {
        Route::get('/add', [CouponController::class, 'add'])->name('ecom_coupon.add'); 
        Route::post('/add', [CouponController::class, 'addPost']); 
    });
    Route::middleware('permission:ecommerce_coupon_edit')->group(function () {
        Route::get('/edit/{coupon}', [CouponController::class, 'edit'])->name('ecom_coupon.edit'); 
        Route::post('/edit/{coupon}', [CouponController::class, 'editPost']); 
        Route::post('/toggle-status/{coupon}', [CouponController::class, 'toggleStatus'])->name('ecom_coupon.toggleStatus');
    });
    Route::middleware('permission:ecommerce_coupon_delete')->group(function () {
        Route::delete('/delete/{coupon}', [CouponController::class, 'delete'])->name('ecom_coupon.delete'); 
    });
});

// Blog
Route::middleware('permission:ecommerce_blog')->group(function () {
    Route::get('ecommerce/blog', [BlogController::class, 'index'])->name('ecom_blog');
    Route::get('ecommerce/blog-datatable', [BlogController::class, 'dataTable'])->name('ecom_blog.datatable');
});
Route::middleware('permission:ecommerce_blog_create')->group(function () {
    Route::get('ecommerce/blog/add', [BlogController::class, 'add'])->name('ecom_blog.add');
    Route::post('ecommerce/blog/add', [BlogController::class, 'addPost']);
});
Route::middleware('permission:ecommerce_blog_edit')->group(function () {
    Route::get('ecommerce/blog/edit/{blog}', [BlogController::class, 'edit'])->name('ecom_blog.edit');
    Route::post('ecommerce/blog/edit/{blog}', [BlogController::class, 'editPost']);
});

//Product
Route::middleware('permission:ecommerce_product')->group(function () {
    Route::get('ecommerce/product', [EcomProductController::class, 'index'])->name('ecom_product');
    Route::get('ecommerce/product-datatable', [EcomProductController::class, 'dataTable'])->name('ecom_product.datatable');
    Route::get('ecommerce/get-sub-category', [EcomProductController::class, 'productSubCategory'])->name('get_product_sub_category');
});
Route::middleware('permission:ecommerce_product_create')->group(function () {
    Route::get('ecommerce/product/add', [EcomProductController::class, 'add'])->name('ecom_product.add');
    Route::post('ecommerce/product/add', [EcomProductController::class, 'addPost']);
});
Route::middleware('permission:ecommerce_product_edit')->group(function () {
    Route::get('ecommerce/product/edit/{product}', [EcomProductController::class, 'edit'])->name('ecom_product.edit');
    Route::post('ecommerce/product/edit/{product}', [EcomProductController::class, 'editPost']);
    Route::get('ecommerce/product-images/{product}', [EcomProductController::class, 'productImages'])->name('ecom_product.images');
    Route::post('ecommerce/product-images/{product}', [EcomProductController::class, 'productImagesPost']);
    Route::post('ecommerce/product-upload-images', [EcomProductController::class, 'productImagesUpload'])->name('ecom_product.upload_images');
    Route::get('ecommerce/product-filters/{product}', [EcomProductController::class, 'productFilters'])->name('ecom_product.filters');
    Route::post('ecommerce/product-filters/{product}', [EcomProductController::class, 'productFiltersPost']);
    Route::get('ecommerce/product/toggle-status/{product}', [EcomProductController::class, 'toggleStatus'])->name('product.toggle_status');
    Route::get('ecommerce/product/toggle-show/{product}', [EcomProductController::class, 'toggleShow'])->name('product.toggle_show');
});
Route::middleware('permission:ecommerce_product_delete')->group(function () {
    Route::delete('ecommerce/product-delete-image/{product}', [EcomProductController::class, 'productDeleteImage'])->name('ecom_product.delete_image');
});

// Order
Route::middleware('permission:ecommerce_order')->group(function () {
    Route::get('ecommerce/order', [OrderController::class, 'index'])->name('ecom_order');
    Route::get('ecommerce/order-datatable', [OrderController::class, 'dataTable'])->name('ecom_order.datatable');
    Route::get('ecommerce/order/details/{order}', [OrderController::class, 'details'])->name('ecom_order.details');
    Route::get('ecommerce/get-order-details', [OrderController::class, 'getOrderDetails'])->name('get_order_details');
});
Route::middleware('permission:ecommerce_order_confirm')->group(function () {
    Route::get('ecommerce/order/confirm/{order}', [OrderController::class, 'salesConfirm'])->name('ecom_order.confirm');
    Route::post('ecommerce/order/confirm/{order}', [OrderController::class, 'salesConfirmPost']);
});
Route::middleware('permission:ecommerce_order_edit')->group(function () {
    Route::post('ecommerce/order/status', [OrderController::class, 'orderStatus'])->name('ecom_order.status_change');
    Route::post('ecommerce/order/make-payment', [OrderController::class, 'makePayment'])->name('sale_receipt.make_payment');
});
Route::middleware('permission:ecommerce_order_delete')->group(function () {
    Route::post('ecommerce/order/delete', [OrderController::class, 'orderDelete'])->name('order_receipt_delete');
});

// Contact
Route::middleware('permission:ecommerce_contact')->group(function () {
    Route::get('ecommerce/contact', [ContactController::class, 'index'])->name('ecom_contact');
    Route::get('ecommerce/contact-datatable', [ContactController::class, 'dataTable'])->name('ecom_contact.datatable');
});
// (Assuming contact has add but no explicit create permission in seeder, using contact_edit for add/edit)
Route::middleware('permission:ecommerce_contact_edit')->group(function () {
    Route::get('ecommerce/contact/add', [ContactController::class, 'add'])->name('ecom_contact.add');
    Route::post('ecommerce/contact/add', [ContactController::class, 'addPost']);
    Route::get('ecommerce/contact/edit/{contact}', [ContactController::class, 'edit'])->name('ecom_contact.edit');
    Route::post('ecommerce/contact/edit/{contact}', [ContactController::class, 'editPost']);
});

// Filters
Route::middleware('permission:ecommerce_filter')->group(function () {
    Route::resource('filters', FilterController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::get('ecommerce/filters-datatable', [FilterController::class, 'dataTable'])->name('filters.datatable');
});
Route::middleware('permission:ecommerce_filter_create')->group(function () {
    Route::resource('filters', FilterController::class)->only(['create', 'store']);
});
Route::middleware('permission:ecommerce_filter_edit')->group(function () {
    Route::resource('filters', FilterController::class)->only(['edit', 'update']);
});
Route::middleware('permission:ecommerce_filter_delete')->group(function () {
    Route::resource('filters', FilterController::class)->only(['destroy']);
});

Route::prefix('ecommerce/packages')->name('product-packages.')->controller(\App\Http\Controllers\Ecommerce\ProductPackageController::class)->group(function () {
 Route::get('/', 'index')->name('index');
 Route::get('create', 'create')->name('create');
 Route::post('/', 'store')->name('store');
 Route::get('{package}/edit', 'edit')->name('edit');
 Route::put('{package}', 'update')->name('update');
});

Route::prefix('ecommerce/package-orders')->name('package-orders.')->controller(\App\Http\Controllers\Ecommerce\PackageOrderController::class)->group(function () {
 Route::get('/', 'index')->name('index');
 Route::get('{checkout}', 'show')->name('show');
 Route::post('{checkout}/confirm', 'confirm')->name('confirm');
 Route::post('{checkout}/cancel', 'cancel')->name('cancel');
 Route::post('{checkout}/status', 'status')->name('status');
});
