<?php


use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Route;

Route::post('attachment-delete/file', [CommonController::class, 'attachmentDelete'])->name('attachment-delete');
Route::post('attachment-title/update', [CommonController::class, 'attachmentTitleUpdate'])->name('attachment-title-update');

Route::get('get-districts', [CommonController::class, 'getDistricts'])->name('get_districts');
Route::get('get-upazilas', [CommonController::class, 'getUpazilas'])->name('get_upazilas');
Route::get('get-unions', [CommonController::class, 'getUnions'])->name('get_unions');
Route::get('get-markets', [CommonController::class, 'getMarkets'])->name('get_markets');
Route::get('get-pourashavas', [CommonController::class, 'getPourashavas'])->name('get_pourashavas');
Route::get('get-villages', [CommonController::class, 'getVillages'])->name('get_villages');
Route::get('get-filtered-customers', [CommonController::class, 'getFilteredCustomers'])->name('get_filtered_customers');
Route::get('get-filtered-suppliers', [CommonController::class, 'getFilteredSuppliers'])->name('get_filtered_suppliers');
Route::get('get-supplier-address-details', [CommonController::class, 'getSupplierAddressDetails'])->name('get_supplier_address_details');
Route::get('get-employee-address-details', [CommonController::class, 'getEmployeeAddressDetails'])->name('get_employee_address_details');
Route::post('ckeditor/upload', [CommonController::class, 'ckeditorUpload'])->name('ckeditor.upload');

// FCM Device Token Save
Route::post('save-device-token', [CommonController::class, 'saveDeviceToken'])->name('save_device_token');
