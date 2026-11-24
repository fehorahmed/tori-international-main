<?php

namespace App\Rules;

use App\Support\ApplicationDate;
use Carbon\Carbon;
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
        $businessDate = ApplicationDate::today();

        try {
            $inputDate = Carbon::parse($value)->format('Y-m-d');
            $businessDateFormatted = Carbon::parse($businessDate)->format('Y-m-d');

            if ($inputDate !== $businessDateFormatted) {
                $fail('The :attribute must be today’s date ('.Carbon::parse($businessDate)->format('d-m-Y').').');
            }
        } catch (\Exception $e) {
            $fail('The :attribute is invalid.');
        }
    }
}
