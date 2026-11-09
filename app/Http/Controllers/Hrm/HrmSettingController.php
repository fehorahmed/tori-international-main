<?php

namespace App\Http\Controllers\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Hrm\HrmSetting;
use Illuminate\Http\Request;

class HrmSettingController extends Controller
{
    public function index()
    {
        // Get the first setting row or create a default one
        $setting = HrmSetting::firstOrCreate(
            ['id' => 1],
            ['notification_interval_minutes' => 60, 'response_window_minutes' => 5]
        );

        return view('hrm.settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'notification_interval_minutes' => 'required|integer|min:1',
            'response_window_minutes'     => 'required|integer|min:1',
        ]);

        $setting = HrmSetting::first();
        if (!$setting) {
            $setting = new HrmSetting();
        }

        $setting->notification_interval_minutes = $request->notification_interval_minutes;
        $setting->response_window_minutes = $request->response_window_minutes;
        $setting->is_web_notification_enabled = $request->has('is_web_notification_enabled') ? 1 : 0;
        $setting->is_browser_notification_enabled = $request->has('is_browser_notification_enabled') ? 1 : 0;
        $setting->is_push_notification_enabled = $request->has('is_push_notification_enabled') ? 1 : 0;
        
        $setting->fcm_api_key = $request->fcm_api_key;
        $setting->fcm_auth_domain = $request->fcm_auth_domain;
        $setting->fcm_project_id = $request->fcm_project_id;
        $setting->fcm_storage_bucket = $request->fcm_storage_bucket;
        $setting->fcm_messaging_sender_id = $request->fcm_messaging_sender_id;
        $setting->fcm_app_id = $request->fcm_app_id;
        $setting->fcm_measurement_id = $request->fcm_measurement_id;
        
        if ($request->filled('fcm_server_key') && $request->fcm_server_key !== '********') {
            $setting->fcm_server_key = $request->fcm_server_key;
        }

        $setting->save();

        return redirect()->back()->with('message', 'HRM Settings updated successfully.');
    }
}
