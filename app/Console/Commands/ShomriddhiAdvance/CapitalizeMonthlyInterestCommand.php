<?php

namespace App\Console\Commands\ShomriddhiAdvance;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
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

        if (!now()->isLastDayOfMonth()) {
            $this->info("Today is not the last day of the month. Skipping.");
            return Command::SUCCESS;
        }

        $currentMonth = now()->format('Y-m');

        // Find all accounts that have accrued interest to capitalize
        ShomriddhiAccount::where('status', 'Active')
            ->where('accrued_interest', '>=', 0.01)
            ->chunkById(500, function ($accounts) use ($capitalizationService, $currentMonth) {
                foreach ($accounts as $account) {
                    // Check if this account already had capitalization THIS month
                    $alreadyCapitalized = ShomriddhiTransaction::where('customer_id', $account->customer_id)
                        ->where('type', 'interest_capitalization')
                        ->where('date', 'like', $currentMonth . '-%')
                        ->exists();

                    if (!$alreadyCapitalized) {
                        $capitalizationService->capitalize($account);
                    }
                }
            });

        $this->info("Monthly Interest Capitalization completed successfully.");
        return Command::SUCCESS;
    }
}
