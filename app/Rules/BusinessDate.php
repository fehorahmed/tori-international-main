<?php

namespace App\Rules;

use App\Models\BranchManagement\SisterConcern;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class BusinessDate implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $activeConcernId = auth()->user()->activeConcernId();
        $sisterConcern = SisterConcern::find($activeConcernId);
        $businessDate = $sisterConcern ? $sisterConcern->business_date : null;

        if (!$businessDate) {
            $fail('Sister concern business date not found.');
            return;
        }

        try {
            $inputDate = \Carbon\Carbon::parse($value)->format('Y-m-d');
            $businessDateFormatted = \Carbon\Carbon::parse($businessDate)->format('Y-m-d');

            if ($inputDate !== $businessDateFormatted) {
                $fail('The :attribute must be the business date (' . \Carbon\Carbon::parse($businessDate)->format('d-m-Y') . ').');
            }
        } catch (\Exception $e) {
            $fail('The :attribute is invalid.');
        }
    }
}
