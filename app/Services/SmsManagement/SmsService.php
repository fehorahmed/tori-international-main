<?php

namespace App\Services\SmsManagement;

use App\Models\SmsManagement\SmsGateway;
use App\Models\SmsManagement\SmsLog;
use App\Models\SmsManagement\SmsTemplate;
use Illuminate\Support\Facades\Log;
use Exception;

class SmsService
{
    /**
     * Dispatch the SMS. This method is called from the Queue Job.
     *
     * @param SmsTemplate $template The specific template that was matched.
     * @param string $mobile The destination mobile number.
     * @param string $parsedMessage The final string after variable parsing.
     * @param int|null $userId ID of the user who triggered this, if any.
     * @return bool
     */
    public function dispatch(SmsTemplate $template, string $mobile, string $parsedMessage, ?int $userId = null): bool
    {
        // Safety Check 1: Ensure the template is active
        if (!$template->is_active) {
            Log::info("SmsService: Aborted sending SMS because template '{$template->title}' is disabled.");
            return false;
        }

        // Fetch the active gateway configuration from the database
        $activeGateway = SmsGateway::where('is_active', true)->where('status', true)->first();

        if (!$activeGateway) {
            Log::error('SmsService: No active SMS gateway found in the database.');
            $this->logSms($template, $mobile, $parsedMessage, 'Failed', 'No active gateway configured', $userId);
            return false;
        }

        // Resolve the strategy class dynamically based on the provider name
        $strategyClass = $this->resolveStrategyClass($activeGateway->provider_name);

        if (!class_exists($strategyClass)) {
            Log::error("SmsService: Gateway Strategy class {$strategyClass} does not exist.");
            $this->logSms($template, $mobile, $parsedMessage, 'Failed', "Strategy class missing for {$activeGateway->provider_name}", $userId);
            return false;
        }

        /** @var SmsGatewayInterface $strategy */
        $strategy = new $strategyClass();

        try {
            // Attempt to send the SMS using the resolved strategy
            $result = $strategy->send($mobile, $parsedMessage, $activeGateway);

            $status = $result['success'] ? 'Sent' : 'Failed';
            
            // Log the attempt
            $this->logSms($template, $mobile, $parsedMessage, $status, $result['response'], $userId);

            return $result['success'];

        } catch (Exception $e) {
            Log::error("SmsService Dispatch Error: " . $e->getMessage());
            $this->logSms($template, $mobile, $parsedMessage, 'Failed', $e->getMessage(), $userId);
            return false;
        }
    }

    /**
     * Helper to map a provider name from DB to its corresponding Strategy class.
     * 
     * @param string $providerName
     * @return string
     */
    protected function resolveStrategyClass(string $providerName): string
    {
        $map = [
            'SSL Wireless' => \App\Services\SmsManagement\Gateways\SslWirelessGateway::class,
            // Add other gateways here, e.g.:
            // 'BulkSMS BD' => \App\Services\SmsManagement\Gateways\BulkSmsBdGateway::class,
        ];

        return $map[$providerName] ?? \App\Services\SmsManagement\Gateways\SslWirelessGateway::class;
    }

    /**
     * Records the SMS history into the sms_logs table.
     */
    protected function logSms(SmsTemplate $template, string $mobile, string $message, string $status, string $response = null, ?int $userId = null)
    {
        SmsLog::create([
            'event_name'    => $template->event_name,
            'mobile_no'     => $mobile,
            'message'       => $message,
            'response_text' => $response,
            'status'        => $status,
            'sent_by'       => $userId ?? auth()->id(),
            'sent_at'       => now(),
        ]);
    }
}
