<?php

namespace App\Http\Controllers\SmsManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SmsManagement\SmsGateway;

class SmsGatewayController extends Controller
{
    public function index()
    {
        $gateways = SmsGateway::all();
        return view('sms_management.gateways.index', compact('gateways'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'provider_name' => 'required|string',
            'api_url'       => 'required|url',
            'api_key'       => 'required|string',
            'sender_id'     => 'nullable|string',
        ]);

        SmsGateway::create([
            'provider_name' => $request->provider_name,
            'api_url'       => $request->api_url,
            'api_key'       => $request->api_key,
            'sender_id'     => $request->sender_id,
            'is_active'     => false,
            'status'        => true,
            'created_by'    => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'SMS Gateway Added Successfully');
    }

    public function update(Request $request, SmsGateway $gateway)
    {
        $request->validate([
            'provider_name' => 'required|string',
            'api_url'       => 'required|url',
            'api_key'       => 'required|string',
            'sender_id'     => 'nullable|string',
        ]);

        $gateway->update([
            'provider_name' => $request->provider_name,
            'api_url'       => $request->api_url,
            'api_key'       => $request->api_key,
            'sender_id'     => $request->sender_id,
            'updated_by'    => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'SMS Gateway Updated Successfully');
    }

    public function makeActive(SmsGateway $gateway)
    {
        // Set all to false first
        SmsGateway::where('is_active', true)->update(['is_active' => false]);
        
        // Set selected to true
        $gateway->update(['is_active' => true]);

        return response()->json(['success' => true, 'message' => 'Gateway activated successfully']);
    }
}
