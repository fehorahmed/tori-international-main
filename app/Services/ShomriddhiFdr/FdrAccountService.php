<?php

namespace App\Services\ShomriddhiFdr;

use App\Models\ShomriddhiFdr\FdrAccount;
use App\Models\ShomriddhiFdr\FdrTransaction;
use App\Models\Account\AccountHead;
use App\Models\Account\AccountGroup;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Enumeration\PaymentPurpose;
use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\ReservedAccountHead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FdrAccountService
{
    /**
     * Approve FDR Account, generate initial deposit transaction and GL voucher.
     */
    public function approve(FdrAccount $account, int $paymentAccountHeadId): array
    {
        $paymentHead = AccountHead::find($paymentAccountHeadId);
        if (!$paymentHead) {
            return ['success' => false, 'message' => 'Payment account head not found.'];
        }

        DB::beginTransaction();
        try {
            $account->update([
                'status' => 'Active',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Create transaction record for the principal deposit
            $fdrTransaction = FdrTransaction::create([
                'fdr_account_id' => $account->id,
                'type' => 'Deposit',
                'amount' => $account->principal_amount,
                'date' => Carbon::parse(auth()->user()->business_date)->format('Y-m-d'),
                'note' => 'Initial FDR Deposit',
                'created_by' => auth()->id()
            ]);

            // General Ledger / Voucher Logic
            $this->postDepositVoucher($account, $paymentHead, $fdrTransaction);

            DB::commit();
            return ['success' => true, 'message' => 'FDR Account approved and accounting voucher generated successfully.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Failed to approve: ' . $e->getMessage()];
        }
    }

    private function postDepositVoucher(FdrAccount $account, AccountHead $paymentHead, FdrTransaction $fdrTxn)
    {
        $scId = auth()->user()->activeConcernId();
        $date = $fdrTxn->date;
        $amount = $fdrTxn->amount;
        $noteText = 'FDR Deposit Collection for A/C: ' . $account->fdr_no;

        // Ensure FDR Liabilities Group Exists
        $fdrGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::CUSTOMER_FDR)->first();
        if (!$fdrGroup) {
            // Fallback if reserved enum doesn't exist specifically for FDR
            // In many systems, it goes under Current Liabilities -> Customer Deposits
            $liabilities = AccountGroup::where('reserved_id', ReservedAccountGroup::CURRENT_LIABILITIES)->first();
            $fdrGroup = AccountGroup::firstOrCreate(
                ['name' => 'FDR Deposits'],
                [
                    'account_group_id' => $liabilities->id ?? null,
                    'reserved_id' => ReservedAccountGroup::CUSTOMER_FDR ?? 999, // Fallback ID
                    'is_editable' => 0,
                    'is_leaf' => 1,
                ]
            );
        }

        // Get or Create Account Head for this specific Customer's FDR
        $fdrHead = AccountHead::firstOrCreate(
            [
                'supplier_id' => $account->customer_id,
                'account_group_id' => $fdrGroup->id,
                'head_type' => 'fdr',
            ],
            [
                'sister_concern_id' => null,
                'code' => generateAccountCode(ReservedAccountGroup::LIABILITIES ?? 2),
                'name' => 'FDR - ' . ($account->customer->name ?? 'Customer') . ' (' . ($account->customer->mobile_no ?? '') . ')',
                'is_editable' => 0,
                'status' => 1,
            ]
        );

        $vType = VoucherType::$COLLECTION_VOUCHER;
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
            'user_id'             => auth()->id(),
            'payment_purpose'     => PaymentPurpose::$FDR_DEPOSIT ?? 50, // Assuming 50 as fallback
        ]);

        // DR Cash/Bank (Asset Increases)
        Transaction::create([
            'sister_concern_id'   => $scId,
            'voucher_id'          => $voucher->id,
            'account_head_id'     => $paymentHead->id,
            'voucher_type'        => $vType,
            'transaction_type'    => TransactionType::$DEBIT,
            'amount'              => $amount,
            'date'                => $date,
            'user_id'             => auth()->id(),
            'notes'               => $noteText,
            'voucher_no'          => $voucherNo,
            'voucher_no_group_sl' => $newNumber,
            'payment_purpose'     => PaymentPurpose::$FDR_DEPOSIT ?? 50,
        ]);

        // CR Customer FDR Deposits (Liability Increases)
        Transaction::create([
            'sister_concern_id'   => $scId,
            'voucher_id'          => $voucher->id,
            'account_head_id'     => $fdrHead->id,
            'voucher_type'        => $vType,
            'transaction_type'    => TransactionType::$CREDIT,
            'amount'              => $amount,
            'date'                => $date,
            'user_id'             => auth()->id(),
            'notes'               => $noteText,
            'voucher_no'          => $voucherNo,
            'voucher_no_group_sl' => $newNumber,
            'payment_purpose'     => PaymentPurpose::$FDR_DEPOSIT ?? 50,
        ]);
        
        // Save voucher reference to the fdr transaction
        $fdrTxn->update(['voucher_id' => $voucher->id]);
    }

    public function encash(FdrAccount $account, array $data): array
    {
        $paymentHead = AccountHead::find($data['payment_mode']);
        if (!$paymentHead) {
            return ['success' => false, 'message' => 'Payment account head not found.'];
        }

        DB::beginTransaction();
        try {
            $account->update([
                'status' => 'Closed',
                'closed_at' => now(),
                'notes' => $account->notes . ' | Encashed on ' . now()->format('Y-m-d'),
            ]);

            $totalPaid = $account->principal_amount + $data['interest_amount'] - $data['penalty_amount'];

            // Create Encashment Transaction Record
            $fdrTransaction = FdrTransaction::create([
                'fdr_account_id' => $account->id,
                'type' => 'Encashment',
                'amount' => $totalPaid,
                'date' => Carbon::parse(auth()->user()->business_date)->format('Y-m-d'),
                'note' => 'FDR Encashment (Principal: ' . $account->principal_amount . ' + Interest: ' . $data['interest_amount'] . ' - Penalty: ' . $data['penalty_amount'] . ')',
                'created_by' => auth()->id()
            ]);

            // Post Withdrawal Voucher
            $this->postEncashmentVoucher($account, $paymentHead, $fdrTransaction, $data);

            DB::commit();
            return ['success' => true, 'message' => 'FDR Account encashed successfully.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Failed to encash: ' . $e->getMessage()];
        }
    }

    private function postEncashmentVoucher(FdrAccount $account, AccountHead $paymentHead, FdrTransaction $fdrTxn, array $data)
    {
        $scId = auth()->user()->activeConcernId();
        $date = $fdrTxn->date;
        $amount = $fdrTxn->amount; // Total paid to customer
        $noteText = $fdrTxn->note;

        // Find existing FDR Customer Head
        $fdrGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::CUSTOMER_FDR ?? 999)->first();
        if (!$fdrGroup) {
            $fdrGroup = AccountGroup::where('name', 'FDR Deposits')->first();
        }

        $fdrHead = AccountHead::where('supplier_id', $account->customer_id)
            ->where('account_group_id', $fdrGroup->id ?? 0)
            ->where('head_type', 'fdr')
            ->first();

        // Fallbacks for missing heads
        if (!$fdrHead) {
            throw new \Exception("FDR Account Head not found for customer.");
        }

        $vType = VoucherType::$PAYMENT_VOUCHER;
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
            'user_id'             => auth()->id(),
            'payment_purpose'     => PaymentPurpose::$FDR_ENCASHMENT ?? 51,
        ]);

        // DR Customer FDR Deposits (Liability Decreases - Principal goes to 0)
        Transaction::create([
            'sister_concern_id'   => $scId,
            'voucher_id'          => $voucher->id,
            'account_head_id'     => $fdrHead->id,
            'voucher_type'        => $vType,
            'transaction_type'    => TransactionType::$DEBIT,
            'amount'              => $account->principal_amount,
            'date'                => $date,
            'user_id'             => auth()->id(),
            'notes'               => 'Principal Return',
            'voucher_no'          => $voucherNo,
            'voucher_no_group_sl' => $newNumber,
            'payment_purpose'     => PaymentPurpose::$FDR_ENCASHMENT ?? 51,
        ]);

        // Need an expense head for Interest Paid
        if ($data['interest_amount'] > 0) {
            $interestExpHead = AccountHead::where('reserved_id', ReservedAccountHead::FDR_INTEREST_EXPENSE)
                ->orWhere('name', 'FDR Interest Expense')
                ->first();
                
            if (!$interestExpHead) {
                // Try to find the general interest expense
                $expGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::FINANCIAL_EXPENSES)->first();
                $interestExpHead = AccountHead::firstOrCreate(
                    ['reserved_id' => ReservedAccountHead::FDR_INTEREST_EXPENSE],
                    [
                        'name' => 'FDR Interest Expense',
                        'account_group_id' => $expGroup->id ?? null,
                        'code' => generateAccountCode(ReservedAccountGroup::EXPENSES ?? 4),
                        'status' => 1
                    ]
                );
            }

            // DR Interest Expense
            Transaction::create([
                'sister_concern_id'   => $scId,
                'voucher_id'          => $voucher->id,
                'account_head_id'     => $interestExpHead->id,
                'voucher_type'        => $vType,
                'transaction_type'    => TransactionType::$DEBIT,
                'amount'              => $data['interest_amount'],
                'date'                => $date,
                'user_id'             => auth()->id(),
                'notes'               => 'Interest Payout',
                'voucher_no'          => $voucherNo,
                'voucher_no_group_sl' => $newNumber,
                'payment_purpose'     => PaymentPurpose::$FDR_ENCASHMENT ?? 51,
            ]);
        }

        // Need an income head for Penalty
        if ($data['penalty_amount'] > 0) {
            $penaltyIncHead = AccountHead::where('reserved_id', ReservedAccountHead::FDR_PENALTY_INCOME)
                ->orWhere('name', 'FDR Penalty Income')
                ->first();
                
            if (!$penaltyIncHead) {
                $incGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::OTHER_INCOME)->first();
                $penaltyIncHead = AccountHead::firstOrCreate(
                    ['reserved_id' => ReservedAccountHead::FDR_PENALTY_INCOME],
                    [
                        'name' => 'FDR Penalty Income',
                        'account_group_id' => $incGroup->id ?? null,
                        'code' => generateAccountCode(ReservedAccountGroup::REVENUE ?? 4),
                        'status' => 1
                    ]
                );
            }

            // CR Penalty Income
            Transaction::create([
                'sister_concern_id'   => $scId,
                'voucher_id'          => $voucher->id,
                'account_head_id'     => $penaltyIncHead->id,
                'voucher_type'        => $vType,
                'transaction_type'    => TransactionType::$CREDIT,
                'amount'              => $data['penalty_amount'],
                'date'                => $date,
                'user_id'             => auth()->id(),
                'notes'               => 'Penalty Collection',
                'voucher_no'          => $voucherNo,
                'voucher_no_group_sl' => $newNumber,
                'payment_purpose'     => PaymentPurpose::$FDR_ENCASHMENT ?? 51,
            ]);
        }

        // CR Cash/Bank (Asset Decreases - Money paid out)
        Transaction::create([
            'sister_concern_id'   => $scId,
            'voucher_id'          => $voucher->id,
            'account_head_id'     => $paymentHead->id,
            'voucher_type'        => $vType,
            'transaction_type'    => TransactionType::$CREDIT,
            'amount'              => $amount,
            'date'                => $date,
            'user_id'             => auth()->id(),
            'notes'               => $noteText,
            'voucher_no'          => $voucherNo,
            'voucher_no_group_sl' => $newNumber,
            'payment_purpose'     => PaymentPurpose::$FDR_ENCASHMENT ?? 51,
        ]);
        
        $fdrTxn->update(['voucher_id' => $voucher->id]);
    }
}
