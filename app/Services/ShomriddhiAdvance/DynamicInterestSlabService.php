<?php

namespace App\Services\ShomriddhiAdvance;

use App\Models\ShomriddhiAdvance\ShomriddhiInterestSlab;

class DynamicInterestSlabService
{
    /**
     * Get the applicable annual interest rate for a given principal balance.
     */
    public function getRateForAmount($amount): float
    {
        $slab = ShomriddhiInterestSlab::where('min_amount', '<=', $amount)
            ->where(function ($query) use ($amount) {
                $query->where('max_amount', '>=', $amount)
                      ->orWhereNull('max_amount');
            })
            ->first();

        // If no slab matches (e.g., balance is below the minimum tier), return 0%
        return $slab ? (float) $slab->interest_rate : 0.00;
    }
}
