<?php

namespace App\Services\SmsManagement\Gateways;

use App\Models\SmsManagement\SmsGateway;
use App\Services\SmsManagement\SmsGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SslWirelessGateway implements SmsGatewayInterface
{
    public function send(string $mobile, string $message, SmsGateway $gatewayConfig): array
    {
        try {
            // Adjust the payload structure based on the actual SSL Wireless API documentation.
            $payload = [
                'api_token' => $gatewayConfig->api_key,
                'sid'       => $gatewayConfig->sender_id,
                'sms'       => $message,
                'msisdn'    => $mobile,
                'csms_id'   => uniqid('sms_'),
            ];

            $response = Http::post($gatewayConfig->api_url, $payload);

            if ($response->successful()) {
                // Determine true success based on API response structure.
                // Assuming successful if HTTP 200 and some success code in JSON.
                $responseData = $response->json();
                $isSuccess = isset($responseData['status']) && $responseData['status'] === 'SUCCESS';

                return [
                    'success' => $isSuccess,
                    'response' => $response->body(),
                ];
            }

            return [
                'success' => false,
                'response' => $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('SslWirelessGateway Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'response' => $e->getMessage(),
            ];
        }
    }
}
