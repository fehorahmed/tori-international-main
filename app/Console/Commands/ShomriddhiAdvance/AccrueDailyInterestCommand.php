<?php

namespace App\Console\Commands\ShomriddhiAdvance;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiDailyInterest;
use App\Services\ShomriddhiAdvance\DynamicInterestSlabService;
use App\Services\ShomriddhiAdvance\InterestCalculationService;
use Carbon\Carbon;

#[Signature('shomriddhi:accrue-daily-interest')]
#[Description('Calculate and accrue daily interest for active Shomriddhi accounts')]
class AccrueDailyInterestCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(DynamicInterestSlabService $slabService, InterestCalculationService $calcService)
    {
        $today = Carbon::today()->toDateString();
        
        $this->info("Starting Daily Interest Accrual for {$today}");

        // Using chunkById prevents memory leaks if there are thousands of accounts
        ShomriddhiAccount::where('status', 'Active')
            ->where('principal_balance', '>=', 1000) // Minimum balance required (Tier 1 starts at 1000)
            ->where(function($query) use ($today) {
                // Only process accounts that haven't been processed today
                $query->where('last_interest_date', '<', $today)
                      ->orWhereNull('last_interest_date');
            })
            ->chunkById(500, function ($accounts) use ($slabService, $calcService, $today) {
                $logsToInsert = [];

                foreach ($accounts as $account) {
                    $rate = $slabService->getRateForAmount($account->principal_balance);
                    
                    if ($rate > 0) {
                        // Catch-up logic: If the server was down or missed days, calculate for all missing days
                        $startDate = $account->last_interest_date 
                            ? Carbon::parse($account->last_interest_date)->addDay() 
                            : Carbon::parse($today);
                        $endDate = Carbon::parse($today);

                        // Loop through each missing day and prepare log data
                        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                            $dailyInterest = $calcService->calculateDailyInterest($account->principal_balance, $rate);
                            $account->accrued_interest += $dailyInterest;

                            $logsToInsert[] = [
                                'origin_sister_concern_id' => $account->origin_sister_concern_id,
                                'customer_id' => $account->customer_id,
                                'date' => $date->toDateString(),
                                'principal_balance' => $account->principal_balance,
                                'interest_rate' => $rate,
                                'earned_interest' => $dailyInterest,
                                'status' => 'Pending',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                        
                        $account->last_interest_date = $today;
                        $account->save();
                    }
                }

                // Batch insert all logs for this chunk in a single query (Ultra-Fast)
                if (!empty($logsToInsert)) {
                    ShomriddhiDailyInterest::insert($logsToInsert);
                }
            });

        $this->info("Daily Interest Accrual completed successfully.");
        return Command::SUCCESS;
    }
}
