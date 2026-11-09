<?php

namespace App\Http\Controllers\SmsManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SmsManagement\SmsLog;
use App\Models\SmsManagement\SmsGateway;
use App\Models\SmsManagement\SmsTemplate;

class SmsDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_sent' => SmsLog::where('status', 'Sent')->count(),
            'total_failed' => SmsLog::where('status', 'Failed')->count(),
            'active_gateway' => SmsGateway::where('is_active', true)->first(),
            'active_templates' => SmsTemplate::where('is_active', true)->count(),
        ];

        $recentLogs = SmsLog::orderBy('id', 'desc')->limit(5)->get();

        return view('sms_management.dashboard.index', compact('stats', 'recentLogs'));
    }
}
