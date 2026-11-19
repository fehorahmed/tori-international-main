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
        $offer = isset($product['id']) ? \App\Services\Offers\OfferService::best((int) $product['id'], $regular) : null;
        $useOffer = $offer && $offer['discount'] > $regular - $net;
        if ($useOffer) { $net = $regular - $offer['discount']; $memberDiscount = 0; }
        return ['discount_source' => $useOffer ? 'special_offer' : ($useMember ? 'member' : 'general'),
            'offer' => $useOffer ? $offer : null, 'active_offer' => $offer,
            'offer_discount' => $useOffer ? $offer['discount'] : 0, 'regular' => $regular, 'net' => $net, 'discount' => $regular - $net,
            'general_discount' => ($useMember || $useOffer) ? 0 : $regular - $selling, 'member_discount' => $memberDiscount];
    }

    public static function requiresAccountPayment(array $pricing): bool
    {
        return $pricing['member_discount'] > 0 || $pricing['active_offer'] !== null;
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
        $standardDiscount = max($price['regular'] - CashoutService::minor($product['selling_price']), $price['active_offer']['discount'] ?? 0);
        $memberRate = max(0, min(10000, CashoutService::minor($product['member_discount_percent'] ?? 0)));
        $memberCandidate = min(max(0, $price['regular'] - 1), intdiv($price['regular'] * $memberRate + 5000, 10000));
        $product['member_discount_visible'] = $memberCandidate > $standardDiscount
            && $memberRate > CashoutService::minor($product['discount_percent'] ?? 0);
        $product['standard_discount_percent'] = $price['regular'] > 0 ? round($standardDiscount * 100 / $price['regular'], 2) : 0;
        $product['old_selling_price'] = CashoutService::money($price['regular']);
        $product['selling_price'] = CashoutService::money($price['net']);
        $product['discount'] = CashoutService::money($price['discount']);
        $product['general_discount'] = CashoutService::money($price['general_discount']);
        $product['member_discount'] = CashoutService::money($price['member_discount']);
        $product['general_discount_percent'] = $product['discount_percent'] ?? 0;
        $product['discount_percent'] = $price['regular'] > 0 ? round($price['discount'] * 100 / $price['regular'], 2) : 0;
        $product['special_offer'] = $price['active_offer'];
        $product['discount_source'] = $price['discount_source'];
        $product['offer_discount'] = CashoutService::money($price['offer_discount']);
        $product['membership_discount_eligible'] = $eligible;
        $setting = membership_transaction_settings('membership_discount');
        $product['membership_discount_minimum_balance'] = CashoutService::money(CashoutService::minor($setting->minimum_balance));
        $product['membership_discount_enabled'] = (bool) $setting->enabled;
        $product['member_discount_visible'] = $product['member_discount_visible'] && $product['membership_discount_enabled'];
        return $product;
    }
}
