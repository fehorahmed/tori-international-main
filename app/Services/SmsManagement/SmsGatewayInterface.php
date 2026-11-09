<?php

namespace App\Services\SmsManagement;

use App\Models\SmsManagement\SmsGateway;

interface SmsGatewayInterface
{
    /**
     * Send an SMS using the specific gateway implementation.
     *
     * @param string $mobile
     * @param string $message
     * @param SmsGateway $gatewayConfig The DB config for this gateway
     * @return array Returns an array like ['success' => true/false, 'response' => 'API response']
     */
    public function send(string $mobile, string $message, SmsGateway $gatewayConfig): array;
}
