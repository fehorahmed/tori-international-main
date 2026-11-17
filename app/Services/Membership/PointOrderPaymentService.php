<?php

namespace App\Services\Membership;

use App\Enumeration\ReservedAccountHead;
use App\Models\Account\Voucher;
use App\Models\Account\Transaction;
use App\Models\Ecommerce\PackageOrderCheckout;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class PointOrderPaymentService
{
    public const SCALE = 1000000;
    public const PAYMENT = 38;
    public const REFUND = 39;

    public static function units($points): int
    {
        $parts = explode('.', (string) $points, 2);
        return (int) $parts[0] * self::SCALE + (int) str_pad(substr($parts[1] ?? '', 0, 6), 6, '0');
    }

    public static function decimal(int $units): string
    {
        return intdiv($units, self::SCALE).'.'.str_pad((string) ($units % self::SCALE), 6, '0', STR_PAD_LEFT);
    }

    public static function required(int $amount, int $value): int
    {
        if ($value <= 0 || $amount <= 0 || $amount > intdiv(PHP_INT_MAX - $value, self::SCALE)) CashoutService::fail('Invalid points payment amount or rate.');
        // Round UP to six decimal places: no underpayment, never use binary floats for debits.
        return intdiv($amount * self::SCALE + $value - 1, $value);
    }

    public static function summary(?Supplier $member): ?array
    {
        if (!$member || $member->status != 1 || $member->type !== 'Customer') return null;
        $s = membership_transaction_settings('referral');
        return ['enabled' => (bool) $s->enabled && CashoutService::minor($s->point_value) > 0,
            'points' => self::decimal(ReferralRewardService::pointUnits($member->id)), 'point_value' => $s->point_value];
    }

    public static function capture(PackageOrderCheckout $checkout, int $actor, array $data): void
    {
        Supplier::whereKey($checkout->customer_id)->lockForUpdate()->firstOrFail();
        $s = membership_transaction_settings('referral');
        $value = CashoutService::minor($s->point_value);
        if (!$s->enabled || $value <= 0) CashoutService::fail('Point payments are currently unavailable.');
        if (!isset($data['point_value']) || CashoutService::minor($data['point_value']) !== $value) CashoutService::fail('Point value changed. Refresh and review your payment.');
        $order = $checkout->saleOrder;
        $amount = CashoutService::minor($order->total);
        $units = self::required($amount, $value);
        if (ReferralRewardService::pointUnits($checkout->customer_id, true) < $units) CashoutService::fail('Insufficient membership points to pay the MRP and delivery charge.');
        $expense = ReferralRewardService::ledger($s->expense_head_id, 'Debit', 5);
        $advance = OrderPaymentService::head(ReservedAccountHead::CUSTOMER_ADVANCE_RECEIVED, 'Credit');
        $v = ReferralRewardService::post($order->sister_concern_id, $actor, self::PAYMENT, $amount,
            'Ecommerce points payment '.$order->order_no, $expense->id, $advance);
        $v->update(['sale_order_id' => $order->id]);
        Transaction::where('voucher_id', $v->id)->update(['sale_order_id' => $order->id]);
        DB::table('membership_point_transactions')->insert([
            'customer_id' => $checkout->customer_id, 'checkout_id' => $checkout->id, 'type' => 'Purchase',
            'points' => self::decimal($units), 'point_value' => CashoutService::money($value), 'amount' => $order->total,
            'voucher_id' => $v->id, 'request_key' => $checkout->request_key, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $order->update(['paid' => $order->total, 'due' => 0]);
    }

    public static function refund(PackageOrderCheckout $checkout, int $actor): void
    {
        Supplier::whereKey($checkout->customer_id)->lockForUpdate()->firstOrFail();
        $original = DB::table('membership_point_transactions')->where('checkout_id', $checkout->id)->where('type', 'Purchase')->lockForUpdate()->first();
        if (!$original) CashoutService::fail('Original points payment is missing.');
        if (DB::table('membership_point_transactions')->where('original_transaction_id', $original->id)->exists()) return;
        $v = OrderPaymentService::reverseVoucher(Voucher::findOrFail($original->voucher_id), $actor, self::REFUND);
        DB::table('membership_point_transactions')->insert([
            'customer_id' => $checkout->customer_id, 'checkout_id' => $checkout->id, 'type' => 'Refund',
            'points' => $original->points, 'point_value' => $original->point_value, 'amount' => $original->amount,
            'voucher_id' => $v->id, 'original_transaction_id' => $original->id, 'request_key' => $checkout->request_key,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $checkout->saleOrder->update(['paid' => 0, 'due' => 0]);
    }

    public static function data(PackageOrderCheckout $checkout): ?object
    {
        return DB::table('membership_point_transactions')->where('checkout_id', $checkout->id)->where('type', 'Purchase')
            ->first(['points', 'point_value', 'amount']);
    }
}
