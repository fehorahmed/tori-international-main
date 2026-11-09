<?php 
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\MonitoringController;
use App\Http\Controllers\LeadWebhookController;
use Illuminate\Support\Facades\Route;


Route::get('leads/webhook/messenger', [LeadWebhookController::class, 'messengerWebhook'])->name('leads.webhook_messenger');
Route::post('leads/webhook/messenger', [LeadWebhookController::class, 'messengerWebhookPost']);    
Route::get('leads/webhook/whatsapp', [LeadWebhookController::class, 'whatsappWebhook'])->name('leads.webhook_whatsapp');
Route::post('leads/webhook/whatsapp', [LeadWebhookController::class, 'whatsappWebhookPost']);
Route::get('leads/webhook/instagram', [LeadWebhookController::class, 'instagramWebhook'])->name('leads.webhook_instagram');
Route::post('leads/webhook/instagram', [LeadWebhookController::class, 'instagramWebhookPost']);

// Authentication APIs (Mobile)
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // Monitoring Protected APIs (Flutter)
    Route::post('monitoring/location/update', [MonitoringController::class, 'updateLocation']);
    Route::get('monitoring/stats', [MonitoringController::class, 'getStats']);
    Route::get('monitoring/history', [MonitoringController::class, 'getHistory']);

});


require __DIR__ . '/ecommerce_api.php';

/*
|--------------------------------------------------------------------------
| Dedicated Mobile App Routes
|--------------------------------------------------------------------------
*/
// 1. Tori Membership (Customer MFI & Digital Banking App)
Route::prefix('membership')->group(base_path('routes/api/tori_membership.php'));

// 2. Tori Field Officer / Employee App (Future)
// Route::prefix('employee')->group(base_path('routes/api/tori_employee.php'));

// 3. Tori Admin & Branch Manager App (Future)
// Route::prefix('admin')->group(base_path('routes/api/tori_admin.php'));
