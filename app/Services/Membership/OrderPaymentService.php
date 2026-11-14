<?php

namespace App\Services\Membership;

use App\Enumeration\ReservedAccountHead;
use App\Models\Account\AccountHead;
use App\Models\Account\InterCompanySettlement;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Ecommerce\PackageOrderCheckout;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use Illuminate\Support\Facades\DB;

class OrderPaymentService
{
    public const PAYMENT = 31;
    public const REFUND = 32;
    public const CANCELLATION = 33;

    public static function head(int $reserved, string $nature): int
    {
        $head = AccountHead::where('reserved_id', $reserved)->whereNull('sister_concern_id')->where('status', 1)->first();
        if (! $head || $head->nature !== $nature) {
            CashoutService::fail('Required ecommerce payment ledger is missing or invalid ('.$reserved.').');
        }
        return $head->id;
    }

    private static function journal(int $order, int $branch, int $actor, string $note, int $purpose, array $entries): Voucher
    {
        $number = generateVoucherNo(1, 'vouchers', $branch);
        $voucher = Voucher::create(['sale_order_id' => $order, 'sister_concern_id' => $branch,
            'voucher_type' => 1, 'voucher_no' => $number, 'voucher_no_group_sl' => (int) filter_var($number, FILTER_SANITIZE_NUMBER_INT),
            'date' => SisterConcern::findOrFail($branch)->business_date ?: now()->toDateString(),
            'amount' => CashoutService::money(array_sum(array_map(fn ($e) => $e[1] === 1 ? $e[2] : 0, $entries))),
            'notes' => $note, 'user_id' => $actor, 'payment_purpose' => $purpose]);
        foreach ($entries as [$head, $type, $amount]) {
            Transaction::create(['sale_order_id' => $order, 'sister_concern_id' => $branch, 'voucher_id' => $voucher->id,
                'voucher_type' => 1, 'account_head_id' => $head, 'transaction_type' => $type,
                'amount' => CashoutService::money($amount), 'date' => $voucher->date, 'voucher_no' => $number,
                'voucher_no_group_sl' => $voucher->voucher_no_group_sl, 'notes' => $note, 'user_id' => $actor, 'payment_purpose' => $purpose]);
        }
        return $voucher;
    }

    public static function capture(PackageOrderCheckout $checkout, int $actor): void
    {
        $order = $checkout->saleOrder;
        $accounts = ShomriddhiAccount::where('customer_id', $checkout->customer_id)->where('status', 'Active')->lockForUpdate()->get();
        $account = $accounts->count() === 1 ? $accounts->first() : null;
        $amount = CashoutService::minor($order->total);
        if (! $account || $amount <= 0 || CashoutService::available($account) < $amount) {
            CashoutService::fail('Insufficient available membership balance. The protected minimum and cash-out holds cannot be spent.', 'ব্যবহারযোগ্য সদস্য ব্যালেন্স পর্যাপ্ত নয়। সংরক্ষিত ন্যূনতম ব্যালেন্স ও ক্যাশ আউটের টাকা খরচ করা যাবে না।');
        }
        $savings = AccountHead::where('supplier_id', $checkout->customer_id)->where('head_type', 'savings')->where('status', 1)->first();
        if (! $savings || $savings->nature !== 'Credit') CashoutService::fail('Customer savings ledger head is missing.');
        $origin = (int) $account->origin_sister_concern_id;
        $destination = (int) $order->sister_concern_id;
        $branches = SisterConcern::whereIn('id', [$origin, $destination])->orderBy('id')->lockForUpdate()->get();
        if ($branches->count() !== count(array_unique([$origin, $destination])) || $branches->contains(fn ($b) => $b->status != 1)) CashoutService::fail('Payment branch is inactive.');
        $advance = self::head(ReservedAccountHead::CUSTOMER_ADVANCE_RECEIVED, 'Credit');
        $note = 'Ecommerce account payment '.$order->order_no;
        if ($origin === $destination) {
            $source = self::journal($order->id, $origin, $actor, $note, self::PAYMENT, [[$savings->id, 1, $amount], [$advance, 2, $amount]]);
            $vouchers = [$source->id];
            $settlement = null;
        } else {
            $payable = self::head(ReservedAccountHead::INTER_COMPANY_PAYABLE, 'Credit');
            $receivable = self::head(ReservedAccountHead::INTER_COMPANY_RECEIVABLE, 'Debit');
            $source = self::journal($order->id, $origin, $actor, $note, self::PAYMENT, [[$savings->id, 1, $amount], [$payable, 2, $amount]]);
            $receiver = self::journal($order->id, $destination, $actor, $note, self::PAYMENT, [[$receivable, 1, $amount], [$advance, 2, $amount]]);
            $vouchers = [$source->id, $receiver->id];
            $settlement = InterCompanySettlement::create(['payment_no' => $order->order_no.'-PAY', 'from_sister_concern_id' => $origin,
                'to_sister_concern_id' => $destination, 'date' => $source->date, 'amount' => $order->total,
                'sender_voucher_id' => $source->id, 'receiver_voucher_id' => $receiver->id, 'status' => 2,
                'create_user_id' => $actor, 'received_user_id' => $actor, 'notes' => $note]);
        }
        $account->principal_balance = CashoutService::money(CashoutService::minor($account->principal_balance) - $amount);
        $account->save();
        self::log($checkout->customer_id, 'withdraw', $source, $actor);
        DB::table('membership_order_payments')->insert(['checkout_id' => $checkout->id, 'customer_id' => $checkout->customer_id,
            'account_id' => $account->id, 'amount' => $order->total, 'voucher_ids' => json_encode($vouchers),
            'settlement_id' => $settlement?->id, 'status' => 'Paid', 'created_at' => now(), 'updated_at' => now()]);
        $order->update(['paid' => $order->total, 'due' => 0]);
    }

