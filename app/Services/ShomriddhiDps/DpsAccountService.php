<?php

namespace App\Services\ShomriddhiDps;

use App\Models\ShomriddhiDps\DpsAccount;
use App\Models\ShomriddhiDps\DpsTransaction;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Enumeration\PaymentPurpose;
use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\ReservedAccountHead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DpsAccountService
{
    /**
     * Approve a DPS account.
     * Creates the customer DPS liability head and posts the initial deposit GL entry.
     */
    public function approve(DpsAccount $account, int $paymentAccountHeadId): array
    {
        $paymentHead = AccountHead::find($paymentAccountHeadId);
        if (!$paymentHead) {
            return ['success' => false, 'message' => 'Payment account head not found.'];
        }

        DB::beginTransaction();
        try {
            $account->update([
                'status'      => 'Active',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Log the opening deposit transaction
            $dpsTxn = DpsTransaction::create([
                'dps_account_id'         => $account->id,
                'installment_no'         => 1,
                'type'                   => 'Installment', // First payment on approval
                'amount'                 => $account->installment_amount,
                'penalty_amount'         => 0,
                'payment_account_head_id' => $paymentAccountHeadId,
                'date'                   => Carbon::parse(auth()->user()->business_date)->format('Y-m-d'),
                'note'                   => 'DPS Account Opening - First Installment',
                'created_by'             => auth()->id(),
            ]);

            // Post the GL voucher
            $this->postInstallmentVoucher($account, $paymentHead, $dpsTxn, 0);

            // Update account counters
            $account->increment('paid_installments');
            $account->increment('total_deposited', $account->installment_amount);

            DB::commit();
            return ['success' => true, 'message' => 'DPS Account approved. First installment recorded and GL voucher generated.'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DPS Approve Error [Account #' . $account->id . ']: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to approve: ' . $e->getMessage()];
        }
    }

    /**
     * Pay a monthly installment.
     * Validates payment is not a duplicate, logs the transaction, and posts the GL voucher.
     */
    public function payInstallment(DpsAccount $account, int $paymentAccountHeadId, string $date, int $numberOfInstallments, float $penaltyCollected, ?string $note): array
    {
        if ($account->status !== 'Active') {
            return ['success' => false, 'message' => 'Only Active accounts can receive installment payments.'];
        }

        if ($account->paid_installments >= $account->total_installments) {
            return ['success' => false, 'message' => 'All installments have already been paid. Please proceed with encashment.'];
        }

        $paymentHead = AccountHead::find($paymentAccountHeadId);
        if (!$paymentHead) {
            return ['success' => false, 'message' => 'Payment account head not found.'];
        }

        DB::beginTransaction();
        try {
            $totalAmount = $account->installment_amount * $numberOfInstallments;
            
            $dpsTxn = DpsTransaction::create([
                'dps_account_id'          => $account->id,
                'installment_no'          => $account->paid_installments + 1, // Represents starting installment
                'type'                    => 'Installment',
                'amount'                  => $totalAmount,
                'penalty_amount'          => $penaltyCollected,
                'payment_account_head_id' => $paymentAccountHeadId,
                'date'                    => $date,
                'note'                    => $note ?? "Collected {$numberOfInstallments} installment(s)",
                'status'                  => 'Pending',
                'created_by'              => auth()->id(),
            ]);

            // NOTE: We DO NOT post the voucher or update the account counters yet!
            // That will happen when the manager approves the transaction.

            DB::commit();
            return [
                'success' => true, 
                'message' => "Collection of {$numberOfInstallments} installment(s) saved as Pending.",
                'transaction_id' => $dpsTxn->id
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DPS Installment Error [Account #' . $account->id . ']: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to process installment: ' . $e->getMessage()];
        }
    }

    /**
     * Approve a pending DPS transaction.
     * Posts the GL voucher and updates the account counters.
     */
    public function approveTransaction(DpsTransaction $transaction): array
    {
        DB::beginTransaction();
        try {
            $account = $transaction->dpsAccount;
            $paymentHead = AccountHead::find($transaction->payment_account_head_id);

            // Post the voucher for the collected amount
            $this->postInstallmentVoucher($account, $paymentHead, $transaction, $transaction->penalty_amount);

            // Calculate number of installments covered by this transaction
            $numberOfInstallments = $transaction->amount / $account->installment_amount;

            // Update account counters
            $account->increment('paid_installments', $numberOfInstallments);
            $account->increment('total_deposited', $transaction->amount);
            
            // Advance next_installment_date by number of installments paid
            $account->update([
                'next_installment_date' => Carbon::parse($account->next_installment_date)->addMonths($numberOfInstallments)->format('Y-m-d'),
            ]);

            // Auto-mature if all installments paid
            if ($account->paid_installments >= $account->total_installments) {
                $account->update(['status' => 'Matured', 'next_installment_date' => null]);
            }

            // Mark as approved
            $transaction->update([
                'status' => 'Approved',
                'updated_at' => now()
            ]);

            DB::commit();
            return ['success' => true, 'message' => 'Transaction approved successfully and accounts updated.'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DPS Transaction Approval Error [Txn #' . $transaction->id . ']: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to approve transaction: ' . $e->getMessage()];
        }
    }

    /**
     * Encash a DPS account (Maturity or Premature).
     * Calculates payout, posts GL voucher, and closes the account.
     */
    public function encash(DpsAccount $account, array $data): array
    {
        $paymentHead = AccountHead::find($data['payment_mode']);
        if (!$paymentHead) {
            return ['success' => false, 'message' => 'Payment account head not found.'];
        }

        DB::beginTransaction();
        try {
            $isPremature = $account->status === 'Active'; // Matured status means full payout
            $type        = $isPremature ? 'Premature_Encashment' : 'Encashment';
            $date        = Carbon::parse(auth()->user()->business_date)->format('Y-m-d');

            $principal      = $account->total_deposited;
            $interestAmount = (float) ($data['interest_amount'] ?? 0);
            $penaltyAmount  = (float) ($data['penalty_amount'] ?? 0);
            $totalPayout    = $principal + $interestAmount - $penaltyAmount;

            // Close the account
            $account->update([
                'status'                => 'Closed',
                'next_installment_date' => null,
                'notes'                 => ($account->notes ?? '') . " | Encashed on {$date}",
            ]);

            // Log encashment transaction
            $dpsTxn = DpsTransaction::create([
                'dps_account_id'          => $account->id,
                'installment_no'          => null,
                'type'                    => $type,
                'amount'                  => $totalPayout,
                'penalty_amount'          => $penaltyAmount,
                'payment_account_head_id' => $data['payment_mode'],
                'date'                    => $date,
                'note'                    => "DPS Encashment: Principal={$principal}, Interest={$interestAmount}, Penalty={$penaltyAmount}",
                'created_by'              => auth()->id(),
            ]);

            // Post the encashment voucher
            $this->postEncashmentVoucher($account, $paymentHead, $dpsTxn, $interestAmount, $penaltyAmount);

            DB::commit();
            return ['success' => true, 'message' => 'DPS Account encashed successfully. Total paid: ৳ ' . number_format($totalPayout, 2)];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DPS Encashment Error [Account #' . $account->id . ']: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to encash: ' . $e->getMessage()];
        }
    }

    /**
     * Calculate the encashment data for the modal (read-only preview).
     */
    public function calculateEncashmentData(DpsAccount $account): array
    {
        $today          = Carbon::today();
        $openingDate    = Carbon::parse($account->opening_date);
        $maturityDate   = Carbon::parse($account->maturity_date);
        $monthsElapsed  = $openingDate->diffInMonths($today);
        $isMature       = $today->greaterThanOrEqualTo($maturityDate);

        $principal       = $account->total_deposited;
        $annualRate      = $account->interest_rate / 100;
        $interestAmount  = 0;
        $penaltyAmount   = 0;
        $type            = $isMature ? 'Maturity' : 'Premature';

        if ($isMature) {
            // Full interest: (Total Principal × Annual Rate × Duration in Years)
            $years          = $account->total_installments / 12;
            $interestAmount = round($principal * $annualRate * $years, 2);
        } else {
            // Pre-mature: No interest if < 6 months, else reduced interest
            if ($monthsElapsed >= 6) {
                $years          = $monthsElapsed / 12;
                $interestAmount = round($principal * $annualRate * $years, 2);
                // Apply premature penalty on interest
                $scheme = $account->scheme;
                if ($scheme && $scheme->premature_penalty_amount > 0) {
                    if ($scheme->premature_penalty_type === 'Percentage') {
                        $penaltyAmount = round($interestAmount * ($scheme->premature_penalty_amount / 100), 2);
                    } else {
                        $penaltyAmount = $scheme->premature_penalty_amount;
                    }
                }
                $interestAmount = max(0, $interestAmount - $penaltyAmount);
            }
            // else: 0 interest, 0 penalty — only principal returned
        }

        $totalPayout = $principal + $interestAmount;

        return [
            'type'            => $type,
            'principal'       => $principal,
            'interest_amount' => $interestAmount,
            'penalty_amount'  => $penaltyAmount,
            'total_payout'    => $totalPayout,
            'months_elapsed'  => $monthsElapsed,
            'is_mature'       => $isMature,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE: GL Voucher Posting Methods
    // ─────────────────────────────────────────────────────────────

    private function postInstallmentVoucher(DpsAccount $account, AccountHead $paymentHead, DpsTransaction $dpsTxn, float $penaltyAmount): void
    {
        $scId     = auth()->user()->activeConcernId();
        $date     = $dpsTxn->date instanceof \Carbon\Carbon ? $dpsTxn->date->format('Y-m-d') : $dpsTxn->date;
        $amount   = $dpsTxn->amount;
        $noteText = "DPS Installment #{$dpsTxn->installment_no} — A/C: {$account->dps_no}";

        $dpsHead  = $this->getOrCreateDpsHead($account);

        $voucherNo     = generateVoucherNo(VoucherType::$COLLECTION_VOUCHER);
        $voucherSl     = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

        $voucher = Voucher::create([
            'sister_concern_id'       => $scId,
            'voucher_type'            => VoucherType::$COLLECTION_VOUCHER,
            'payment_type_id'         => $paymentHead->payment_mode ?? null,
            'payment_account_head_id' => $paymentHead->id, // Receipt Mode
            'amount'                  => $amount,
            'date'                    => $date,
            'voucher_no'              => $voucherNo,
            'voucher_no_group_sl'     => $voucherSl,
            'notes'                   => $noteText,
            'user_id'                 => auth()->id(),
            'payment_purpose'         => PaymentPurpose::$DPS_INSTALLMENT,
        ]);

        // DR Cash/Bank
        Transaction::create([
            'sister_concern_id'   => $scId,
            'voucher_id'          => $voucher->id,
            'account_head_id'     => $paymentHead->id,
            'voucher_type'        => VoucherType::$COLLECTION_VOUCHER,
            'transaction_type'    => TransactionType::$DEBIT,
            'amount'              => $amount,
            'date'                => $date,
            'user_id'             => auth()->id(),
            'notes'               => $noteText,
            'voucher_no'          => $voucherNo,
            'voucher_no_group_sl' => $voucherSl,
            'payment_purpose'     => PaymentPurpose::$DPS_INSTALLMENT,
        ]);

        // CR Customer DPS Liability
        Transaction::create([
            'sister_concern_id'   => $scId,
            'voucher_id'          => $voucher->id,
            'account_head_id'     => $dpsHead->id,
            'voucher_type'        => VoucherType::$COLLECTION_VOUCHER,
            'transaction_type'    => TransactionType::$CREDIT,
            'amount'              => $amount,
            'date'                => $date,
            'user_id'             => auth()->id(),
            'notes'               => $noteText,
            'voucher_no'          => $voucherNo,
            'voucher_no_group_sl' => $voucherSl,
            'payment_purpose'     => PaymentPurpose::$DPS_INSTALLMENT,
        ]);

        $dpsTxn->update(['voucher_id' => $voucher->id]);
    }

    private function postEncashmentVoucher(DpsAccount $account, AccountHead $paymentHead, DpsTransaction $dpsTxn, float $interestAmount, float $penaltyAmount): void
    {
        $scId      = auth()->user()->activeConcernId();
        $date      = $dpsTxn->date instanceof \Carbon\Carbon ? $dpsTxn->date->format('Y-m-d') : $dpsTxn->date;
        $principal = $account->total_deposited;
        $totalPayout = $principal + $interestAmount - $penaltyAmount;
        $noteText  = "DPS Encashment — A/C: {$account->dps_no}";

        $dpsHead   = $this->getOrCreateDpsHead($account);

        $isPremature = $dpsTxn->type === 'Premature_Encashment';
        $purposeCode = $isPremature ? PaymentPurpose::$DPS_PREMATURE_ENCASHMENT : PaymentPurpose::$DPS_ENCASHMENT;

        $voucherNo = generateVoucherNo(VoucherType::$PAYMENT_VOUCHER);
        $voucherSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

        $voucher = Voucher::create([
            'sister_concern_id'       => $scId,
            'voucher_type'            => VoucherType::$PAYMENT_VOUCHER,
            'payment_type_id'         => $paymentHead->payment_mode ?? null,
            'payment_account_head_id' => $paymentHead->id, // Payment Mode
            'amount'                  => $totalPayout,
            'date'                    => $date,
            'voucher_no'              => $voucherNo,
            'voucher_no_group_sl'     => $voucherSl,
            'notes'                   => $noteText,
            'user_id'                 => auth()->id(),
            'payment_purpose'         => $purposeCode,
        ]);

        // DR Customer DPS Head (Liability decreases — full principal)
        Transaction::create([
            'sister_concern_id'   => $scId,
            'voucher_id'          => $voucher->id,
            'account_head_id'     => $dpsHead->id,
            'voucher_type'        => VoucherType::$PAYMENT_VOUCHER,
            'transaction_type'    => TransactionType::$DEBIT,
            'amount'              => $principal,
            'date'                => $date,
            'user_id'             => auth()->id(),
            'notes'               => 'DPS Principal Return',
            'voucher_no'          => $voucherNo,
            'voucher_no_group_sl' => $voucherSl,
            'payment_purpose'     => $purposeCode,
        ]);

        // DR DPS Interest Expense (if interest applicable)
        if ($interestAmount > 0) {
            $interestExpHead = $this->getOrCreateInterestExpenseHead();
            Transaction::create([
                'sister_concern_id'   => $scId,
                'voucher_id'          => $voucher->id,
                'account_head_id'     => $interestExpHead->id,
                'voucher_type'        => VoucherType::$PAYMENT_VOUCHER,
                'transaction_type'    => TransactionType::$DEBIT,
                'amount'              => $interestAmount,
                'date'                => $date,
                'user_id'             => auth()->id(),
                'notes'               => 'DPS Interest Payout',
                'voucher_no'          => $voucherNo,
                'voucher_no_group_sl' => $voucherSl,
                'payment_purpose'     => $purposeCode,
            ]);
        }

        // CR DPS Penalty Income (if penalty applicable)
        if ($penaltyAmount > 0) {
            $penaltyIncHead = $this->getOrCreatePenaltyIncomeHead();
            Transaction::create([
                'sister_concern_id'   => $scId,
                'voucher_id'          => $voucher->id,
                'account_head_id'     => $penaltyIncHead->id,
                'voucher_type'        => VoucherType::$PAYMENT_VOUCHER,
                'transaction_type'    => TransactionType::$CREDIT,
                'amount'              => $penaltyAmount,
                'date'                => $date,
                'user_id'             => auth()->id(),
                'notes'               => 'DPS Premature Penalty',
                'voucher_no'          => $voucherNo,
                'voucher_no_group_sl' => $voucherSl,
                'payment_purpose'     => $purposeCode,
            ]);
        }

        // CR Cash/Bank (total payout to customer)
        Transaction::create([
            'sister_concern_id'   => $scId,
            'voucher_id'          => $voucher->id,
            'account_head_id'     => $paymentHead->id,
            'voucher_type'        => VoucherType::$PAYMENT_VOUCHER,
            'transaction_type'    => TransactionType::$CREDIT,
            'amount'              => $totalPayout,
            'date'                => $date,
            'user_id'             => auth()->id(),
            'notes'               => $noteText,
            'voucher_no'          => $voucherNo,
            'voucher_no_group_sl' => $voucherSl,
            'payment_purpose'     => $purposeCode,
        ]);

        $dpsTxn->update(['voucher_id' => $voucher->id]);
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE: Account Head Helpers
    // ─────────────────────────────────────────────────────────────

    private function getOrCreateDpsHead(DpsAccount $account): AccountHead
    {
        $dpsGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::DPS_DEPOSITS)->first();
        if (!$dpsGroup) {
            $currentLiabilities = AccountGroup::where('reserved_id', ReservedAccountGroup::CURRENT_LIABILITIES)->first();
            $dpsGroup = AccountGroup::firstOrCreate(
                ['reserved_id' => ReservedAccountGroup::DPS_DEPOSITS],
                [
                    'name'             => 'DPS Deposits',
                    'account_group_id' => $currentLiabilities->id ?? null,
                    'is_editable'      => 0,
                    'is_leaf'          => 1,
                ]
            );
        }

        return AccountHead::firstOrCreate(
            [
                'supplier_id'      => $account->customer_id,
                'account_group_id' => $dpsGroup->id,
                'head_type'        => 'dps',
            ],
            [
                'sister_concern_id' => null,
                'code'              => generateAccountCode($dpsGroup->top_reserved_id ?? ReservedAccountGroup::LIABILITIES),
                'name'              => 'DPS - ' . ($account->customer->name ?? 'Customer') . ' (' . ($account->customer->mobile_no ?? '') . ')',
                'nature'            => 'Credit',
                'is_editable'       => 0,
                'is_system'         => 0,
                'status'            => 1,
            ]
        );
    }

    private function getOrCreateInterestExpenseHead(): AccountHead
    {
        $head = AccountHead::where('reserved_id', ReservedAccountHead::DPS_INTEREST_EXPENSE)->first();
        if (!$head) {
            $expGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::FINANCIAL_EXPENSES)->first();
            $head = AccountHead::firstOrCreate(
                ['reserved_id' => ReservedAccountHead::DPS_INTEREST_EXPENSE],
                [
                    'name'              => 'DPS Interest Expense',
                    'sister_concern_id' => null,
                    'account_group_id'  => $expGroup->id ?? null,
                    'code'              => generateAccountCode($expGroup->top_reserved_id ?? ReservedAccountGroup::EXPENSES),
                    'nature'            => 'Debit',
                    'head_type'         => 'primary',
                    'is_system'         => 1,
                    'is_editable'       => 0,
                    'status'            => 1,
                ]
            );
        }
        return $head;
    }

    private function getOrCreatePenaltyIncomeHead(): AccountHead
    {
        $head = AccountHead::where('reserved_id', ReservedAccountHead::DPS_PENALTY_INCOME)->first();
        if (!$head) {
            $incGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::OTHER_INCOME)->first();
            $head = AccountHead::firstOrCreate(
                ['reserved_id' => ReservedAccountHead::DPS_PENALTY_INCOME],
                [
                    'name'              => 'DPS Penalty Income',
                    'sister_concern_id' => null,
                    'account_group_id'  => $incGroup->id ?? null,
                    'code'              => generateAccountCode($incGroup->top_reserved_id ?? ReservedAccountGroup::REVENUE),
                    'nature'            => 'Credit',
                    'head_type'         => 'primary',
                    'is_system'         => 1,
                    'is_editable'       => 0,
                    'status'            => 1,
                ]
            );
        }
        return $head;
    }
}
