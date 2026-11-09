<?php

namespace App\Services\Microfinance;

use App\Enumeration\ReservedAccountHead;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\Microfinance\MfInstallment;
use App\Models\Microfinance\MfLoan;
use App\Models\Microfinance\MfLoanProduct;
use App\Models\Microfinance\MfMember;
use App\Models\Microfinance\MfSavingsAccount;
use App\Models\Microfinance\MfSavingsProduct;
use App\Models\Microfinance\MfSavingsTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OpeningBalanceService
{
    // ─────────────────────────────────────────────────────────────
    // 1. General Savings Opening
    // ─────────────────────────────────────────────────────────────
    public function createGeneralSavingsOpening(array $data): MfSavingsAccount
    {
        return DB::transaction(function () use ($data) {
            $scId          = auth()->user()->activeConcernId();
            $migrationDate = auth()->user()->business_date;
            $member        = MfMember::where('sister_concern_id', $scId)->findOrFail($data['member_id']);
            $product       = MfSavingsProduct::findOrFail($data['mf_savings_product_id']);

            // Validate: no existing active GS account
            $hasGS = MfSavingsAccount::where('member_id', $member->id)
                ->whereHas('product', fn($q) => $q->where('product_type', 1))
                ->whereIn('status', [0, 1])
                ->exists();
            if ($hasGS) {
                throw new \Exception('This member already has a General Savings account!');
            }

            $balance     = floatval($data['current_balance']);
            $openingDate = Carbon::createFromFormat('d-m-Y', $data['opening_date'])->format('Y-m-d');

            // Create Account — directly Active
            $account = MfSavingsAccount::create([
                'sister_concern_id'    => $scId,
                'somiti_id'            => $member->somiti_id,
                'member_id'            => $member->id,
                'mf_savings_product_id'=> $product->id,
                'account_no'           => $this->generateAccountNo($member, $product, $scId),
                'account_title'        => $data['account_title'] ?? $member->name,
                'interest_rate'        => $product->interest_rate,
                'opening_balance'      => $balance,
                'current_balance'      => $balance,
                'opening_date'         => $openingDate,
                'status'               => 1,        // Active
                'is_opening_entry'     => 1,
                'migration_date'       => $migrationDate,
                'created_by'           => auth()->id(),
            ]);

            // Opening Transaction
            MfSavingsTransaction::create([
                'sister_concern_id'     => $scId,
                'somiti_id'             => $member->somiti_id,
                'mf_savings_account_id' => $account->id,
                'transaction_type'      => MfSavingsTransaction::TYPE_OPENING,
                'amount'                => $balance,
                'date'                  => $migrationDate,
                'notes'                 => 'Migration Opening Balance (From Old Software)',
                'created_by'            => auth()->id(),
            ]);

            // GL Entry: Dr. Opening Balance Equity → Cr. MFI Savings Liability
            $this->postOpeningGlEntry($scId, $balance, $migrationDate,
                'General Savings Opening Balance: ' . $account->account_no,
                debitReservedId: ReservedAccountHead::OPENING_BALANCE_EQUITY,
                creditReservedId: ReservedAccountHead::MFI_SAVINGS_ACCOUNT
            );

            return $account;
        });
    }

    // ─────────────────────────────────────────────────────────────
    // 2. DPS Opening
    // ─────────────────────────────────────────────────────────────
    public function createDpsOpening(array $data): MfSavingsAccount
    {
        return DB::transaction(function () use ($data) {
            $scId          = auth()->user()->activeConcernId();
            $migrationDate = auth()->user()->business_date;
            $member        = MfMember::where('sister_concern_id', $scId)->findOrFail($data['member_id']);
            $product       = MfSavingsProduct::findOrFail($data['mf_savings_product_id']);

            // Validate: must have active GS
            $this->assertHasActiveGS($member->id);

            $balance         = floatval($data['current_balance']);
            $installmentsPaid= intval($data['installments_paid']);
            $openingDate     = Carbon::createFromFormat('d-m-Y', $data['opening_date'])->format('Y-m-d');
            $nextDueDate     = Carbon::createFromFormat('d-m-Y', $data['next_due_date']);
            $maturityDate    = Carbon::parse($openingDate)->addMonths((int)$product->duration_months)->format('Y-m-d');

            // Create Account — directly Active
            $account = MfSavingsAccount::create([
                'sister_concern_id'     => $scId,
                'somiti_id'             => $member->somiti_id,
                'member_id'             => $member->id,
                'mf_savings_product_id' => $product->id,
                'account_no'            => $this->generateAccountNo($member, $product, $scId),
                'account_title'         => $data['account_title'] ?? $member->name,
                'interest_rate'         => $product->interest_rate,
                'installment_amount'    => $product->installment_amount,
                'installment_frequency' => $product->installment_frequency,
                'duration_months'       => $product->duration_months,
                'opening_balance'       => $balance,
                'current_balance'       => $balance,
                'opening_date'          => $openingDate,
                'maturity_date'         => $maturityDate,
                'status'                => 1,
                'is_opening_entry'      => 1,
                'migration_date'        => $migrationDate,
                'installments_paid'     => $installmentsPaid,
                'created_by'            => auth()->id(),
            ]);

            // Bulk Opening Transaction (represents all previously paid installments)
            MfSavingsTransaction::create([
                'sister_concern_id'     => $scId,
                'somiti_id'             => $member->somiti_id,
                'mf_savings_account_id' => $account->id,
                'transaction_type'      => MfSavingsTransaction::TYPE_OPENING,
                'amount'                => $balance,
                'date'                  => $migrationDate,
                'notes'                 => "Migration Opening: {$installmentsPaid} installments already paid in old software",
                'created_by'            => auth()->id(),
            ]);

            // Generate remaining installment schedule only
            $totalInstallments  = intval($product->duration_months); // DPS: 1 per month
            $remainingCount     = $totalInstallments - $installmentsPaid;
            $installmentAmount  = floatval($product->installment_amount);

            for ($i = 1; $i <= $remainingCount; $i++) {
                MfSavingsAccount::where('id', $account->id); // dummy to reference account
                // We'll track as MfInstallment-like entry on loan side; for savings DPS
                // remaining schedule is tracked via installments_paid + next_due_date
                // No separate installment table for savings — tracked by collection
            }

            // GL Entry: Dr. Opening Balance Equity → Cr. MFI Savings Liability
            $this->postOpeningGlEntry($scId, $balance, $migrationDate,
                'DPS Opening Balance: ' . $account->account_no . " ({$installmentsPaid} installments paid)",
                debitReservedId: ReservedAccountHead::OPENING_BALANCE_EQUITY,
                creditReservedId: ReservedAccountHead::MFI_SAVINGS_ACCOUNT
            );

            return $account;
        });
    }

    // ─────────────────────────────────────────────────────────────
    // 3. FDR Opening
    // ─────────────────────────────────────────────────────────────
    public function createFdrOpening(array $data): MfSavingsAccount
    {
        return DB::transaction(function () use ($data) {
            $scId          = auth()->user()->activeConcernId();
            $migrationDate = auth()->user()->business_date;
            $member        = MfMember::where('sister_concern_id', $scId)->findOrFail($data['member_id']);
            $product       = MfSavingsProduct::findOrFail($data['mf_savings_product_id']);

            // Validate: must have active GS
            $this->assertHasActiveGS($member->id);

            $principal       = floatval($data['principal_amount']);
            $accruedInterest = floatval($data['opening_accrued_interest'] ?? 0);
            $currentBalance  = $principal + $accruedInterest;
            $openingDate     = Carbon::createFromFormat('d-m-Y', $data['opening_date'])->format('Y-m-d');
            $maturityDate    = isset($data['maturity_date'])
                ? Carbon::createFromFormat('d-m-Y', $data['maturity_date'])->format('Y-m-d')
                : Carbon::parse($openingDate)->addMonths((int)$product->duration_months)->format('Y-m-d');

            // Create Account — directly Active
            $account = MfSavingsAccount::create([
                'sister_concern_id'       => $scId,
                'somiti_id'               => $member->somiti_id,
                'member_id'               => $member->id,
                'mf_savings_product_id'   => $product->id,
                'account_no'              => $this->generateAccountNo($member, $product, $scId),
                'account_title'           => $data['account_title'] ?? $member->name,
                'interest_rate'           => $product->interest_rate,
                'duration_months'         => $product->duration_months,
                'opening_balance'         => $principal,
                'opening_accrued_interest'=> $accruedInterest,
                'current_balance'         => $currentBalance,
                'opening_date'            => $openingDate,
                'maturity_date'           => $maturityDate,
                'status'                  => 1,
                'is_opening_entry'        => 1,
                'migration_date'          => $migrationDate,
                'created_by'              => auth()->id(),
            ]);

            // Opening Transaction
            MfSavingsTransaction::create([
                'sister_concern_id'     => $scId,
                'somiti_id'             => $member->somiti_id,
                'mf_savings_account_id' => $account->id,
                'transaction_type'      => MfSavingsTransaction::TYPE_OPENING,
                'amount'                => $currentBalance,
                'date'                  => $migrationDate,
                'notes'                 => "Migration Opening: Principal ৳{$principal} + Accrued Interest ৳{$accruedInterest}",
                'created_by'            => auth()->id(),
            ]);

            // GL Entry: Dr. Opening Balance Equity → Cr. MFI Savings Liability
            $this->postOpeningGlEntry($scId, $currentBalance, $migrationDate,
                'FDR Opening Balance: ' . $account->account_no,
                debitReservedId: ReservedAccountHead::OPENING_BALANCE_EQUITY,
                creditReservedId: ReservedAccountHead::MFI_SAVINGS_ACCOUNT
            );

            return $account;
        });
    }

    // ─────────────────────────────────────────────────────────────
    // 4. Loan Opening
    // ─────────────────────────────────────────────────────────────
    public function createLoanOpening(array $data): MfLoan
    {
        return DB::transaction(function () use ($data) {
            $scId          = auth()->user()->activeConcernId();
            $migrationDate = auth()->user()->business_date;
            $member        = MfMember::where('sister_concern_id', $scId)->findOrFail($data['member_id']);
            $product       = MfLoanProduct::findOrFail($data['mf_loan_product_id']);

            // Validate: must have active GS
            $this->assertHasActiveGS($member->id);

            $approvedAmount       = floatval($data['approved_amount']);
            $outstandingPrincipal = floatval($data['outstanding_principal']);
            $totalInstallments    = intval($data['total_installments']);
            $installmentsPaid     = intval($data['installments_paid']);
            $remainingInstallments= $totalInstallments - $installmentsPaid;
            $disbursementDate     = Carbon::createFromFormat('d-m-Y', $data['disbursement_date'])->format('Y-m-d');
            $nextDueDate          = Carbon::createFromFormat('d-m-Y', $data['next_due_date']);

            if ($installmentsPaid >= $totalInstallments) {
                throw new \Exception('Already paid installments must be less than total installments.');
            }
            if ($outstandingPrincipal <= 0) {
                throw new \Exception('Outstanding principal must be greater than zero.');
            }

            // Generate loan number
            $count  = MfLoan::where('member_id', $member->id)->count() + 1;
            $loanNo = $product->code . '-LN-' . $member->id . '-' . $count . '-OPN';

            // Create Loan — directly Active (status = 3)
            $loan = MfLoan::create([
                'sister_concern_id'    => $scId,
                'somiti_id'            => $member->somiti_id,
                'member_id'            => $member->id,
                'mf_loan_product_id'   => $product->id,
                'loan_no'              => $loanNo,
                'loan_purpose'         => $data['loan_purpose'] ?? 'Migration Opening Balance',
                'applied_amount'       => $approvedAmount,
                'approved_amount'      => $approvedAmount,
                'disbursed_amount'     => $approvedAmount,
                'net_disbursed_amount' => $approvedAmount,
                'total_installments'   => $totalInstallments,
                'disbursement_date'    => $disbursementDate,
                'first_installment_date'=> $nextDueDate->format('Y-m-d'),
                'status'               => 3, // Active
                'is_opening_entry'     => 1,
                'migration_date'       => $migrationDate,
                'installments_paid'    => $installmentsPaid,
                'outstanding_principal'=> $outstandingPrincipal,
                'created_by'           => auth()->id(),
            ]);

            // Generate installment schedule
            $this->generateOpeningInstallments(
                $loan, $product, $approvedAmount, $outstandingPrincipal,
                $totalInstallments, $installmentsPaid, $nextDueDate, $migrationDate
            );

            // GL Entry: Dr. MFI Loan Portfolio → Cr. Opening Balance Equity
            $this->postOpeningGlEntry($scId, $outstandingPrincipal, $migrationDate,
                'Loan Opening Balance: ' . $loan->loan_no . " (Outstanding: ৳{$outstandingPrincipal})",
                debitReservedId: ReservedAccountHead::MFI_LOAN_PORTFOLIO,
                creditReservedId: ReservedAccountHead::OPENING_BALANCE_EQUITY
            );

            return $loan;
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────────────────────────

    private function assertHasActiveGS(int $memberId): void
    {
        $hasGS = MfSavingsAccount::where('member_id', $memberId)
            ->where('status', 1)
            ->whereHas('product', fn($q) => $q->where('product_type', 1))
            ->exists();

        if (!$hasGS) {
            throw new \Exception('Member must have an active General Savings account before migrating DPS/FDR/Loan.');
        }
    }

    private function generateAccountNo(MfMember $member, MfSavingsProduct $product, int $scId): string
    {
        $count = MfSavingsAccount::where('sister_concern_id', $scId)
                                 ->where('member_id', $member->id)
                                 ->count() + 1;
        return $product->code . '-' . $member->member_no . '-' . $count;
    }

    private function postOpeningGlEntry(
        int    $scId,
        float  $amount,
        string $date,
        string $notes,
        int    $debitReservedId,
        int    $creditReservedId
    ): void {
        $debitHead  = AccountHead::findOrFail(reservedAccountHeadId($debitReservedId));
        $creditHead = AccountHead::findOrFail(reservedAccountHeadId($creditReservedId));

        // Generate Voucher No
        $prefix    = 'MFI-OPN-';
        $maxNo     = Voucher::where('sister_concern_id', $scId)
                            ->where('voucher_no', 'LIKE', "{$prefix}%")
                            ->selectRaw("MAX(CAST(SUBSTRING(voucher_no, LENGTH('{$prefix}') + 1) AS UNSIGNED)) as max_no")
                            ->value('max_no');
        $voucherNo = $prefix . str_pad(($maxNo ?? 0) + 1, 4, '0', STR_PAD_LEFT);
        $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

        $voucher = Voucher::create([
            'sister_concern_id' => $scId,
            'voucher_type'      => VoucherType::$JOURNAL_VOUCHER,
            'amount'            => $amount,
            'date'              => $date,
            'voucher_no'        => $voucherNo,
            'voucher_no_group_sl'=> $voucherNoGroupSl,
            'notes'             => $notes,
            'user_id'           => auth()->id(),
        ]);

        // Debit
        Transaction::create([
            'sister_concern_id'  => $scId,
            'voucher_id'         => $voucher->id,
            'account_head_id'    => $debitHead->id,
            'voucher_type'       => VoucherType::$JOURNAL_VOUCHER,
            'transaction_type'   => TransactionType::$DEBIT,
            'amount'             => $amount,
            'date'               => $date,
            'user_id'            => auth()->id(),
            'notes'              => $notes,
            'voucher_no'         => $voucherNo,
            'voucher_no_group_sl'=> $voucherNoGroupSl,
        ]);
        $debitHead->increment('current_balance', $amount);

        // Credit
        Transaction::create([
            'sister_concern_id'  => $scId,
            'voucher_id'         => $voucher->id,
            'account_head_id'    => $creditHead->id,
            'voucher_type'       => VoucherType::$JOURNAL_VOUCHER,
            'transaction_type'   => TransactionType::$CREDIT,
            'amount'             => $amount,
            'date'               => $date,
            'user_id'            => auth()->id(),
            'notes'              => $notes,
            'voucher_no'         => $voucherNo,
            'voucher_no_group_sl'=> $voucherNoGroupSl,
        ]);
        $creditHead->increment('current_balance', $amount);
    }

    private function generateOpeningInstallments(
        MfLoan       $loan,
        MfLoanProduct $product,
        float        $approvedAmount,
        float        $outstandingPrincipal,
        int          $totalInstallments,
        int          $installmentsPaid,
        Carbon       $nextDueDate,
        string       $migrationDate
    ): void {
        $annualRate      = $product->interest_rate;
        $frequency       = $product->installment_frequency;
        $interestType    = $product->interest_type ?? 1;
        $remaining       = $totalInstallments - $installmentsPaid;

        // --- Paid installments (mark as paid, is_opening_paid = 1) ---
        $paidInstallmentAmount = $installmentsPaid > 0
            ? round(($approvedAmount - $outstandingPrincipal) / $installmentsPaid, 2)
            : 0;

        $pastDate = Carbon::parse($loan->disbursement_date);
        for ($i = 1; $i <= $installmentsPaid; $i++) {
            MfInstallment::create([
                'sister_concern_id' => $loan->sister_concern_id,
                'somiti_id'         => $loan->somiti_id,
                'mf_loan_id'        => $loan->id,
                'installment_no'    => $i,
                'due_date'          => $pastDate->format('Y-m-d'),
                'principal_amount'  => $paidInstallmentAmount,
                'interest_amount'   => 0,
                'total_due'         => $paidInstallmentAmount,
                'paid_amount'       => $paidInstallmentAmount,
                'paid_date'         => $migrationDate,
                'status'            => 2, // Paid
                'is_opening_paid'   => 1,
            ]);
            $pastDate = $this->addPeriod($pastDate, $frequency);
        }

        // --- Remaining installments ---
        if ($interestType == 2) {
            // Reducing Balance on outstanding principal
            $periodicRate = $this->getPeriodicRate($annualRate, $frequency);
            $emi = $periodicRate > 0
                ? intval(round($outstandingPrincipal * $periodicRate * pow(1 + $periodicRate, $remaining)
                    / (pow(1 + $periodicRate, $remaining) - 1)))
                : intval(round($outstandingPrincipal / $remaining));

            $balance = $outstandingPrincipal;
            for ($i = $installmentsPaid + 1; $i <= $totalInstallments; $i++) {
                $seq            = $i - $installmentsPaid;
                $interestAmt    = intval(round($balance * $periodicRate));
                $principalAmt   = $emi - $interestAmt;
                if ($seq == $remaining) {
                    $principalAmt = $balance;
                    $emi          = $principalAmt + $interestAmt;
                }
                MfInstallment::create([
                    'sister_concern_id' => $loan->sister_concern_id,
                    'somiti_id'         => $loan->somiti_id,
                    'mf_loan_id'        => $loan->id,
                    'installment_no'    => $i,
                    'due_date'          => $nextDueDate->format('Y-m-d'),
                    'principal_amount'  => $principalAmt,
                    'interest_amount'   => $interestAmt,
                    'total_due'         => $emi,
                    'status'            => 1,
                    'is_opening_paid'   => 0,
                ]);
                $balance -= $principalAmt;
                $nextDueDate = $this->addPeriod($nextDueDate, $frequency);
            }
        } else {
            // Flat interest on outstanding
            $totalInterest    = intval(round(($outstandingPrincipal * $annualRate) / 100));
            $totalPayable     = $outstandingPrincipal + $totalInterest;
            $installmentTotal = intval(round($totalPayable / $remaining));
            $installPrincipal = intval(round($outstandingPrincipal / $remaining));
            $installInterest  = $installmentTotal - $installPrincipal;

            $accPrincipal = 0;
            $accInterest  = 0;

            for ($i = $installmentsPaid + 1; $i <= $totalInstallments; $i++) {
                $seq = $i - $installmentsPaid;
                if ($seq == $remaining) {
                    $curPrincipal = $outstandingPrincipal - $accPrincipal;
                    $curInterest  = $totalInterest - $accInterest;
                    $curTotal     = $curPrincipal + $curInterest;
                } else {
                    $curPrincipal = $installPrincipal;
                    $curInterest  = $installInterest;
                    $curTotal     = $installmentTotal;
                    $accPrincipal += $curPrincipal;
                    $accInterest  += $curInterest;
                }

                MfInstallment::create([
                    'sister_concern_id' => $loan->sister_concern_id,
                    'somiti_id'         => $loan->somiti_id,
                    'mf_loan_id'        => $loan->id,
                    'installment_no'    => $i,
                    'due_date'          => $nextDueDate->format('Y-m-d'),
                    'principal_amount'  => $curPrincipal,
                    'interest_amount'   => $curInterest,
                    'total_due'         => $curTotal,
                    'status'            => 1,
                    'is_opening_paid'   => 0,
                ]);
                $nextDueDate = $this->addPeriod($nextDueDate, $frequency);
            }
        }
    }

    private function getPeriodicRate(float $annualRate, int $frequency): float
    {
        return match ($frequency) {
            1 => ($annualRate / 100) / 365,
            2 => ($annualRate / 100) / 52,
            default => ($annualRate / 100) / 12,
        };
    }

    private function addPeriod(Carbon $date, int $frequency): Carbon
    {
        return match ($frequency) {
            1 => $date->addDay(),
            2 => $date->addWeek(),
            default => $date->addMonth(),
        };
    }
}
