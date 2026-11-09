<?php

use App\Http\Controllers\BranchSwitchController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\CardManagement\MemberCardController;
use App\Http\Controllers\CardManagement\EmployeeCardController;
use App\Http\Controllers\ShomriddhiAdvance\AccountController;
use App\Http\Middleware\checkUserStatus;
use App\Models\Ecommerce\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    $userAgent = request()->userAgent();
    $isWebView = Str::contains($userAgent, ['TORI_INTERNATIONAL_APP']);
    if ($isWebView) {
        return redirect()->route('login');
    }

    $setting = Setting::select('primary_color')->first();
    return view('ecommerce.app', compact('setting'));
});

Route::get('/card-management/member/verify/{card_number}', [MemberCardController::class, 'verifyPublic'])->name('card-management.member.public_verify');
Route::get('/card-management/employee/verify/{card_number}', [EmployeeCardController::class, 'verifyPublic'])->name('card-management.employee.public_verify');

// Shomriddhi Public Mini-Statement (QR Scan)
Route::get('/shomriddhi/mini-statement/{account_number}', [AccountController::class, 'miniStatement'])->name('shomriddhi.mini_statement');

Route::prefix('admin')->group(function () {
    // Route::get('/', function () {
    //     return redirect()->route('login');
    // });
    
    Route::get('/', function () {
        $userAgent = request()->userAgent();
        $isWebView = Str::contains($userAgent, ['TORI_INTERNATIONAL_APP']);
        if ($isWebView) {
            return redirect()->route('login');
        }

        $setting = Setting::select('primary_color')->first();
        return view('ecommerce.app', compact('setting'));
    });

    Route::middleware(['auth','role_check:Admin,Area_Manager,Branch_Manager,SR,Technician,Trainee,Lead_Collector', 'verified', CheckUserStatus::class, 'checkOtpSessionExpire'])->group(function () {
        require __DIR__ . '/dashboard.php';
        require __DIR__ . '/settings.php';
        require __DIR__ . '/branch_management.php';
        require __DIR__ . '/profile.php';
        require __DIR__ . '/crm_workspace/crm_workspace.php';
        require __DIR__ . '/office_workspace/office_workspace.php';
        require __DIR__ . '/hr_workspace/hr_workspace.php';
        require __DIR__ . '/accounts_workspace/accounts_workspace.php';

        require __DIR__ . '/purchase_workspace/purchase_workspace.php';

        require __DIR__ . '/sales_workspace/sales_workspace.php';



        require __DIR__ . '/db_backup.php';
        require __DIR__ . '/common.php';
        // require __DIR__ . '/microfinance.php';






        require __DIR__ . '/shomriddhi_workspace/shomriddhi_workspace.php';
        require __DIR__ . '/employee_profile.php';



        require __DIR__ . '/returns_management.php';

        Route::post('/branch/switch', [BranchSwitchController::class, 'switch'])->name('branch.switch');
    });

    require __DIR__ . '/auth.php';
});



Route::get('/clear', function () {
    Artisan::call('cache:forget spatie.permission.cache');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    return "Cleared!";
});

Route::get('/firebase-messaging-sw.js', [CommonController::class, 'firebaseMessagingSw'])->name('firebase-messaging-sw.js');

Route::get('/{any}', function () {
    $setting = Setting::select('primary_color')->first();
    return view('ecommerce.app', compact('setting'));
})->where('any', '.*');