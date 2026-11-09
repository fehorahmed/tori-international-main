<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('mf:daily-cron')]
#[Description('Apply overdue loan penalties and post savings interest.')]
class MicrofinanceDailyCron extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(\App\Services\Microfinance\LoanService $loanService, \App\Services\Microfinance\SavingsAccountService $savingsService)
    {
        $this->info('Starting Microfinance daily processes...');

        // 1. Overdue Loan Penalties
        $this->info('Applying loan penalties...');
        $penaltiesCount = $loanService->applyPenalties();
        $this->info("Penalties applied: {$penaltiesCount} installment(s).");

        // 2. Savings Interest Posting & Accrual
        // Post monthly/quarterly/yearly interest for FDR, and accumulate daily interest for General Savings
        $this->info('Processing savings interest (Accruals & Postings)...');
        $savingsCount = 0;
        $today = \Carbon\Carbon::today();
        $isAnnualPostingDay = ($today->month == 6 && $today->day == 30);

        \App\Models\Microfinance\MfSavingsAccount::where('status', 1)
            ->with('product')
            ->chunk(100, function ($accounts) use ($savingsService, $today, $isAnnualPostingDay, &$savingsCount) {
                foreach ($accounts as $account) {
                    $product = $account->product;

                    if ($product->product_type == 1) {
                        // General Savings: 1. Daily Product Accrual
                        $dailyInterest = round(($account->current_balance * $account->interest_rate) / 100 / 365, 4);
                        if ($dailyInterest > 0) {
                            $account->increment('accrued_interest', $dailyInterest);
                        }

                        // General Savings: 2. Annual Posting (June 30)
                        if ($isAnnualPostingDay) {
                            $finalInterest = round($account->fresh()->accrued_interest, 2);
                            if ($finalInterest > 0) {
                                try {
                                    $savingsService->postAnnualInterest($account, $finalInterest);
                                    $savingsCount++;
                                } catch (\Exception $e) {
                                    $this->error("Error posting annual interest for Account {$account->account_no}: " . $e->getMessage());
                                }
                            }
                        }
                    } elseif ($product->product_type == 3) {
                        // FDR: 1=Maturity, 2=Monthly, 3=Quarterly, 4=Yearly
                        $postingType = $product->interest_posting_type;
                        $shouldPostFDR = false;

                        if ($postingType == 2 && $today->day == 1) {
                            $shouldPostFDR = true;
                        } elseif ($postingType == 3 && $today->day == 1 && in_array($today->month, [1, 4, 7, 10])) {
                            $shouldPostFDR = true;
                        } elseif ($postingType == 4 && $today->day == 1 && $today->month == 1) {
                            $shouldPostFDR = true;
                        } elseif ($postingType == 1 && $account->maturity_date && $today->isSameDay($account->maturity_date)) {
                            $shouldPostFDR = true;
                        }

                        if ($shouldPostFDR) {
                            try {
                                $savingsService->postInterest($account);
                                $savingsCount++;
                            } catch (\Exception $e) {
                                $this->error("Error posting FDR interest for Account {$account->account_no}: " . $e->getMessage());
                            }
                        }
                    }
                }
            });

        $this->info("Interest processed successfully! {$savingsCount} account(s) posted/updated.");
        $this->info('Daily processes completed successfully!');
    }
}
