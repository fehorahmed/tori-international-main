<?php

namespace App\Console\Commands\ShomriddhiFdr;

use Illuminate\Console\Command;
use App\Models\ShomriddhiFdr\FdrAccount;
use App\Models\ShomriddhiFdr\FdrTransaction;
use App\Models\Account\AccountHead;
use App\Models\Account\AccountGroup;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Enumeration\ReservedAccountGroup;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProcessFdrInterestPayout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shomriddhi-fdr:process-interest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process automatic periodic interest payout for active FDR accounts and transfer to Savings account.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting FDR Interest Payout processing...');
        
        $today = Carbon::today()->format('Y-m-d');

        // Find active accounts where next_interest_date is today or passed, and frequency is not At Maturity
        $dueAccounts = FdrAccount::with(['scheme', 'customer'])
            ->where('status', 'Active')
            ->where('interest_payout_frequency', '!=', 'At Maturity')
            ->whereNotNull('next_interest_date')
            ->whereDate('next_interest_date', '<=', $today)
            ->get();

        if ($dueAccounts->isEmpty()) {
            $this->info('No due FDR accounts found for interest payout today.');
            return;
        }

        /** @var \App\Models\ShomriddhiFdr\FdrAccount $account */
        foreach ($dueAccounts as $account) {
            $this->processPayout($account, $today);
        }

        $this->info('FDR Interest Payout processing completed.');
    }

    private function processPayout(FdrAccount $account, $today)
    {
        DB::beginTransaction();
        try {
            // Find linked Savings Account (Shomriddhi Advance)
            $savingsAccount = ShomriddhiAccount::where('customer_id', $account->customer_id)
                ->where('status', 'Active')
                ->first();

            if (!$savingsAccount) {
                // If no active savings account, we skip or just log an error. 
                // For safety, we skip and log it. The admin has to resolve it.
                $this->error("Account FDR-{$account->fdr_no} skipped: No active Shomriddhi Advance account found for the customer.");
                DB::rollBack();
                return;
            }

            // Calculate Interest Amount based on Frequency
            $yearlyInterest = ($account->principal_amount * $account->scheme->interest_rate) / 100;
            $interestAmount = 0;
            
            $nextDate = Carbon::parse($account->next_interest_date);

            if ($account->interest_payout_frequency === 'Monthly') {
                $interestAmount = $yearlyInterest / 12;
                $nextDate->addMonth();
            } elseif ($account->interest_payout_frequency === 'Quarterly') {
                $interestAmount = $yearlyInterest / 4;
                $nextDate->addMonths(3);
            } elseif ($account->interest_payout_frequency === 'Half-Yearly') {
                $interestAmount = $yearlyInterest / 2;
                $nextDate->addMonths(6);
            } elseif ($account->interest_payout_frequency === 'Yearly') {
                $interestAmount = $yearlyInterest;
                $nextDate->addYear();
            }
            
            $interestAmount = round($interestAmount, 2);
            
            if ($interestAmount <= 0) {
                DB::rollBack();
                return;
            }

            // 1. Create FDR Transaction
            $fdrTransaction = FdrTransaction::create([
                'fdr_account_id' => $account->id,
                'type' => 'Interest Payout',
                'amount' => $interestAmount,
                'date' => $today,
                'note' => "Auto {$account->interest_payout_frequency} Interest Payout to Savings A/C: " . $savingsAccount->account_no,
                'created_by' => 1 // System user or admin ID
            ]);

            // 2. Add Money to Shomriddhi Savings Account
            $savingsAccount->increment('balance', $interestAmount);

            // Create Shomriddhi Transaction (Savings Deposit)
            $savingsTransaction = ShomriddhiTransaction::create([
                'shomriddhi_account_id' => $savingsAccount->id,
                'type' => 'Deposit',
                'amount' => $interestAmount,
                'date' => $today,
                'note' => "Auto Interest Received from FDR A/C: " . $account->fdr_no,
                'created_by' => 1
            ]);

            // 3. Update FDR Account next_interest_date
            $maturityDate = Carbon::parse($account->maturity_date);
            
            if ($nextDate->greaterThan($maturityDate)) {
                // The term is over! Check Auto-Renew
                if ($account->auto_renew) {
                    $newMaturityDate = $maturityDate->copy()->addMonths($account->scheme->duration);
                    $account->update([
                        'opening_date' => $maturityDate->format('Y-m-d'),
                        'maturity_date' => $newMaturityDate->format('Y-m-d'),
                        'next_interest_date' => $nextDate->format('Y-m-d')
                    ]);
                    $this->info("FDR-{$account->fdr_no} Auto-Renewed. New Maturity: {$newMaturityDate->format('Y-m-d')}");
                } else {
                    // Stop paying interest, mark as Matured
                    $account->update([
                        'next_interest_date' => null, 
                        'status' => 'Matured'
                    ]);
                    $this->info("FDR-{$account->fdr_no} Matured. Interest payout stopped.");
                }
            } else {
                $account->update(['next_interest_date' => $nextDate->format('Y-m-d')]);
            }

            // 4. Create Accounting Vouchers
            $this->postAccountingVoucher($account, $savingsAccount, $interestAmount, $today, $fdrTransaction->id);

            DB::commit();
            $this->info("Successfully processed payout for FDR-{$account->fdr_no}. Amount: {$interestAmount}");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to process payout for FDR-{$account->fdr_no}. Error: " . $e->getMessage());
        }
    }

    private function postAccountingVoucher($account, $savingsAccount, $amount, $date, $fdrTransactionId)
    {
        $scId = $account->origin_sister_concern_id;
        $noteText = "Auto Interest Transfer from FDR-{$account->fdr_no} to Savings-{$savingsAccount->account_no}";

        // Get Interest Expense Head
        $interestExpHead = AccountHead::where('name', 'FDR Interest Expense')->where('is_system', 1)->first();
        if (!$interestExpHead) {
            $expGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::FINANCIAL_EXPENSES ?? 0)->first();
            $interestExpHead = AccountHead::firstOrCreate(
                ['name' => 'FDR Interest Expense'],
                [
                    'sister_concern_id' => null,
                    'account_group_id' => $expGroup->id ?? null,
                    'code' => generateAccountCode($expGroup->top_reserved_id ?? ReservedAccountGroup::EXPENSES),
                    'nature' => 'Debit',
                    'head_type' => 'primary',
                    'is_system' => 1,
                    'is_editable' => 0,
                    'status' => 1
                ]
            );
        }

        // Get Savings Liability Head for the specific customer
        $savingsHead = AccountHead::where('supplier_id', $account->customer_id)
            ->where('head_type', 'savings')
            ->first();
            
        if (!$savingsHead) {
            // Fallback just in case (though ShomriddhiAdvanceService usually creates this)
            $savingsGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::CUSTOMER_SAVINGS)->first();
            $savingsHead = AccountHead::firstOrCreate(
                [
                    'supplier_id' => $account->customer_id,
                    'head_type' => 'savings',
                ],
                [
                    'sister_concern_id' => null,
                    'account_group_id' => $savingsGroup->id ?? null,
                    'code' => generateAccountCode($savingsGroup->top_reserved_id ?? ReservedAccountGroup::LIABILITIES),
                    'name' => 'Savings - ' . ($account->customer->name ?? 'Customer'),
                    'nature' => 'Credit',
                    'is_system' => 0,
                    'is_editable' => 0,
                    'status' => 1
                ]
            );
        }

        $vType = VoucherType::$JOURNAL_VOUCHER;
        $voucherNo = generateVoucherNo($vType);
        $newNumber = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

        $voucher = Voucher::create([
            'sister_concern_id'   => $scId,
            'voucher_type'        => $vType,
            'amount'              => $amount,
            'date'                => $date,
            'voucher_no'          => $voucherNo,
            'voucher_no_group_sl' => $newNumber,
            'notes'               => $noteText,
            'user_id'             => 1, // System
        ]);

        // DR Interest Expense
        Transaction::create([
            'sister_concern_id'   => $scId,
            'voucher_id'          => $voucher->id,
            'account_head_id'     => $interestExpHead->id,
            'voucher_type'        => $vType,
            'transaction_type'    => TransactionType::$DEBIT,
            'amount'              => $amount,
            'date'                => $date,
            'user_id'             => 1,
            'notes'               => $noteText,
            'voucher_no'          => $voucherNo,
            'voucher_no_group_sl' => $newNumber,
        ]);

        // CR Savings Liability
        Transaction::create([
            'sister_concern_id'   => $scId,
            'voucher_id'          => $voucher->id,
            'account_head_id'     => $savingsHead->id,
            'voucher_type'        => $vType,
            'transaction_type'    => TransactionType::$CREDIT,
            'amount'              => $amount,
            'date'                => $date,
            'user_id'             => 1,
            'notes'               => $noteText,
            'voucher_no'          => $voucherNo,
            'voucher_no_group_sl' => $newNumber,
        ]);
        
        // Link voucher to FdrTransaction
        FdrTransaction::where('id', $fdrTransactionId)->update(['voucher_id' => $voucher->id]);
    }
}
