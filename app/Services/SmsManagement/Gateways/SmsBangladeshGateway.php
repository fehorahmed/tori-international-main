<?php

namespace App\Services\SmsManagement\Gateways;

use App\Models\SmsManagement\SmsGateway;
use App\Services\SmsManagement\SmsGatewayInterface;
use Illuminate\Support\Facades\Http;

class SmsBangladeshGateway implements SmsGatewayInterface
{
    public static function validUrl(string $url): bool
    {
        return in_array(rtrim($url, '/'), ['https://panel.smsbangladesh.com', 'https://panel2.smsbangladesh.com',
            'http://panel.smsbangladesh.com', 'http://panel2.smsbangladesh.com'], true);
    }

    public function send(string $mobile, string $message, SmsGateway $gatewayConfig): array
    {
        return $this->deliver($mobile, $message, $gatewayConfig, false);
    }

    public function sendOtp(string $mobile, string $message, SmsGateway $gatewayConfig): array
    {
        return $this->deliver($mobile, $message, $gatewayConfig, true);
    }

    private function deliver(string $mobile, string $message, SmsGateway $gateway, bool $otp): array
    {
        $numbers = array_map(fn ($value) => SslWirelessGateway::normalizeMobile($value), explode(',', $mobile));
        if (in_array(null, $numbers, true) || ($otp && count($numbers) !== 1)) {
            return ['success' => false, 'response' => 'Invalid Bangladesh mobile number.'];
        }
        if (trim($message) === '') {
            return ['success' => false, 'response' => 'Message cannot be blank.'];
        }
        $user = $gateway->username;
        $password = $gateway->password;
        if (! $user || ! $password || ! self::validUrl($gateway->api_url)) {
            return ['success' => false, 'response' => 'Configure SMS Bangladesh credentials and base URL first.'];
        }
        try {
            $parameters = ['user' => $user, 'password' => $password, 'to' => implode(',', $numbers), 'text' => $message];
            if ($gateway->sender_id) {
                $parameters['from'] = $gateway->sender_id;
            }
            // Provider requires GET. Never persist URLs, raw provider messages or exceptions.
            $response = Http::connectTimeout(5)->timeout(15)->withOptions(['allow_redirects' => false])
                ->get(rtrim($gateway->api_url, '/').($otp ? '/otp' : '/api'), $parameters);
            $data = $response->json();
            $code = is_array($data) ? (int) ($data['response_code'] ?? 0) : 0;
            $success = $response->successful() && is_array($data) && (int) ($data['success'] ?? 0) === 1 && $code === 100;
            $statuses = [];
            if ($otp) {
                $success = $success && in_array(strtolower((string) ($data['status'] ?? '')), ['sent', 'delivered'], true);
                $statuses[$numbers[0]] = $success;
            } else {
                foreach (($data['delivery_status'] ?? []) as $row) {
                    $number = SslWirelessGateway::normalizeMobile((string) ($row['MSISDN'] ?? ''));
                    if (in_array($number, $numbers, true)) {
                        $statuses[$number] = $success && (int) ($row['Status'] ?? 0) === 1;
                    }
                }
                foreach ($numbers as $number) {
                    if (! ($statuses[$number] ?? false)) {
                        $success = false;
                    }
                }
            }
            $errors = [101 => 'SMS username is missing.', 102 => 'SMS password is missing.', 103 => 'Recipient is missing.',
                104 => 'Message is missing.', 105 => 'Invalid SMS Bangladesh credentials.', 106 => 'Insufficient SMS balance.',
                107 => 'Insufficient reseller balance.', 108 => 'SMS message is too long.'];

            return ['success' => $success, 'response' => json_encode(['response_code' => $code,
                'message' => $success ? 'SMS Bangladesh accepted the SMS.' : ($errors[$code] ?? 'SMS Bangladesh did not confirm sending.'),
                'recipients' => $statuses])];
        } catch (\Throwable) {
            return ['success' => false, 'response' => 'SMS Bangladesh could not be reached; delivery is unconfirmed.'];
        }
    }
}
