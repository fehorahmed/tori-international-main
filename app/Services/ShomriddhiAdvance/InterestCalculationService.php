<?php

namespace App\Services\ShomriddhiAdvance;

class InterestCalculationService
{
    /**
     * Calculate 1 day of interest based on the standard compound/daily formula.
     */
    public function calculateDailyInterest(float $principal, float $annualRate): float
    {
        if ($principal <= 0 || $annualRate <= 0) {
            return 0.00;
        }

        // Formula: (Principal * Rate%) / 365
        // Equivalent to: (Principal * (Rate / 100)) / 365
        return ($principal * $annualRate) / 36500;
    }
}
