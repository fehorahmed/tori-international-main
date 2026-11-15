<?php

namespace App\Services\Membership;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ReferralService
{
    public static function resolve(string $type, string $code): int
    {
        $query = $type === 'member'
            ? Supplier::where('type', 'Customer')->where('member_no', $code)
            : User::where('username', $code);
        $matches = $query->where('status', 1)->limit(2)->get();
        if ($matches->count() !== 1) self::invalid();
        return $matches->first()->id;
    }

    public static function ensureActive(string $type, int $id): void
    {
        $query = $type === 'member' ? Supplier::where('type', 'Customer') : User::query();
        if (! $query->whereKey($id)->where('status', 1)->lockForUpdate()->first()) self::invalid();
    }

    private static function invalid(): never
    {
        $message = request()->getPreferredLanguage(['en', 'bn']) === 'bn'
            ? 'নির্বাচিত ধরনের জন্য রেফারেল কোডটি ভুল, নিষ্ক্রিয় অথবা একাধিক ব্যক্তির সঙ্গে মিলে গেছে।'
            : 'Referral code is invalid, inactive or ambiguous for the selected Member / Agent type.';
        throw ValidationException::withMessages(['referral_code' => $message]);
    }
}
