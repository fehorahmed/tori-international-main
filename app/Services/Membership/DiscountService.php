<?php

namespace App\Services\Membership;

use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\Supplier;

class DiscountService
{
    public static function eligible(?Supplier $customer, bool $lock = false): bool
    {
        $setting = membership_transaction_settings('membership_discount');
        if (! $setting->enabled || ! $customer || $customer->type !== 'Customer' || $customer->status != 1) return false;
        $query = ShomriddhiAccount::where('customer_id', $customer->id)->where('status', 'Active');
        if ($lock) $query->lockForUpdate();
        $accounts = $query->get();
        return $accounts->count() === 1 && CashoutService::minor($accounts->first()->principal_balance) >= CashoutService::minor($setting->minimum_balance);
    }

    // Use the existing administrator-configured prices, including their original rounding.
    public static function price($product, bool $eligible): array
    {
        $selling = CashoutService::minor($product['selling_price']);
        $regular = max($selling, CashoutService::minor($product['old_selling_price'] ?? 0));
        $rate = $eligible ? max(0, min(10000, CashoutService::minor($product['member_discount_percent'] ?? 0))) : 0;
        $candidate = min(max(0, $regular - 1), intdiv($regular * $rate + 5000, 10000));
        $useMember = $rate > CashoutService::minor($product['discount_percent'] ?? 0)
            && $candidate > $regular - $selling;
        $memberDiscount = $useMember ? $candidate : 0;
        $net = $useMember ? $regular - $memberDiscount : $selling;
        return ['regular' => $regular, 'net' => $net, 'discount' => $regular - $net,
            'general_discount' => $useMember ? 0 : $regular - $selling, 'member_discount' => $memberDiscount];
    }

    public static function validateRates($general, $member): void
    {
        $member = CashoutService::minor($member ?? 0);
        if ($member > 0 && $member <= CashoutService::minor($general ?? 0)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'member_discount_percent' => 'Member discount must be zero or greater than general discount.',
            ]);
        }
    }

    public static function present(array $product, bool $eligible): array
    {
        $price = self::price($product, $eligible);
        $product['old_selling_price'] = CashoutService::money($price['regular']);
        $product['selling_price'] = CashoutService::money($price['net']);
        $product['discount'] = CashoutService::money($price['discount']);
        $product['general_discount'] = CashoutService::money($price['general_discount']);
        $product['member_discount'] = CashoutService::money($price['member_discount']);
        $product['general_discount_percent'] = $product['discount_percent'] ?? 0;
        $product['discount_percent'] = $price['regular'] > 0 ? round($price['discount'] * 100 / $price['regular'], 2) : 0;
        $product['membership_discount_eligible'] = $eligible;
        return $product;
    }
}
