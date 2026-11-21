<?php

namespace App\Console\Commands\ShomriddhiAdvance;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Services\ShomriddhiAdvance\DailyInterestAccrualService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

#[Signature('shomriddhi:accrue-daily-interest')]
#[Description('Calculate and accrue daily interest for active Shomriddhi accounts')]
class AccrueDailyInterestCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(DailyInterestAccrualService $accrual)
    {
        $today = Carbon::today()->toDateString();
        
        $this->info("Starting Daily Interest Accrual for {$today}");

        $failed = 0;
        // Using chunkById prevents memory leaks if there are thousands of accounts
        ShomriddhiAccount::where('status', 'Active')
            ->where(function($query) use ($today) {
                // Only process accounts that haven't been processed today
                $query->where('last_interest_date', '<', $today)
                      ->orWhereNull('last_interest_date');
            })
            ->chunkById(500, function ($accounts) use ($accrual, $today, &$failed) {
                foreach ($accounts as $account) {
                    try {
                        $accrual->accrue($account->id, $today);
                    } catch (\Throwable $e) {
                        $failed++;
                        Log::error('Daily interest recovery failed.', ['account_id' => $account->id, 'exception' => $e]);
                        $this->error("Account {$account->id}: {$e->getMessage()}");
                    }
                }
            });

        if ($failed) {
            $this->error("Daily interest recovery failed for {$failed} account(s). Reconcile history and retry.");
            return Command::FAILURE;
        }
        $this->info("Daily Interest Accrual completed successfully.");
        return Command::SUCCESS;
    }
}
