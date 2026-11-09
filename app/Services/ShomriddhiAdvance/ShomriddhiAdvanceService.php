<?php

namespace App\Services\ShomriddhiAdvance;

use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Enumeration\PaymentPurpose;
use App\Repositories\ShomriddhiAdvance\Contracts\ShomriddhiAdvanceRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ShomriddhiAdvanceService
{
    public function __construct(
        private ShomriddhiAdvanceRepositoryInterface $repository
    ) {}

    /**
     * Approve a pending transaction.
     * Delegates DB update to Repository; handles accounting logic here.
     */
    public function approve(ShomriddhiTransaction $advance, int $paymentAccountHeadId): array
    {
        $savingsHead  = AccountHead::where('supplier_id', $advance->customer_id)
            ->where('head_type', 'savings')
            ->first();

        if (!$savingsHead) {
            return ['success' => false, 'message' => 'Customer savings account head not found.'];
        }

        $paymentHead = AccountHead::find($paymentAccountHeadId);
        if (!$paymentHead) {
            return ['success' => false, 'message' => 'Payment account head not found.'];
        }

        DB::beginTransaction();
        try {
            // Find the ShomriddhiAccount
            $account = ShomriddhiAccount::where('customer_id', $advance->customer_id)->first();
            if (!$account) {
                return ['success' => false, 'message' => 'Shomriddhi Account profile not found for this customer.'];
            }

            $date = Carbon::parse(auth()->user()->business_date)->format('Y-m-d');

            // Apply Withdrawal / Deposit Rules on the Account Balance
            if ($advance->type === 'withdraw') {
                // If withdrawal amount is equal or greater than principal, it's a FULL withdrawal
                if ($advance->amount >= $account->principal_balance) {
                    // Force Capitalize Accrued Interest
                    $capitalizationService = app(CapitalizationService::class);
                    $capitalizationService->capitalize($account, $date);
                    
                    $account->refresh(); // Reload to get updated principal_balance
                    
                    // Modify advance amount to the new FULL principal balance (so they get interest too)
                    $advance->amount = $account->principal_balance;
                    $advance->save();

                    // Deduct everything
                    $account->principal_balance = 0;
                    $account->save();
                } else {
                    // Partial Withdrawal - Do not touch accrued interest
                    $account->principal_balance -= $advance->amount;
                    $account->save();
                }
            } elseif ($advance->type === 'deposit') {
                $account->principal_balance += $advance->amount;
                $account->save();
            }

            // Proceed with GL Voucher Creation
            [$vType, $label] = $this->resolveVoucherType($advance->type);

            $scId      = auth()->user()->activeConcernId();
            $voucherNo = generateVoucherNo($vType);
            $newNumber = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));
            $noteText  = 'Shomriddhi ' . $label . ($advance->note ? ' — ' . $advance->note : '');

            $voucher = Voucher::create([
                'sister_concern_id'   => $scId,
                'voucher_type'        => $vType,
                'amount'              => $advance->amount,
                'date'                => $date,
                'voucher_no'          => $voucherNo,
                'voucher_no_group_sl' => $newNumber,
                'notes'               => $noteText,
                'user_id'             => auth()->id(),
                'payment_purpose'     => $advance->type === 'deposit' ? PaymentPurpose::$SHOMRIDDHI_ADVANCE_DEPOSIT : PaymentPurpose::$SHOMRIDDHI_ADVANCE_WITHDRAW,
            ]);

            if ($advance->type === 'deposit') {
                // DR Cash/Bank (টাকা ঢুকছে)
                Transaction::create([
                    'sister_concern_id'   => $scId,
                    'voucher_id'          => $voucher->id,
                    'account_head_id'     => $paymentHead->id,
                    'voucher_type'        => $vType,
                    'transaction_type'    => TransactionType::$DEBIT,
                    'amount'              => $advance->amount,
                    'date'                => $date,
                    'user_id'             => auth()->id(),
                    'notes'               => $noteText,
                    'voucher_no'          => $voucherNo,
                    'voucher_no_group_sl' => $newNumber,
                    'payment_purpose'     => PaymentPurpose::$SHOMRIDDHI_ADVANCE_DEPOSIT,
                ]);
                // CR Customer Savings (দায় বাড়ছে)
                Transaction::create([
                    'sister_concern_id'   => $scId,
                    'voucher_id'          => $voucher->id,
                    'account_head_id'     => $savingsHead->id,
                    'voucher_type'        => $vType,
                    'transaction_type'    => TransactionType::$CREDIT,
                    'amount'              => $advance->amount,
                    'date'                => $date,
                    'user_id'             => auth()->id(),
                    'notes'               => $noteText,
                    'voucher_no'          => $voucherNo,
                    'voucher_no_group_sl' => $newNumber,
                    'payment_purpose'     => PaymentPurpose::$SHOMRIDDHI_ADVANCE_DEPOSIT,
                ]);
            } else {
                // DR Customer Savings (দায় কমছে)
                Transaction::create([
                    'sister_concern_id'   => $scId,
                    'voucher_id'          => $voucher->id,
                    'account_head_id'     => $savingsHead->id,
                    'voucher_type'        => $vType,
                    'transaction_type'    => TransactionType::$DEBIT,
                    'amount'              => $advance->amount,
                    'date'                => $date,
                    'user_id'             => auth()->id(),
                    'notes'               => $noteText,
                    'voucher_no'          => $voucherNo,
                    'voucher_no_group_sl' => $newNumber,
                    'payment_purpose'     => PaymentPurpose::$SHOMRIDDHI_ADVANCE_WITHDRAW,
                ]);
                // CR Cash/Bank (টাকা বেরিয়ে যাচ্ছে)
                Transaction::create([
                    'sister_concern_id'   => $scId,
                    'voucher_id'          => $voucher->id,
                    'account_head_id'     => $paymentHead->id,
                    'voucher_type'        => $vType,
                    'transaction_type'    => TransactionType::$CREDIT,
                    'amount'              => $advance->amount,
                    'date'                => $date,
                    'user_id'             => auth()->id(),
                    'notes'               => $noteText,
                    'voucher_no'          => $voucherNo,
                    'voucher_no_group_sl' => $newNumber,
                    'payment_purpose'     => PaymentPurpose::$SHOMRIDDHI_ADVANCE_WITHDRAW,
                ]);
            }

            $this->repository->updateStatus($advance, 'Approved', [
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'voucher_id'  => $voucher->id,
            ]);

            DB::commit();
            return ['success' => true, 'message' => 'Transaction approved. Account balances and GL updated.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Reject a pending transaction.
     */
    public function reject(ShomriddhiTransaction $advance): array
    {
        $this->repository->updateStatus($advance, 'Rejected');
        return ['success' => true, 'message' => 'Transaction rejected.'];
    }

    /**
     * Delete an approved transaction and reverse its effects.
     */
    public function deleteTransaction(ShomriddhiTransaction $advance): array
    {
        DB::beginTransaction();
        try {
            $account = ShomriddhiAccount::where('customer_id', $advance->customer_id)->first();
            if ($account) {
                // Reverse the balance
                if ($advance->type === 'deposit') {
                    $account->principal_balance -= $advance->amount;
                } elseif ($advance->type === 'withdraw') {
                    // For withdraw, we just add the amount back. 
                    // Note: Reversing interest capitalization from a full withdrawal is complex and requires custom logic if needed.
                    $account->principal_balance += $advance->amount;
                }
                $account->save();
            }

            // Delete the voucher and associated transactions
            if ($advance->voucher_id) {
                $voucher = Voucher::find($advance->voucher_id);
                if ($voucher) {
                    if ($voucher->payment_purpose == PaymentPurpose::$SHOMRIDDHI_CARD_FEE) {
                        DB::rollBack();
                        return ['success' => false, 'message' => 'Cannot delete transaction because it is a Card Commission fee.'];
                    }
                    Transaction::where('voucher_id', $voucher->id)->delete();
                    $voucher->delete();
                }
            }

            // Log who deleted it and soft delete the record
            $advance->deleted_by = auth()->id();
            $advance->save();
            $advance->delete();

            DB::commit();
            return ['success' => true, 'message' => 'Transaction successfully deleted and balances reversed.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Resolve voucher type and label by transaction type string.
     */
    private function resolveVoucherType(string $type): array
    {
        if ($type === 'deposit') {
            return [VoucherType::$COLLECTION_VOUCHER, 'Deposit'];
        }
        return [VoucherType::$PAYMENT_VOUCHER, 'Withdrawal'];
    }
}
