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
        Route::get('registration/options', [\App\Http\Controllers\API\Membership\RegistrationController::class, 'options']);
        Route::post('register/initiate', [\App\Http\Controllers\API\Membership\RegistrationController::class, 'initiate']);
        Route::post('register/resend', [\App\Http\Controllers\API\Membership\RegistrationController::class, 'resend']);
        Route::post('register/verify-otp', [\App\Http\Controllers\API\Membership\RegistrationController::class, 'verify']);
        Route::post('register/complete', [\App\Http\Controllers\API\Membership\RegistrationController::class, 'complete']);
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
        Route::get('card', [\App\Http\Controllers\API\Membership\CardController::class, 'show']);
        Route::get('transfers', [\App\Http\Controllers\API\Membership\TransferController::class, 'index']);
        Route::post('transfers/quote', [\App\Http\Controllers\API\Membership\TransferController::class, 'quote'])->middleware('throttle:20,1');
        Route::post('transfers', [\App\Http\Controllers\API\Membership\TransferController::class, 'store'])->middleware('throttle:10,1');
        Route::get('cashout', [\App\Http\Controllers\API\Membership\CashoutController::class, 'index']);
        Route::post('cashout/quote', [\App\Http\Controllers\API\Membership\CashoutController::class, 'quote'])->middleware('throttle:20,1');
        Route::post('cashouts', [\App\Http\Controllers\API\Membership\CashoutController::class, 'store'])->middleware('throttle:10,1');
        Route::get('cashouts/{cashout}', [\App\Http\Controllers\API\Membership\CashoutController::class, 'show']);
        Route::post('cashouts/{cashout}/cancel', [\App\Http\Controllers\API\Membership\CashoutController::class, 'cancel']);
        Route::put('profile', [\App\Http\Controllers\API\Membership\ProfileController::class, 'update']);
        Route::post('kyc', [\App\Http\Controllers\API\Membership\KycController::class, 'store'])->middleware('throttle:6,1');
        Route::get('kyc/{kyc}/documents/{kind}', [\App\Http\Controllers\API\Membership\KycController::class, 'document']);
    });
});

