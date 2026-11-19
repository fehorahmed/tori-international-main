<?php

namespace App\Services\SmsManagement;

use App\Models\SmsManagement\SmsGateway;
use App\Models\SmsManagement\SmsLog;
use App\Models\SmsManagement\SmsTemplate;
use App\Services\SmsManagement\Gateways\SendMySmsGateway;
use App\Services\SmsManagement\Gateways\SmsBangladeshGateway;
use App\Services\SmsManagement\Gateways\SslWirelessGateway;
use Exception;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public static function usesLiveOtp(): bool
    {
        return ! app()->environment(['local', 'testing']) || (bool) config('services.sendmysms.send_in_local');
    }

    /** Send a transactional OTP without requiring a marketing/event template. */
    public function sendSms(string $mobile, string $message): bool
    {
        $gateway = SmsGateway::where('is_active', true)->where('status', true)->first();
        $success = false;
        if ($gateway) {
            $strategyClass = $this->resolveStrategyClass($gateway->provider_name);
            if (class_exists($strategyClass)) {
                try {
                    $strategy = new $strategyClass;
                    $result = $strategy instanceof SmsBangladeshGateway
                        ? $strategy->sendOtp($mobile, $message, $gateway) : $strategy->send($mobile, $message, $gateway);
                    $success = (bool) ($result['success'] ?? false);
                } catch (Exception $e) {
                    Log::error('Transactional SMS delivery failed.');
                }
            }
        }
        SmsLog::create(['event_name' => 'membership_otp', 'mobile_no' => $mobile,
            'message' => 'Membership OTP (redacted)', 'response_text' => in_array($gateway?->provider_name, ['SendMySMS', 'SSL Wireless', 'SMS Bangladesh'], true) ? ($result['response'] ?? 'No active SMS gateway.') : ($success ? 'OTP dispatched' : 'OTP delivery failed'),
            'status' => $success ? 'Sent' : 'Failed', 'sent_by' => null, 'sent_at' => now()]);

        return $success;
    }

    /**
     * Dispatch the SMS. This method is called from the Queue Job.
     *
     * @param  SmsTemplate  $template  The specific template that was matched.
     * @param  string  $mobile  The destination mobile number.
     * @param  string  $parsedMessage  The final string after variable parsing.
     * @param  int|null  $userId  ID of the user who triggered this, if any.
     */
    public function dispatch(SmsTemplate $template, string $mobile, string $parsedMessage, ?int $userId = null): bool
    {
        // Safety Check 1: Ensure the template is active
        if (! $template->is_active) {
            Log::info("SmsService: Aborted sending SMS because template '{$template->title}' is disabled.");

            return false;
        }

        // Fetch the active gateway configuration from the database
        $activeGateway = SmsGateway::where('is_active', true)->where('status', true)->first();

        if (! $activeGateway) {
            Log::error('SmsService: No active SMS gateway found in the database.');
            $this->logSms($template, $mobile, $parsedMessage, 'Failed', 'No active gateway configured', $userId);

            return false;
        }

        // Resolve the strategy class dynamically based on the provider name
        $strategyClass = $this->resolveStrategyClass($activeGateway->provider_name);

        if (! class_exists($strategyClass)) {
            Log::error("SmsService: Gateway Strategy class {$strategyClass} does not exist.");
            $this->logSms($template, $mobile, $parsedMessage, 'Failed', "Strategy class missing for {$activeGateway->provider_name}", $userId);

            return false;
        }

        /** @var SmsGatewayInterface $strategy */
        $strategy = new $strategyClass;

        try {
            // Attempt to send the SMS using the resolved strategy
            $result = $strategy->send($mobile, $parsedMessage, $activeGateway);

            $status = $result['success'] ? 'Sent' : 'Failed';

            // Log the attempt
            $this->logSms($template, $mobile, $parsedMessage, $status, $result['response'], $userId);

            return $result['success'];

        } catch (Exception $e) {
            Log::error('SMS gateway dispatch failed.');
            $this->logSms($template, $mobile, $parsedMessage, 'Failed', 'SMS gateway dispatch failed.', $userId);

            return false;
        }
    }

    /**
     * Helper to map a provider name from DB to its corresponding Strategy class.
     */
    protected function resolveStrategyClass(string $providerName): string
    {
        $map = [
            'SSL Wireless' => SslWirelessGateway::class,
            'SendMySMS' => SendMySmsGateway::class,
            'SMS Bangladesh' => SmsBangladeshGateway::class,
            // Add other gateways here, e.g.:
            // 'BulkSMS BD' => \App\Services\SmsManagement\Gateways\BulkSmsBdGateway::class,
        ];

        return $map[$providerName] ?? '';
    }

    /**
     * Records the SMS history into the sms_logs table.
     */
    protected function logSms(SmsTemplate $template, string $mobile, string $message, string $status, ?string $response = null, ?int $userId = null)
    {
        SmsLog::create([
            'event_name' => $template->event_name,
            'mobile_no' => $mobile,
            'message' => $message,
            'response_text' => $response,
            'status' => $status,
            'sent_by' => $userId ?? auth()->id(),
            'sent_at' => now(),
        ]);
    }
}
