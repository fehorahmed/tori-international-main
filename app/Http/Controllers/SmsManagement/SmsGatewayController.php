<?php

namespace App\Http\Controllers\SmsManagement;

use App\Http\Controllers\Controller;
use App\Models\SmsManagement\SmsGateway;
use App\Services\SmsManagement\Gateways\SendMySmsGateway;
use App\Services\SmsManagement\Gateways\SmsBangladeshGateway;
use App\Services\SmsManagement\Gateways\SslWirelessGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SmsGatewayController extends Controller
{
    public function index()
    {
        $gateways = SmsGateway::all();

        return view('sms_management.gateways.index', compact('gateways'));
    }

    private function configuration(Request $request, ?SmsGateway $gateway = null): array
    {
        $ssl = $request->provider_name === 'SSL Wireless';
        $bd = $request->provider_name === 'SMS Bangladesh';
        $current = $gateway?->provider_name === $request->provider_name ? $gateway : null;
        $data = $request->validate([
            'provider_name' => 'required|string|in:SSL Wireless,SendMySMS,SMS Bangladesh',
            'api_url' => 'required|url|max:255',
            'api_key' => (! $bd && ! $current?->api_key ? 'required' : 'nullable').'|string|max:255',
            'username' => (! $ssl && ! $current?->username ? 'required' : 'nullable').'|string|max:255',
            'password' => ($bd && ! $current?->password ? 'required' : 'nullable').'|string|max:255',
            'sender_id' => ($ssl ? 'required' : 'nullable').'|string|max:20',
        ]);
        if ($ssl) {
            Validator::make($data, ['api_url' => [function ($attribute, $value, $fail) {
                if (! SslWirelessGateway::validUrl($value)) {
                    $fail('Use the SSL Wireless single-SMS endpoint: '.SslWirelessGateway::URL);
                }
            }]])->validate();
            $data['api_key'] = $data['api_key'] ?? $current?->api_key;
            $data['username'] = null;
            $data['password'] = null;
        } elseif ($request->provider_name === 'SMS Bangladesh') {
            Validator::make($data, ['api_url' => [function ($attribute, $value, $fail) {
                if (! SmsBangladeshGateway::validUrl($value)) {
                    $fail('Use the SMS Bangladesh panel or panel2 base URL without /api or /otp.');
                }
            }]])->validate();
            $data['api_key'] = '';
            $data['username'] = $data['username'] ?? $current?->username;
            $data['password'] = $data['password'] ?? $current?->password;
            $data['sender_id'] = $data['sender_id'] ?? null;
        } else {
            $data['api_url'] = SendMySmsGateway::URL;
            $data['api_key'] = $data['api_key'] ?? $current?->api_key;
            $data['username'] = $data['username'] ?? $current?->username;
            $data['password'] = null;
            $data['sender_id'] = null;
        }

        return $data;
    }

    public function store(Request $request)
    {
        SmsGateway::create($this->configuration($request) + ['is_active' => false, 'status' => true, 'created_by' => auth()->id()]);

        return redirect()->back()->with('success', 'SMS Gateway Added Successfully');
    }

    public function update(Request $request, SmsGateway $gateway)
    {
        $gateway->update($this->configuration($request, $gateway) + ['updated_by' => auth()->id()]);

        return redirect()->back()->with('success', 'SMS Gateway Updated Successfully');
    }

    public function makeActive(SmsGateway $gateway)
    {
        abort_unless($gateway->status && in_array($gateway->provider_name, ['SSL Wireless', 'SendMySMS', 'SMS Bangladesh'], true), 422, 'This gateway is unavailable.');
        if ($gateway->provider_name === 'SSL Wireless') {
            abort_unless(SslWirelessGateway::validUrl($gateway->api_url) && $gateway->api_key && $gateway->sender_id, 422, 'Configure the SSL Wireless URL, API token and Sender ID first.');
        }
        if ($gateway->provider_name === 'SMS Bangladesh') {
            abort_unless(SmsBangladeshGateway::validUrl($gateway->api_url) && $gateway->username && $gateway->password, 422, 'Save SMS Bangladesh username/password and a valid base URL in gateway settings first.');
        }
        if ($gateway->provider_name === 'SendMySMS') {
            abort_unless($gateway->username && $gateway->api_key, 422, 'Save SendMySMS username and API key in gateway settings first.');
        }
        DB::transaction(function () use ($gateway) {
            SmsGateway::query()->orderBy('id')->lockForUpdate()->get();
            SmsGateway::where('is_active', true)->update(['is_active' => false]);
            $gateway->update(['is_active' => true]);
        });

        return response()->json(['success' => true, 'message' => 'Gateway activated successfully']);
    }
}
