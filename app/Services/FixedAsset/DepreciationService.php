<?php

namespace App\Services\FixedAsset;

use App\Enumeration\ReservedAccountHead;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\FixedAsset\FixedAsset;
use App\Models\FixedAsset\FixedAssetDepreciationLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DepreciationService
{
    /**
     * Preview depreciation for a specific month without saving (Format: YYYY-MM)
     */
    public function previewDepreciation(string $period, int $sisterConcernId): array
    {
        $parsedDate = Carbon::parse($period . '-01')->endOfMonth();
        $today = $parsedDate->format('Y-m-d');
        
        $assets = FixedAsset::with('category')
            ->where('sister_concern_id', $sisterConcernId)
            ->where('status', 'active')
            ->where('depreciation_method', '!=', 'none')
            ->where('date', '<=', $today)
            ->whereDoesntHave('depreciationLogs', function ($q) use ($period) {
                $q->where('period', $period);
            })
            ->whereRaw('book_value > salvage_value')
            ->get();

        if ($assets->isEmpty()) {
            return ['status' => false, 'message' => 'No active assets pending depreciation for ' . $period, 'data' => []];
        }

        $previewData = [];
        $totalDepreciation = 0;

        foreach ($assets as $asset) {
            $monthlyDepreciation = $this->calculateMonthlyDepreciation($asset);
            
            $maxDepreciation = $asset->book_value - $asset->salvage_value;
            if ($monthlyDepreciation > $maxDepreciation) {
                $monthlyDepreciation = $maxDepreciation;
            }

            if ($monthlyDepreciation <= 0) {
                continue;
            }

            $totalDepreciation += $monthlyDepreciation;

            $previewData[] = [
                'asset_code'               => $asset->asset_code,
                'asset_name'               => $asset->name,
                'category_name'            => $asset->category->name ?? '-',
                'accumulated_depreciation' => $asset->accumulated_depreciation,
                'book_value_before'        => $asset->book_value,
                'depreciation_amount'      => $monthlyDepreciation,
                'book_value_after'         => $asset->book_value - $monthlyDepreciation,
            ];
        }

        return [
            'status' => true, 
            'total_amount' => $totalDepreciation,
            'assets_count' => count($previewData),
            'data' => $previewData
        ];
    }

    /**
     * Run depreciation for a specific month (Format: YYYY-MM)
     */
    public function runDepreciation(string $period, int $sisterConcernId, int $userId): array
    {
        $parsedDate = Carbon::parse($period . '-01')->endOfMonth();
        $today = $parsedDate->format('Y-m-d');
        
        // Find active assets that need depreciation for this period
        $assets = FixedAsset::with('category')
            ->where('sister_concern_id', $sisterConcernId)
            ->where('status', 'active')
            ->where('depreciation_method', '!=', 'none')
            ->where('date', '<=', $today)
            // Ensure not already depreciated this period
            ->whereDoesntHave('depreciationLogs', function ($q) use ($period) {
                $q->where('period', $period);
            })
            // Ensure book value is greater than salvage value
            ->whereRaw('book_value > salvage_value')
            ->get();

        if ($assets->isEmpty()) {
            return ['status' => false, 'message' => 'No active assets pending depreciation for ' . $period];
        }

        $totalDepreciation = 0;
        $logsData = [];
        
        $depExpenseHeadId = reservedAccountHeadId(ReservedAccountHead::DEPRECIATION_EXPENSE);

        DB::beginTransaction();
        try {
            // 1. Calculate depreciation amounts
            foreach ($assets as $asset) {
                $monthlyDepreciation = $this->calculateMonthlyDepreciation($asset);
                
                // Ensure we don't depreciate below salvage value
                $maxDepreciation = $asset->book_value - $asset->salvage_value;
                if ($monthlyDepreciation > $maxDepreciation) {
                    $monthlyDepreciation = $maxDepreciation;
                }

                if ($monthlyDepreciation <= 0) {
                    continue; // Skip if no depreciation needed
                }

                $bookValueBefore = $asset->book_value;
                $bookValueAfter = $bookValueBefore - $monthlyDepreciation;

                $asset->accumulated_depreciation += $monthlyDepreciation;
                $asset->book_value = $bookValueAfter;
                $asset->save();

                $totalDepreciation += $monthlyDepreciation;

                $logsData[] = [
                    'asset'               => $asset,
                    'book_value_before'   => $bookValueBefore,
                    'depreciation_amount' => $monthlyDepreciation,
                    'book_value_after'    => $bookValueAfter,
                ];
            }

            if ($totalDepreciation <= 0) {
                DB::rollBack();
                return ['status' => false, 'message' => 'Calculated depreciation amount is zero for all pending assets.'];
            }

            // 2. Generate a single batch Journal Voucher for the month
            $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
            $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

            $voucher = Voucher::create([
                'sister_concern_id'               => $sisterConcernId,
                'voucher_type'                    => VoucherType::$JOURNAL_VOUCHER,
                'amount'                          => $totalDepreciation,
                'date'                            => $today,
                'voucher_no'                      => $voucherNo,
                'voucher_no_group_sl'             => $voucherNoGroupSl,
                'account_head_payee_depositor_id' => $depExpenseHeadId, // Overall payee is the expense head
                'notes'                           => 'Monthly Depreciation for ' . $period,
                'user_id'                         => $userId,
            ]);

            // Dr. Depreciation Expense (Total)
            Transaction::create([
                'sister_concern_id'               => $sisterConcernId,
                'voucher_id'                      => $voucher->id,
                'account_head_id'                 => $depExpenseHeadId,
                'voucher_type'                    => VoucherType::$JOURNAL_VOUCHER,
                'transaction_type'                => TransactionType::$DEBIT,
                'amount'                          => $totalDepreciation,
                'date'                            => $today,
                'voucher_no'                      => $voucherNo,
                'voucher_no_group_sl'             => $voucherNoGroupSl,
                'account_head_payee_depositor_id' => $depExpenseHeadId,
                'notes'                           => 'Total Depreciation Expense for ' . $period,
                'user_id'                         => $userId,
            ]);

            // Cr. Accumulated Depreciation (For each category/asset)
            // Group by Accumulated Depreciation Head to optimize transactions
            $accDepTotals = [];
            foreach ($logsData as $log) {
                $accDepHeadId = $log['asset']->category->accumulated_dep_head_id 
                    ?? reservedAccountHeadId(ReservedAccountHead::ACCUMULATED_DEPRECIATION);
                
                if (!isset($accDepTotals[$accDepHeadId])) {
                    $accDepTotals[$accDepHeadId] = 0;
                }
                $accDepTotals[$accDepHeadId] += $log['depreciation_amount'];

                // Create log entry
                FixedAssetDepreciationLog::create([
                    'fixed_asset_id'      => $log['asset']->id,
                    'sister_concern_id'   => $sisterConcernId,
                    'period'              => $period,
                    'book_value_before'   => $log['book_value_before'],
                    'depreciation_amount' => $log['depreciation_amount'],
                    'book_value_after'    => $log['book_value_after'],
                    'voucher_id'          => $voucher->id,
                    'user_id'             => $userId,
                ]);
            }

            foreach ($accDepTotals as $accHeadId => $amount) {
                Transaction::create([
                    'sister_concern_id'               => $sisterConcernId,
                    'voucher_id'                      => $voucher->id,
                    'account_head_id'                 => $accHeadId,
                    'voucher_type'                    => VoucherType::$JOURNAL_VOUCHER,
                    'transaction_type'                => TransactionType::$CREDIT,
                    'amount'                          => $amount,
                    'date'                            => $today,
                    'voucher_no'                      => $voucherNo,
                    'voucher_no_group_sl'             => $voucherNoGroupSl,
                    'account_head_payee_depositor_id' => $depExpenseHeadId,
                    'notes'                           => 'Accumulated Depreciation for ' . $period,
                    'user_id'                         => $userId,
                ]);
            }

            DB::commit();
            return [
                'status' => true, 
                'message' => 'Depreciation run successful. JV: ' . $voucherNo, 
                'processed' => count($logsData),
                'amount' => $totalDepreciation
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function calculateMonthlyDepreciation(FixedAsset $asset): float
    {
        if ($asset->depreciation_method === 'SLM') {
            // SLM: (Cost - Salvage) / Useful Life
            $depreciableAmount = $asset->total - $asset->salvage_value;
            $yearlyDepreciation = $depreciableAmount / max($asset->useful_life_years, 1);
            return $yearlyDepreciation / 12;

        } elseif ($asset->depreciation_method === 'WDV') {
            // WDV: Book Value * Rate%
            $yearlyDepreciation = $asset->book_value * ($asset->depreciation_rate / 100);
            return $yearlyDepreciation / 12;
        }

        return 0;
    }
}