    private static function log(int $customer, string $type, Voucher $voucher, int $actor): void
    {
        ShomriddhiTransaction::create(['customer_id' => $customer, 'type' => $type, 'amount' => $voucher->amount,
            'date' => $voucher->date, 'note' => $voucher->notes, 'status' => 'Approved', 'voucher_id' => $voucher->id,
            'created_by' => $actor, 'approved_by' => $actor, 'approved_at' => now()]);
    }

    public static function reverseVoucher(Voucher $original, int $actor, int $purpose): Voucher
    {
        $entries = Transaction::where('voucher_id', $original->id)->get()->map(fn ($e) => [$e->account_head_id, $e->transaction_type == 1 ? 2 : 1, CashoutService::minor($e->amount)])->all();
        if (! $entries) CashoutService::fail('Original order payment journal is missing.');
        return self::journal($original->sale_order_id, $original->sister_concern_id, $actor, 'Reversal of '.$original->voucher_no.' — '.$original->notes, $purpose, $entries);
    }

    public static function refund(PackageOrderCheckout $checkout, int $actor): void
    {
        $payment = DB::table('membership_order_payments')->where('checkout_id', $checkout->id)->lockForUpdate()->first();
        if (! $payment || $payment->status === 'Refunded') return;
        $account = ShomriddhiAccount::withTrashed()->whereKey($payment->account_id)->lockForUpdate()->firstOrFail();
        $reversals = [];
        foreach (json_decode($payment->voucher_ids, true) as $id) {
            $reversals[$id] = self::reverseVoucher(Voucher::findOrFail($id), $actor, self::REFUND);
        }
        $source = reset($reversals);
        $account->principal_balance = CashoutService::money(CashoutService::minor($account->principal_balance) + CashoutService::minor($payment->amount));
        $account->save();
        self::log($payment->customer_id, 'deposit', $source, $actor);
        if ($payment->settlement_id) {
            $old = InterCompanySettlement::findOrFail($payment->settlement_id);
            InterCompanySettlement::create(['payment_no' => $checkout->saleOrder->order_no.'-REFUND',
                'from_sister_concern_id' => $old->to_sister_concern_id, 'to_sister_concern_id' => $old->from_sister_concern_id,
                'date' => $source->date, 'amount' => $payment->amount, 'sender_voucher_id' => $reversals[$old->receiver_voucher_id]->id,
                'receiver_voucher_id' => $reversals[$old->sender_voucher_id]->id, 'status' => 2,
                'create_user_id' => $actor, 'received_user_id' => $actor, 'notes' => 'Ecommerce payment refund']);
        }
        DB::table('membership_order_payments')->where('id', $payment->id)->update(['status' => 'Refunded',
            'refund_voucher_ids' => json_encode(array_map(fn ($v) => $v->id, array_values($reversals))), 'refunded_at' => now(), 'updated_at' => now()]);
        $checkout->saleOrder->update(['paid' => 0, 'due' => 0]);
    }
}
