<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FixedAsset\FixedAssetCategoryController;
use App\Http\Controllers\FixedAsset\FixedAssetDashboardController;
use App\Http\Controllers\FixedAsset\FixedAssetController;
use App\Http\Controllers\FixedAsset\DisposalController;
use App\Http\Controllers\FixedAsset\DepreciationController;
use App\Http\Controllers\FixedAsset\FixedAssetReportController;

Route::prefix('fixed-assets')->name('fixed-assets.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [FixedAssetDashboardController::class, 'index'])->name('dashboard');

    // Categories
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [FixedAssetCategoryController::class, 'index'])->name('index');
        Route::get('/datatable', [FixedAssetCategoryController::class, 'dataTable'])->name('datatable');
        Route::get('/create', [FixedAssetCategoryController::class, 'create'])->name('create');
        Route::post('/store', [FixedAssetCategoryController::class, 'store'])->name('store');
        Route::get('/{category}/edit', [FixedAssetCategoryController::class, 'edit'])->name('edit');
        Route::post('/{category}/update', [FixedAssetCategoryController::class, 'update'])->name('update');
        Route::delete('/{category}/delete', [FixedAssetCategoryController::class, 'destroy'])->name('destroy');
    });

    // Assets
    Route::get('/', [FixedAssetController::class, 'index'])->name('index');
    Route::get('/datatable', [FixedAssetController::class, 'dataTable'])->name('datatable');
    Route::get('/create', [FixedAssetController::class, 'create'])->name('create');
    Route::post('/store', [FixedAssetController::class, 'store'])->name('store');
    Route::get('/{fixedAsset}/show', [FixedAssetController::class, 'show'])->name('show');
    Route::get('/{fixedAsset}/edit', [FixedAssetController::class, 'edit'])->name('edit');
    Route::post('/{fixedAsset}/update', [FixedAssetController::class, 'update'])->name('update');

    // Disposal (UI: Remove Asset)
    Route::get('/{fixedAsset}/sell', [DisposalController::class, 'sellForm'])->name('sell-form');
    Route::post('/{fixedAsset}/sell', [DisposalController::class, 'sellStore'])->name('sell-store');
    Route::get('/{fixedAsset}/scrap', [DisposalController::class, 'scrapForm'])->name('scrap-form');
    Route::post('/{fixedAsset}/scrap', [DisposalController::class, 'scrapStore'])->name('scrap-store');

    // Depreciation
    Route::prefix('depreciation')->name('depreciation.')->group(function () {
        Route::get('/', [DepreciationController::class, 'index'])->name('index');
        Route::get('/datatable', [DepreciationController::class, 'dataTable'])->name('datatable');
        Route::post('/preview', [DepreciationController::class, 'preview'])->name('preview');
        Route::post('/run', [DepreciationController::class, 'run'])->name('run');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/register', [FixedAssetReportController::class, 'registerIndex'])->name('register');
        Route::get('/register-generate', [FixedAssetReportController::class, 'registerGenerate'])->name('register.generate');

        Route::get('/depreciation', [FixedAssetReportController::class, 'depreciationIndex'])->name('depreciation');
        Route::get('/depreciation-generate', [FixedAssetReportController::class, 'depreciationGenerate'])->name('depreciation.generate');

        Route::get('/addition-disposal', [FixedAssetReportController::class, 'additionDisposalIndex'])->name('addition_disposal');
        Route::get('/addition-disposal-generate', [FixedAssetReportController::class, 'additionDisposalGenerate'])->name('addition_disposal.generate');

        Route::get('/category-summary', [FixedAssetReportController::class, 'categorySummaryIndex'])->name('category_summary');
        Route::get('/category-summary-generate', [FixedAssetReportController::class, 'categorySummaryGenerate'])->name('category_summary.generate');
    });
});
