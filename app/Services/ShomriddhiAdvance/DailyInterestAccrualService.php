<?php

namespace App\Services\ShomriddhiAdvance;

use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiDailyInterest;
use Carbon\Carbon;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Services\Membership\CashoutService;
use Illuminate\Support\Facades\DB;

class DailyInterestAccrualService
{
    public function __construct(
        private DynamicInterestSlabService $slabs,
        private InterestCalculationService $calculator,
    ) {}

    public function accrue(int $accountId, string $throughDate): void
    {
        DB::transaction(function () use ($accountId, $throughDate) {
            // Reload after acquiring the lock; never accrue from a stale chunk row.
            $account = ShomriddhiAccount::whereKey($accountId)->lockForUpdate()->first();
            if (! $account || $account->status !== 'Active'
                || ($account->last_interest_date && $account->last_interest_date >= $throughDate)) {
                return;
            }
            $start = $account->last_interest_date
                ? Carbon::parse($account->last_interest_date)->addDay()
                : Carbon::parse($account->opening_date ?? $account->created_at)->startOfDay();
            $end = Carbon::parse($throughDate);
            if ($start->gt($end)) return;
            app(InterestRateHistoryService::class)->lock();
            $changes = [];
            if ($start->lt(Carbon::today())) {
                // Reconstruct accounting-date closing balances only when the complete
                // approved savings journal reconciles to the current principal.
                if (ShomriddhiAccount::where('customer_id', $account->customer_id)->count() !== 1) {
                    throw new \RuntimeException('Historical balance recovery requires exactly one savings account.');
                }
                $entries = ShomriddhiTransaction::where('customer_id', $account->customer_id)->where('status', 'Approved')
                    ->selectRaw('date, type, SUM(amount) as total')->groupBy('date', 'type')->get();
                $total = 0;
                foreach ($entries as $entry) {
                    $sign = match ($entry->type) { 'deposit', 'interest_capitalization' => 1, 'withdraw' => -1,
                        default => throw new \RuntimeException('Unknown savings journal type; cannot reconstruct historical balance.') };
                    $delta = CashoutService::minor((string) $entry->total) * $sign;
                    $total += $delta;
                    $key = Carbon::parse($entry->date)->toDateString();
                    $changes[$key] = ($changes[$key] ?? 0) + $delta;
                }
                if ($total !== CashoutService::minor($account->principal_balance)) {
                    throw new \RuntimeException('Savings journal does not reconcile to principal; historical recovery requires reconciliation.');
                }
            }
            $logs = [];
            for ($date = $start; $date->lte($end); $date->addDay()) {
                $day = $date->toDateString();
                $principal = CashoutService::minor($account->principal_balance);
                foreach ($changes as $changeDate => $delta) {
                    if ($changeDate > $day) $principal -= $delta;
                }
                if ($principal < 0) throw new \RuntimeException('Historical savings balance is negative; reconciliation required.');
                $balance = CashoutService::money($principal);
                $rate = $principal >= 100000 ? $this->slabs->getRateForAmountOn($balance, $day) : 0;
                $interest = $this->calculator->calculateDailyInterest((float) $balance, $rate);
                // Match the ledger's DECIMAL(15,4) precision for each daily entry.
                $interest = round($interest, 4);
                $account->accrued_interest = round($account->accrued_interest + $interest, 4);
                $logs[] = ['origin_sister_concern_id' => $account->origin_sister_concern_id,
                    'customer_id' => $account->customer_id, 'date' => $date->toDateString(),
                    'principal_balance' => $balance, 'interest_rate' => $rate,
                    'earned_interest' => $interest, 'status' => 'Pending',
                    'created_at' => now(), 'updated_at' => now()];
            }
            $account->last_interest_date = $throughDate;
            $account->save();
            ShomriddhiDailyInterest::insert($logs);
        }, 3);
    }
}
