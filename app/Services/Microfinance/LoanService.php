<?php

namespace App\Services\Microfinance;

use App\Models\Microfinance\MfLoan;
use App\Models\Microfinance\MfInstallment;
use App\Models\Microfinance\MfLoanProduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LoanService
{
    /**
     * Stage 1: Approve a pending loan application.
     */
    public function approve(MfLoan $loan, array $data): MfLoan
    {
        return DB::transaction(function () use ($loan, $data) {
            if ($loan->status !== 1) {
                throw new \Exception('Only pending loans can be approved.');
            }

            if ($loan->sister_concern_id !== auth()->user()->activeConcernId()) {
                throw new \Exception('Unauthorized action.');
            }

            $loan->update([
                'approved_amount' => $data['approved_amount'],
                'status'          => 2, // Approved
                'approved_by'     => auth()->id(),
            ]);

            return $loan->fresh();
        });
    }

    /**
     * Stage 2: Disburse an approved loan. Creates installment schedule.
     */
    public function disburse(MfLoan $loan, array $data): MfLoan
    {
        return DB::transaction(function () use ($loan, $data) {
            if ($loan->status !== 2) {
                throw new \Exception('Only approved loans can be disbursed.');
            }

            if ($loan->sister_concern_id !== auth()->user()->activeConcernId()) {
                throw new \Exception('Unauthorized action.');
            }

            $approvedAmount   = $loan->approved_amount;
            $processingFee    = $loan->processing_fee ?? 0;
            $insuranceAmount  = $loan->insurance_amount ?? 0;
            $netDisbursed     = $approvedAmount - $processingFee - $insuranceAmount;

            $loan->update([
                'disbursed_amount'       => $approvedAmount,
                'net_disbursed_amount'   => $netDisbursed,
                'disbursement_date'      => $data['disbursement_date'],
                'first_installment_date' => $data['first_installment_date'],
                'status'                 => 3, // Active
            ]);

            // Delete any existing installments before regenerating
            $loan->installments()->delete();

            $this->generateInstallments($loan);

            return $loan->fresh();
        });
    }

    /**
     * Reject a loan application.
     */
    public function reject(MfLoan $loan): MfLoan
    {
        if ($loan->sister_concern_id !== auth()->user()->activeConcernId()) {
            throw new \Exception('Unauthorized action.');
        }

        if (!in_array($loan->status, [1, 2])) {
            throw new \Exception('Only pending or approved loans can be rejected.');
        }

        $loan->update(['status' => 5]);
        return $loan;
    }

    /**
     * Mark a loan as formally closed.
     */
    public function close(MfLoan $loan): MfLoan
    {
        if ($loan->sister_concern_id !== auth()->user()->activeConcernId()) {
            throw new \Exception('Unauthorized action.');
        }

        $unpaidCount = $loan->installments()->whereIn('status', [1, 3])->count();
        if ($unpaidCount > 0) {
            throw new \Exception("Cannot close loan: {$unpaidCount} installment(s) still unpaid.");
        }

        $loan->update(['status' => 4]);
        return $loan;
    }

    /**
     * Generate installment schedule based on interest type.
     * interest_type: 1 = Flat, 2 = Reducing Balance
     */
    public function generateInstallments(MfLoan $loan): void
    {
        $product         = $loan->product;
        $amount          = $loan->approved_amount;
        $annualRate      = $product->interest_rate;
        $numInstallments = $loan->total_installments;
        $frequency       = $product->installment_frequency; // 1=Daily,2=Weekly,3=Monthly
        $interestType    = $product->interest_type ?? 1;    // 1=Flat,2=Reducing

        $nextDueDate = Carbon::parse($loan->first_installment_date);

        if ($interestType == 2) {
            // ── Reducing Balance (EMI) ──────────────────────────────────
            $periodicRate = $this->getPeriodicRate($annualRate, $frequency);

            if ($periodicRate > 0) {
                // EMI = P * r * (1+r)^n / ((1+r)^n - 1)
                $emi = $amount * $periodicRate * pow(1 + $periodicRate, $numInstallments)
                     / (pow(1 + $periodicRate, $numInstallments) - 1);
                $emi = intval(round($emi)); // Strictly round to whole Taka
            } else {
                $emi = intval(round($amount / $numInstallments));
            }

            $balance = $amount;

            for ($i = 1; $i <= $numInstallments; $i++) {
                $interestAmount  = intval(round($balance * $periodicRate));
                $principalAmount = $emi - $interestAmount;

                // Last installment: clear remaining balance
                if ($i == $numInstallments) {
                    $principalAmount = $balance;
                    $emi = $principalAmount + $interestAmount;
                }

                MfInstallment::create([
                    'sister_concern_id' => $loan->sister_concern_id,
                    'somiti_id'         => $loan->somiti_id,
                    'mf_loan_id'        => $loan->id,
                    'installment_no'    => $i,
                    'due_date'          => $nextDueDate->format('Y-m-d'),
                    'principal_amount'  => $principalAmount,
                    'interest_amount'   => $interestAmount,
                    'total_due'         => $emi,
                    'status'            => 1,
                ]);

                $balance -= $principalAmount;
                $nextDueDate = $this->addPeriod($nextDueDate, $frequency);
            }
        } else {
            // ── Flat Interest ──────────────────────────────────────────
            $totalInterest        = intval(round(($amount * $annualRate) / 100));
            $totalPayable         = $amount + $totalInterest;
            $installmentTotal     = intval(round($totalPayable / $numInstallments));
            $installmentPrincipal = intval(round($amount / $numInstallments));
            $installmentInterest  = $installmentTotal - $installmentPrincipal;

            $accumulatedPrincipal = 0;
            $accumulatedInterest  = 0;

            for ($i = 1; $i <= $numInstallments; $i++) {
                if ($i == $numInstallments) {
                    // Last installment: adjust to match the exact totals!
                    $currentPrincipal = $amount - $accumulatedPrincipal;
                    $currentInterest  = $totalInterest - $accumulatedInterest;
                    $currentTotal     = $currentPrincipal + $currentInterest;
                } else {
                    $currentPrincipal = $installmentPrincipal;
                    $currentInterest  = $installmentInterest;
                    $currentTotal     = $installmentTotal;

                    $accumulatedPrincipal += $currentPrincipal;
                    $accumulatedInterest  += $currentInterest;
                }

                MfInstallment::create([
                    'sister_concern_id' => $loan->sister_concern_id,
                    'somiti_id'         => $loan->somiti_id,
                    'mf_loan_id'        => $loan->id,
                    'installment_no'    => $i,
                    'due_date'          => $nextDueDate->format('Y-m-d'),
                    'principal_amount'  => $currentPrincipal,
                    'interest_amount'   => $currentInterest,
                    'total_due'         => $currentTotal,
                    'status'            => 1,
                ]);

                $nextDueDate = $this->addPeriod($nextDueDate, $frequency);
            }
        }
    }

    /**
     * Apply overdue penalties to pending installments.
     * Called by scheduled command daily.
     */
    public function applyPenalties(): int
    {
        $today = Carbon::today();
        $count = 0;

        MfInstallment::whereIn('status', [1, 3])
            ->where('due_date', '<', $today)
            ->where('penalty_amount', 0)
            ->with('loan.product')
            ->chunk(200, function ($installments) use (&$count) {
                foreach ($installments as $installment) {
                    $product      = $installment->loan->product;
                    $totalDue     = $installment->total_due;
                    $penaltyType  = $product->late_penalty_type ?? 'percentage';
                    $penaltyRate  = $product->late_penalty_rate ?? 0;
                    
                    if ($penaltyType === 'flat') {
                        $penalty = round($penaltyRate, 2);
                    } else {
                        $penalty = round(($totalDue * $penaltyRate) / 100, 2);
                    }

                    if ($penalty > 0) {
                        $installment->update([
                            'penalty_amount' => $penalty,
                            'total_due'      => $totalDue + $penalty,
                        ]);
                        $count++;
                    }
                }
            });

        return $count;
    }

    // ── Helpers ────────────────────────────────────────────────────

    private function getPeriodicRate(float $annualRate, int $frequency): float
    {
        // Convert annual % to periodic decimal
        switch ($frequency) {
            case 1: return ($annualRate / 100) / 365; // Daily
            case 2: return ($annualRate / 100) / 52;  // Weekly
            case 3: return ($annualRate / 100) / 12;  // Monthly
        }
        return ($annualRate / 100) / 12;
    }

    private function addPeriod(Carbon $date, int $frequency): Carbon
    {
        switch ($frequency) {
            case 1: return $date->addDay();
            case 2: return $date->addWeek();
            case 3: return $date->addMonth();
        }
        return $date->addMonth();
    }
}
