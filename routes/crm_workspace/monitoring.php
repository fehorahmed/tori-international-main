<?php 

use App\Http\Controllers\Monitoring\LocationController;
use App\Http\Controllers\Monitoring\DashboardController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('tracking/dashboard', [DashboardController::class, 'index'])->name('tracking.dashboard');

// Live Location & Map Data (Requires live_location permission)
Route::middleware('permission:live_location')->group(function () {
    Route::get('tracking/live-location', [LocationController::class, 'liveLocation'])->name('tracking.live_location');
    Route::get('tracking/initial-locations', [LocationController::class, 'initialLocations'])->name('tracking.initial_location');
    Route::get('tracking/search-user', [LocationController::class, 'searchUser'])->name('tracking.search_user');
});

// Location History (Requires location_history permission)
Route::middleware('permission:location_history')->group(function () {
    Route::get('tracking/location-history', [LocationController::class, 'locationHistory'])->name('tracking.location_history');
    Route::get('tracking/user-history/{userId}/{date}', [LocationController::class, 'userLocationHistory'])->name('tracking.user_history');
});

// User Location Updates (No special permission needed, just auth)
Route::get('tracking/update-location', [LocationController::class, 'updateLocationGet']);
Route::post('tracking/update-location', [LocationController::class, 'updateLocation'])->name('tracking.update_location');
Route::post('tracking/set-offline', [LocationController::class, 'setOffline'])->name('tracking.set_offline');