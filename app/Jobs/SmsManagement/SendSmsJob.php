<?php

namespace App\Jobs\SmsManagement;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\SmsManagement\SmsTemplate;
use App\Services\SmsManagement\SmsService;

class SendSmsJob implements ShouldQueue
{
    use Queueable;

    protected SmsTemplate $template;
    protected string $mobile;
    protected string $parsedMessage;
    protected ?int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(SmsTemplate $template, string $mobile, string $parsedMessage, ?int $userId = null)
    {
        $this->template = $template;
        $this->mobile = $mobile;
        $this->parsedMessage = $parsedMessage;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService): void
    {
        $smsService->dispatch($this->template, $this->mobile, $this->parsedMessage, $this->userId);
    }
}
