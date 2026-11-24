<?php

use App\Http\Controllers\API\Membership\AgentTransactionController;
use App\Http\Controllers\API\Membership\AuthController;
use App\Http\Controllers\API\Membership\BeneficiaryController;
use App\Http\Controllers\API\Membership\CardController;
use App\Http\Controllers\API\Membership\CashoutController;
use App\Http\Controllers\API\Membership\DashboardController;
use App\Http\Controllers\API\Membership\FdrController;
use App\Http\Controllers\API\Membership\KycController;
use App\Http\Controllers\API\Membership\ProfileController;
use App\Http\Controllers\API\Membership\ReferralController;
use App\Http\Controllers\API\Membership\RegistrationController;
use App\Http\Controllers\API\Membership\TransferController;
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
    Route::get('auth/registration/locations', [RegistrationController::class, 'locations'])->middleware('throttle:60,1');
    // Guest / Authentication Routes (Rate Limited)
    Route::prefix('auth')->middleware(['throttle:10,1'])->group(function () {
        Route::get('registration/options', [RegistrationController::class, 'options']);
        Route::post('register/initiate', [RegistrationController::class, 'initiate']);
        Route::post('register/resend', [RegistrationController::class, 'resend']);
        Route::post('register/verify-otp', [RegistrationController::class, 'verify']);
        Route::post('register/complete', [RegistrationController::class, 'complete']);
        // Step 1: Submit mobile number -> Send OTP
        Route::post('initiate', [AuthController::class, 'initiate']);

        // Step 2: Verify OTP -> Receive short-lived verification token
        Route::post('verify-otp', [AuthController::class, 'verifyOtp']);

        // Step 3 (A): Login with 6-Digit PIN
        Route::post('login-pin', [AuthController::class, 'loginWithPin']);

        // Step 3 (B): Set / Reset 6-Digit PIN (First time or reset)
        Route::post('set-pin', [AuthController::class, 'setPin']);

        // Biometric Login (Fingerprint / FaceID)
        Route::post('login-biometric', [AuthController::class, 'loginBiometric'])->middleware('throttle:6,1');
    });

    // Protected Routes (Sanctum Authenticated for Customer / Member)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('agent/{kind}')->where(['kind' => 'agent_cashin|agent_cashout'])->group(function () {
            Route::get('/', [AgentTransactionController::class, 'index']);
            Route::post('quote', [AgentTransactionController::class, 'quote'])->middleware('throttle:20,1');
            Route::post('/', [AgentTransactionController::class, 'store'])->middleware('throttle:10,1');
        });
        Route::prefix('fdr')->group(function () {
            Route::get('/', [FdrController::class, 'index']);
            Route::post('quote', [FdrController::class, 'quote'])->middleware('throttle:30,1');
            Route::post('open', [FdrController::class, 'open'])->middleware('throttle:6,1');
            Route::get('{id}', [FdrController::class, 'show'])->whereNumber('id');
            Route::post('{id}/encash', [FdrController::class, 'requestEncash'])->middleware('throttle:6,1')->whereNumber('id');
        });

        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('toggle-biometric', [AuthController::class, 'toggleBiometric'])->middleware('throttle:6,1');
        });

        Route::post('referrals/milestones/collect', [ReferralController::class, 'collectMilestones'])->middleware('throttle:6,1');
        Route::get('referrals/history', [ReferralController::class, 'history']);
        Route::get('referrals', [ReferralController::class, 'index']);
        Route::post('referrals/redeem', [ReferralController::class, 'redeem'])->middleware('throttle:6,1');

        // Dashboard & Profile
        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::get('profile', [ProfileController::class, 'getProfile']);
        Route::get('card', [CardController::class, 'show']);
        Route::get('beneficiaries', [BeneficiaryController::class, 'index']);
        Route::post('beneficiaries/lookup', [BeneficiaryController::class, 'lookup'])->middleware('throttle:20,1');
        Route::post('beneficiaries', [BeneficiaryController::class, 'store']);
        Route::patch('beneficiaries/{beneficiary}', [BeneficiaryController::class, 'update'])->whereNumber('beneficiary');
        Route::delete('beneficiaries/{beneficiary}', [BeneficiaryController::class, 'destroy'])->whereNumber('beneficiary');
        Route::get('transfers', [TransferController::class, 'index']);
        Route::post('transfers/quote', [TransferController::class, 'quote'])->middleware('throttle:20,1');
        Route::post('transfers', [TransferController::class, 'store'])->middleware('throttle:10,1');
        Route::get('cashout', [CashoutController::class, 'index']);
        Route::post('cashout/quote', [CashoutController::class, 'quote'])->middleware('throttle:20,1');
        Route::post('cashouts', [CashoutController::class, 'store'])->middleware('throttle:10,1');
        Route::get('cashouts/{cashout}', [CashoutController::class, 'show']);
        Route::post('cashouts/{cashout}/cancel', [CashoutController::class, 'cancel']);
        Route::put('profile', [ProfileController::class, 'update']);
        Route::post('kyc', [KycController::class, 'store'])->middleware('throttle:6,1');
        Route::get('kyc/{kyc}/documents/{kind}', [KycController::class, 'document']);
    });
});
