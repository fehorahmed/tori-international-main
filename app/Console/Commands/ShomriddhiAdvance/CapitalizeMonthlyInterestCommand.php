<?php

namespace App\Console\Commands\ShomriddhiAdvance;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use Illuminate\Support\Facades\Log;
use App\Services\ShomriddhiAdvance\CapitalizationService;

#[Signature('shomriddhi:capitalize-monthly-interest')]
#[Description('Capitalize accumulated accrued interest to principal balance at the end of the month')]
class CapitalizeMonthlyInterestCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(CapitalizationService $capitalizationService)
    {
        $this->info("Starting Monthly Interest Capitalization...");

        $failed = 0;

        // Find all accounts that have accrued interest to capitalize
        ShomriddhiAccount::where('status', 'Active')
            ->where('accrued_interest', '>=', 0.01)
            ->chunkById(500, function ($accounts) use ($capitalizationService, &$failed) {
                foreach ($accounts as $account) {
                    try {
                        $capitalizationService->recoverMonthly($account);
                    } catch (\Throwable $e) {
                        $failed++;
                        Log::error('Monthly interest capitalization failed.', ['account_id' => $account->id, 'exception' => $e]);
                        $this->error("Account {$account->id}: interest recovery incomplete; check logs and retry.");
                    }
                }
            });

        if ($failed) {
            $this->error("Monthly interest capitalization failed for {$failed} account(s). Check logs and retry.");
            return Command::FAILURE;
        }
        $this->info("Monthly Interest Capitalization completed successfully.");
        return Command::SUCCESS;
    }
}
