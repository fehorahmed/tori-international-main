<?php

namespace App\Services\Membership;

use App\Models\CustomerKyc;
use App\Models\Supplier;
use Illuminate\Validation\ValidationException;

class KycEligibilityService
{
    public static function status(Supplier $customer): string
    {
        $kyc = CustomerKyc::where('customer_id', $customer->id)->latest('id')->first();
        if ($kyc?->status === 'Verified' && $kyc->submitted_name !== $customer->name) {
            return 'Needs resubmission';
        }
        return $kyc?->status ?? 'Not submitted';
    }

    public static function ensureApproved(Supplier $customer): void
    {
        if (self::status($customer) !== 'Verified') {
            throw ValidationException::withMessages(['kyc' => request()->getPreferredLanguage(['en', 'bn']) === 'bn'
                ? 'টাকা পাঠানো বা ক্যাশ আউটের জন্য কেওয়াইসি অনুমোদিত হতে হবে।'
                : 'Approved KYC is required to send money or cash out.']);
        }
    }
}
