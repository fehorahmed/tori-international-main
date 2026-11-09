<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| TORI Membership & Digital Banking Mobile App APIs
|--------------------------------------------------------------------------
| Base Prefix: /api/membership/
| Target App : tori_membership (Customer MFI & Digital Banking)
|
*/

Route::prefix('v1')->group(function () {
    // Guest / Authentication Routes (Rate Limited)
    Route::prefix('auth')->middleware(['throttle:10,1'])->group(function () {
        // Step 1: Submit mobile number -> Send OTP
        Route::post('initiate', [\App\Http\Controllers\API\Membership\AuthController::class, 'initiate']);

        // Step 2: Verify OTP -> Receive short-lived verification token
        Route::post('verify-otp', [\App\Http\Controllers\API\Membership\AuthController::class, 'verifyOtp']);

        // Step 3 (A): Login with 6-Digit PIN
        Route::post('login-pin', [\App\Http\Controllers\API\Membership\AuthController::class, 'loginWithPin']);

        // Step 3 (B): Set / Reset 6-Digit PIN (First time or reset)
        Route::post('set-pin', [\App\Http\Controllers\API\Membership\AuthController::class, 'setPin']);

        // Biometric Login (Fingerprint / FaceID)
        Route::post('login-biometric', [\App\Http\Controllers\API\Membership\AuthController::class, 'loginBiometric']);
    });

    // Protected Routes (Sanctum Authenticated for Customer / Member)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('logout', [\App\Http\Controllers\API\Membership\AuthController::class, 'logout']);
            Route::post('toggle-biometric', [\App\Http\Controllers\API\Membership\AuthController::class, 'toggleBiometric']);
        });

        // Dashboard & Profile
        Route::get('dashboard', [\App\Http\Controllers\API\Membership\DashboardController::class, 'index']);
        Route::get('profile', [\App\Http\Controllers\API\Membership\ProfileController::class, 'getProfile']);
    });
});

