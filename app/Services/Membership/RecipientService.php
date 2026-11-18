<?php

namespace App\Services\Membership;

use App\Models\Supplier;

class RecipientService
{
    public static function resolve(Supplier $sender, string $identifier = '', ?int $id = null): Supplier
    {
        $identifier = trim($identifier);
        $digits = preg_replace('/\D/', '', $identifier);
        if (str_starts_with($digits, '880') && strlen($digits) === 13) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            $digits = '0'.$digits;
        }
        $query = Supplier::where('type', 'Customer')->where('status', 1);
        if ($id !== null) {
            $query->whereKey($id);
        } else {
            $query->where(function ($q) use ($identifier, $digits) {
                $q->where('member_no', $identifier);
                if (preg_match('/^01[3-9]\d{8}$/', $digits)) {
                    $q->orWhereIn('mobile_no', [$digits, substr($digits, 1), '88'.$digits, '+88'.$digits]);
                }
            });
        }
        $rows = $query->limit(2)->get();
        if ($rows->count() !== 1) {
            CashoutService::fail('Recipient not found or the identifier is ambiguous.', 'প্রাপক পাওয়া যায়নি অথবা একাধিক হিসাব আছে।');
        }
        $recipient = $rows->first();
        if ($recipient->id === $sender->id) {
            CashoutService::fail('You cannot send money to yourself.', 'নিজের হিসাবে টাকা পাঠানো যাবে না।');
        }

        return $recipient;
    }

    public static function data(Supplier $recipient): array
    {
        return ['recipient_id' => $recipient->id, 'name' => $recipient->name, 'member_no' => $recipient->member_no,
            'active' => $recipient->status == 1 && $recipient->type === 'Customer',
            // KYC documents are private and must never be shared through beneficiary lookup.
            'image' => $recipient->image ? asset($recipient->image) : null];
    }
}
