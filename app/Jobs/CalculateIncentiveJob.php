<?php

namespace App\Jobs;

use App\Models\Sales\SaleOrder;
use App\Services\IncentiveManagement\IncentiveCalculationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalculateIncentiveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int — Job fail হলে কতবার retry করবে */
    public int $tries = 3;

    /** @var int — retry এর মাঝে কত সেকেন্ড অপেক্ষা */
    public int $backoff = 5;

    public function __construct(
        public readonly SaleOrder $saleOrder
    ) {}

    public function handle(IncentiveCalculationService $service): void
    {
        try {
            $service->processForSale($this->saleOrder);
        } catch (\Throwable $e) {
            Log::error('[CalculateIncentiveJob] Failed', [
                'sale_order_id' => $this->saleOrder->id,
                'error'         => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);

            // Job queue এ fail mark করো যাতে retry হয়
            $this->fail($e);
        }
    }

    /**
     * Sales Return এর জন্য — SaleReturn module তৈরি হলে uncomment করবেন।
     *
     * @todo Enable after SaleReturn module implementation
     */
    // public static function dispatchForReturn($saleReturn): void
    // {
    //     static::dispatch($saleReturn)->onQueue('incentive');
    // }
}
