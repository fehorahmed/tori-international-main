<?php

namespace App\Services\ShomriddhiAdvance;

use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\ShomriddhiAdvance\ShomriddhiDailyInterest;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\ReservedAccountHead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CapitalizationService
{
    /**
     * Capitalize the accrued interest of a specific account.
     * Moves accrued_interest to principal_balance and posts GL entries.
     */
    public function capitalize(ShomriddhiAccount $account, string $date = null): void
    {
        if ($account->accrued_interest <= 0) {
            return;
        }

        $date = $date ?? Carbon::now()->format('Y-m-d');
        // Safely extract up to 2 decimal places (e.g. 1.0981 -> 1.09) to avoid negative fractions
        $amountToCapitalize = floor($account->accrued_interest * 100) / 100;

        if ($amountToCapitalize <= 0) {
            return; // Not enough to capitalize (e.g. 0.001)
        }

        DB::beginTransaction();
        try {
            // 1. Reset accrued_interest and add to principal
            $account->principal_balance += $amountToCapitalize;
            // Any fraction below 2 decimals stays in accrued_interest for next time
            $account->accrued_interest = $account->accrued_interest - $amountToCapitalize;
            $account->save();

            ShomriddhiDailyInterest::where('customer_id', $account->customer_id)
                ->where('status', 'Pending')
                ->update(['status' => 'Capitalized']);

            // 2. Create ShomriddhiTransaction Log
            $advance = ShomriddhiTransaction::create([
                'customer_id' => $account->customer_id,
                'type' => 'interest_capitalization',
                'amount' => $amountToCapitalize,
                'date' => $date,
                'note' => 'System Auto Capitalization',
                'status' => 'Approved',
                'created_by' => 1, // System User
                'approved_by' => 1,
                'approved_at' => Carbon::now(),
            ]);

            // 3. Double-Entry Accounting
            $sisterConcernId = $account->origin_sister_concern_id;

            // Find Customer Savings Head
            $customerSavingsHead = AccountHead::where('supplier_id', $account->customer_id)
                ->where('head_type', 'savings')
                ->first();

            // Find or Create Interest Expense Head
            $expenseHead = AccountHead::where('reserved_id', ReservedAccountHead::SHOMRIDDHI_INTEREST_EXPENSE)->first();

            if (!$expenseHead) {
                $expenseGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::FINANCIAL_EXPENSES)->first();
                if ($expenseGroup) {
                    $expenseHead = AccountHead::create([
                        'sister_concern_id' => null, 
                        'code' => generateAccountCode(ReservedAccountGroup::FINANCIAL_EXPENSES),
                        'reserved_id' => ReservedAccountHead::SHOMRIDDHI_INTEREST_EXPENSE,
                        'account_group_id' => $expenseGroup->id,
                        'name' => 'Shomriddhi Interest Expense',
                        'nature' => 'Debit',
                        'head_type' => 'primary',
                        'is_system' => 1,
                        'is_editable' => 0,
                        'is_control_account' => 0,
                    ]);
                }
            }

            if ($customerSavingsHead && $expenseHead) {
                $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER, 'vouchers', $sisterConcernId);
                $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

                $voucher = Voucher::create([
                    'sister_concern_id'   => $sisterConcernId,
                    'voucher_type'        => VoucherType::$JOURNAL_VOUCHER,
                    'amount'              => $amountToCapitalize,
                    'date'                => $date,
                    'voucher_no'          => $voucherNo,
                    'voucher_no_group_sl' => $voucherNoGroupSl,
                    'notes'               => 'Auto Interest Capitalization',
                    'user_id'             => 1, // System User
                ]);

                // Debit Expense
                Transaction::create([
                    'sister_concern_id'   => $sisterConcernId,
                    'voucher_id'          => $voucher->id,
                    'account_head_id'     => $expenseHead->id,
                    'voucher_type'        => VoucherType::$JOURNAL_VOUCHER,
                    'transaction_type'    => TransactionType::$DEBIT,
                    'amount'              => $amountToCapitalize,
                    'date'                => $date,
                    'user_id'             => 1,
                    'notes'               => 'Shomriddhi Monthly Interest Capitalization',
                    'voucher_no'          => $voucherNo,
                    'voucher_no_group_sl' => $voucherNoGroupSl,
                ]);

                // Credit Liability (Customer Savings)
                Transaction::create([
                    'sister_concern_id'   => $sisterConcernId,
                    'voucher_id'          => $voucher->id,
                    'account_head_id'     => $customerSavingsHead->id,
                    'voucher_type'        => VoucherType::$JOURNAL_VOUCHER,
                    'transaction_type'    => TransactionType::$CREDIT,
                    'amount'              => $amountToCapitalize,
                    'date'                => $date,
                    'user_id'             => 1,
                    'notes'               => 'Shomriddhi Monthly Interest Capitalization',
                    'voucher_no'          => $voucherNo,
                    'voucher_no_group_sl' => $voucherNoGroupSl,
                ]);

                // Link the generated voucher to the Shomriddhi Advance log
                $advance->voucher_id = $voucher->id;
                $advance->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shomriddhi Capitalization Error: ' . $e->getMessage());
        }
    }
}
