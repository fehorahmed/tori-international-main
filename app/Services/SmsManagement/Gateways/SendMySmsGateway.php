<?php

namespace App\Services\SmsManagement\Gateways;

use App\Models\SmsManagement\SmsGateway;
use App\Services\SmsManagement\SmsGatewayInterface;
use Illuminate\Support\Facades\Http;

class SendMySmsGateway implements SmsGatewayInterface
{
    public const URL = 'https://sendmysms.net/api.php';

    public static function normalizeMobile(string $mobile): ?string
    {
        $mobile = preg_replace('/[\s+()-]/', '', trim($mobile));
        if (str_starts_with($mobile, '880')) {
            $mobile = substr($mobile, 2);
        }
        if (preg_match('/^1[3-9]\d{8}$/', $mobile)) {
            $mobile = '0'.$mobile;
        }

        return preg_match('/^01[3-9]\d{8}$/', $mobile) ? $mobile : null;
    }

    public function send(string $mobile, string $message, SmsGateway $gatewayConfig): array
    {
        $number = self::normalizeMobile($mobile);
        if (! $number) {
            return ['success' => false, 'response' => 'Incorrect phone number.'];
        }
        if (trim($message) === '') {
            return ['success' => false, 'response' => 'Message cannot be blank.'];
        }
        $user = $gatewayConfig->username;
        $key = $gatewayConfig->api_key;
        if (! $user || ! $key) {
            return ['success' => false, 'response' => 'SendMySMS username or API key is missing from gateway settings.'];
        }
        try {
            // POST keeps credentials out of URLs. No retry: a timeout may follow acceptance.
            $response = Http::asForm()->connectTimeout(5)->timeout(15)->withOptions(['allow_redirects' => false])
                ->post(self::URL, ['user' => $user, 'key' => $key, 'to' => $number, 'msg' => $message]);
            $data = $response->json();
            $success = $response->successful() && is_array($data) && ($data['status'] ?? null) === 'OK';
            if ($success) {
                return ['success' => true, 'response' => 'SendMySMS accepted the SMS.'];
            }
            $errors = ['Wrong username or API-Key' => 'Invalid SMS credentials.', 'Incorrect phone number' => 'Incorrect phone number.',
                'Message can not be blank' => 'Message cannot be blank.', 'You do not have enough SMS credit' => 'Insufficient SMS credit.'];

            return ['success' => false, 'response' => $errors[$data['response'] ?? ''] ?? 'SMS provider did not confirm sending.'];
        } catch (\Throwable) {
            // Never record request bodies, API keys, OTP text or raw exceptions/responses.
            return ['success' => false, 'response' => 'SMS provider could not be reached; delivery is unconfirmed.'];
        }
    }
}
