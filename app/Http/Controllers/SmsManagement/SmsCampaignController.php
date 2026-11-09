<?php

namespace App\Http\Controllers\SmsManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SmsManagement\SmsTemplate;
use App\Models\SmsManagement\SmsGateway;
use App\Services\SmsManagement\SmsService;
use Illuminate\Support\Facades\Log;

class SmsCampaignController extends Controller
{
    public function index()
    {
        return view('sms_management.campaign.index');
    }

    public function sendBulk(Request $request, SmsService $smsService)
    {
        $request->validate([
            'numbers' => 'required|string',
            'message' => 'required|string',
        ]);

        $numbers = array_filter(array_map('trim', explode(',', $request->numbers)));
        if (empty($numbers)) {
            return redirect()->back()->with('error', 'No valid numbers provided.');
        }

        $activeGateway = SmsGateway::where('is_active', true)->where('status', true)->first();
        if (!$activeGateway) {
            return redirect()->back()->with('error', 'No active SMS Gateway found. Please configure one first.');
        }

        // We create a dummy template for bulk campaigns so it bypasses template active check
        // Or we just call the Gateway Strategy directly to avoid template constraints.
        // For logging sake, we use SmsService but with a dummy template instance.
        
        $dummyTemplate = new SmsTemplate([
            'event_name' => 'bulk_sms',
            'is_active' => true,
        ]);

        $successCount = 0;
        $failCount = 0;

        foreach ($numbers as $number) {
            // Validate basic BD number pattern (very basic)
            if (strlen($number) < 11) {
                $failCount++;
                continue;
            }

            // dispatch to background job in a real scenario, but for simplicity here we do dispatch directly
            // \App\Jobs\SmsManagement\SendSmsJob::dispatch($dummyTemplate, $number, $request->message, auth()->id());
            
            // To provide immediate feedback, we will just dispatch the Job
            \App\Jobs\SmsManagement\SendSmsJob::dispatch($dummyTemplate, $number, $request->message, auth()->id());
            $successCount++;
        }

        return redirect()->back()->with('success', "Campaign Started! $successCount messages queued for delivery.");
    }
}
