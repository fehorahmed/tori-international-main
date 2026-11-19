<?php

namespace App\Services\SmsManagement\Gateways;

use App\Models\SmsManagement\SmsGateway;
use App\Services\SmsManagement\SmsGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SslWirelessGateway implements SmsGatewayInterface
{
    public const URL = 'https://smsplus.sslwireless.com/api/v3/send-sms';

    public static function validUrl(string $url): bool
    {
        return rtrim($url, '/') === self::URL;
    }

    public static function normalizeMobile(string $mobile): ?string
    {
        $mobile = preg_replace('/[\s+()-]/', '', trim($mobile));
        if (preg_match('/^01[3-9]\d{8}$/', $mobile)) {
            $mobile = '88'.$mobile;
        }
        if (preg_match('/^1[3-9]\d{8}$/', $mobile)) {
            $mobile = '880'.$mobile;
        }

        return preg_match('/^8801[3-9]\d{8}$/', $mobile) ? $mobile : null;
    }

    public function send(string $mobile, string $message, SmsGateway $gatewayConfig): array
    {
        $number = self::normalizeMobile($mobile);
        if (! $number) {
            return ['success' => false, 'response' => 'Invalid Bangladesh mobile number.'];
        }
        if (trim($message) === '') {
            return ['success' => false, 'response' => 'Message cannot be blank.'];
        }
        if (! self::validUrl($gatewayConfig->api_url) || ! $gatewayConfig->api_key || ! $gatewayConfig->sender_id) {
            return ['success' => false, 'response' => 'SSL Wireless API URL, token or Sender ID is missing or invalid.'];
        }
        try {
            // No retries or redirects: avoid duplicate sends and credential forwarding.
            $response = Http::acceptJson()->connectTimeout(5)->timeout(15)->withOptions(['allow_redirects' => false])
                ->post(self::URL, ['api_token' => $gatewayConfig->api_key, 'sid' => $gatewayConfig->sender_id,
                    'sms' => $message, 'msisdn' => $number, 'csms_id' => Str::random(20)]);
            $data = $response->json();
            $rows = is_array($data) && is_array($data['smsinfo'] ?? null) ? $data['smsinfo'] : [];
            $row = count($rows) === 1 ? array_values($rows)[0] : null;
            $success = $response->successful() && is_array($data) && ($data['status'] ?? '') === 'SUCCESS'
                && (int) ($data['status_code'] ?? 0) === 200 && is_array($row)
                && ($row['sms_status'] ?? '') === 'SUCCESS'
                && self::normalizeMobile((string) ($row['msisdn'] ?? '')) === $number;
            $code = is_array($data) ? (int) ($data['status_code'] ?? 0) : 0;
            $errors = [4001 => 'Invalid SSL Wireless credentials.', 4002 => 'Sender ID is not permitted.',
                4003 => 'Server IP is not whitelisted.', 4004 => 'Invalid request format.', 4005 => 'Invalid API endpoint.',
                4020 => 'Invalid client SMS reference.', 4022 => 'Required SMS parameter is missing.',
                4023 => 'Duplicate SMS reference.', 4024 => 'Duplicate mobile number.', 4025 => 'Invalid mobile number.',
                4026 => 'Recipient is blocked.', 4027 => 'SMS message is too long.', 4028 => 'Invalid SMS data.',
                4035 => 'This SSL account requires the signed API.'];
            $reference = $success ? (string) ($row['reference_id'] ?? '') : '';
            $safe = ['status_code' => $code, 'message' => $success ? 'SSL Wireless accepted the SMS.' : ($errors[$code] ?? 'SSL Wireless did not confirm sending.')];
            if (preg_match('/^[a-zA-Z0-9_-]{1,128}$/', $reference)) {
                $safe['reference_id'] = $reference;
            }

            // Provider responses can echo SMS text; only whitelisted metadata is retained.
            return ['success' => $success, 'response' => json_encode($safe)];
        } catch (\Throwable) {
            return ['success' => false, 'response' => 'SSL Wireless could not be reached; delivery is unconfirmed.'];
        }
    }
}
