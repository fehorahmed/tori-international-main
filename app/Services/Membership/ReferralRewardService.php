<?php

namespace App\Services\Membership;

use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\BranchManagement\SisterConcern;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ReferralRewardService
{
    public const ACCRUAL = 34;
    public const REDEMPTION = 35;
    public const PAYMENT = 36;

    // Called only in the successful fee collection transaction, never during registration or refunds.
    public static function award(int $customerId, int $feeId, int $branchId, int $actor): void
    {
        $fee = DB::table('membership_registration_fees')->where('id', $feeId)->lockForUpdate()->first();
        if (! $fee || $fee->customer_id != $customerId || ! $fee->paid_at) return;
        $referral = DB::table('membership_referrals')->where('customer_id', $customerId)->lockForUpdate()->first();
        if (! $referral || DB::table('membership_referral_rewards')->where('referral_id', $referral->id)->exists()) return;
        $s = membership_transaction_settings('referral');
        // Snapshot a zero reward when disabled; later settings changes never retroactively award old fees.
        $points = $s->enabled && $referral->referrer_type === 'member' ? (int) $s->member_points : 0;
        $amount = $s->enabled && $referral->referrer_type === 'agent' ? CashoutService::minor($s->agent_bonus) : 0;
        $voucher = null;
        $payable = null;
        if ($amount > 0) {
            $expense = self::ledger($s->expense_head_id, 'Debit', 5);
            $payable = self::ledger($s->payable_head_id, 'Credit', 2);
            $voucher = self::post($branchId, $actor, self::ACCRUAL, $amount,
                'Agent referral commission / referral '.$referral->id, $expense->id, $payable->id);
        }
        if ($points > 0) Supplier::whereKey($referral->referrer_id)->lockForUpdate()->first();
        DB::table('membership_referral_rewards')->insert([
            'referral_id' => $referral->id, 'registration_fee_id' => $feeId,
            'referrer_type' => $referral->referrer_type, 'referrer_id' => $referral->referrer_id,
            'branch_id' => $branchId, 'points' => $points, 'amount' => CashoutService::money($amount),
            'payable_head_id' => $payable?->id, 'voucher_id' => $voucher?->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        if ($points > 0) MilestoneBonusService::collect($referral->referrer_id);
    }

    public static function points(int $customer, bool $locked = false): int
    {
        $earned = DB::table('membership_referral_rewards')->where('referrer_type', 'member')->where('referrer_id', $customer);
        $spent = DB::table('membership_point_redemptions')->where('customer_id', $customer);
        // Current reads after the member lock: funding transactions may already have an older MySQL snapshot.
        if ($locked) return (int) $earned->orderBy('id')->lockForUpdate()->get(['points'])->sum('points')
            - (int) $spent->orderBy('id')->lockForUpdate()->get(['points'])->sum('points');
        return (int) $earned->sum('points') - (int) $spent->sum('points');
    }

    public static function summary(int $customer): array
    {
        $s = membership_transaction_settings('referral');
        $points = self::points($customer);
        return ['customer_id' => $customer, 'enabled' => (bool) $s->enabled, 'points' => $points,
            'point_value' => $s->point_value, 'redeemable_amount' => CashoutService::money($points * CashoutService::minor($s->point_value)),
            'referral_count' => DB::table('membership_referrals')->where('referrer_type', 'member')->where('referrer_id', $customer)->count(),
            'rewarded_count' => DB::table('membership_referral_rewards')->where('referrer_type', 'member')->where('referrer_id', $customer)->where('points', '>', 0)->count(),
            'milestones' => MilestoneBonusService::summary($customer),
            'redemptions' => DB::table('membership_point_redemptions')->where('customer_id', $customer)->latest('id')->limit(30)->get(['points', 'point_value', 'amount', 'request_key', 'created_at'])];
    }

    public static function redeem(Supplier $customer, int $points, string $rate, string $pin, string $key): object
    {
        return DB::transaction(function () use ($customer, $points, $rate, $pin, $key) {
            // Serializes concurrent redemptions for this member; the unique request key protects retries.
            $customer = Supplier::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $old = DB::table('membership_point_redemptions')->where('customer_id', $customer->id)->where('request_key', $key)->first();
            if ($old) {
                if ($old->points != $points || CashoutService::minor($old->point_value) !== CashoutService::minor($rate)) self::fail('Request key already used.', 'অনুরোধটি আগে ব্যবহার হয়েছে।');
                return $old;
            }
            if ($customer->type !== 'Customer' || $customer->status != 1 || ! $customer->pin || ! Hash::check($pin, $customer->pin)) self::fail('Member is inactive or PIN is incorrect.', 'মেম্বার নিষ্ক্রিয় অথবা পিন সঠিক নয়।');
            $s = membership_transaction_settings('referral');
            $value = CashoutService::minor($s->point_value);
            if (! $s->enabled || $value <= 0) self::fail('Point redemption is currently unavailable.', 'পয়েন্ট রিডিম বর্তমানে বন্ধ আছে।');
            if ($value !== CashoutService::minor($rate)) self::fail('Point value changed. Refresh and confirm again.', 'পয়েন্টের মূল্য বদলেছে। রিফ্রেশ করে আবার নিশ্চিত করুন।');
            if ($points < 1 || $points > 1000000 || self::points($customer->id, true) < $points) self::fail('Insufficient referral points.', 'রেফারেল পয়েন্ট পর্যাপ্ত নয়।');
            $accounts = ShomriddhiAccount::where('customer_id', $customer->id)->where('status', 'Active')->lockForUpdate()->get();
            if ($accounts->count() !== 1) self::fail('Exactly one active savings account is required.', 'একটি সক্রিয় সঞ্চয় হিসাব থাকতে হবে।');
            $a = $accounts->first();
            $heads = AccountHead::where('supplier_id', $customer->id)->where('head_type', 'savings')->where('status', 1)->where('nature', 'Credit')->get();
            if ($heads->count() !== 1 || ($heads->first()->sister_concern_id && $heads->first()->sister_concern_id != $a->origin_sister_concern_id)) self::fail('Savings ledger is missing or ambiguous.', 'সঞ্চয় লেজার পাওয়া যায়নি অথবা একাধিক আছে।');
            $actor = User::whereKey($s->posting_user_id)->where('status', 1)->first();
            if (! $actor) self::fail('Configure the referral posting user in Transaction Settings.', 'Transaction Settings-এ রেফারেল সেটিংস সংরক্ষণ করুন।');
            $expense = self::ledger($s->expense_head_id, 'Debit', 5);
            $amount = $points * $value;
            $v = self::post($a->origin_sister_concern_id, $actor->id, self::REDEMPTION, $amount,
                'Referral points redemption / member '.$customer->id, $expense->id, $heads->first()->id);
            ShomriddhiTransaction::create(['customer_id' => $customer->id, 'type' => 'deposit', 'amount' => CashoutService::money($amount),
                'date' => $v->date, 'note' => $v->notes, 'status' => 'Approved', 'voucher_id' => $v->id,
                'created_by' => $actor->id, 'approved_by' => $actor->id, 'approved_at' => now()]);
            $a->principal_balance = CashoutService::money(CashoutService::minor($a->principal_balance) + $amount);
            $a->save();
            $id = DB::table('membership_point_redemptions')->insertGetId(['customer_id' => $customer->id, 'request_key' => $key,
                'points' => $points, 'point_value' => CashoutService::money($value), 'amount' => CashoutService::money($amount),
                'account_id' => $a->id, 'voucher_id' => $v->id, 'created_at' => now(), 'updated_at' => now()]);
            return DB::table('membership_point_redemptions')->find($id);
        }, 3);
    }

    public static function payAgent(int $rewardId, int $cashHeadId, User $admin): void
    {
        abort_unless($admin->role === 'Admin', 403);
        DB::transaction(function () use ($rewardId, $cashHeadId, $admin) {
            $r = DB::table('membership_referral_rewards')->where('id', $rewardId)->lockForUpdate()->first();
            abort_unless($r && $r->referrer_type === 'agent' && CashoutService::minor($r->amount) > 0, 404);
            if ($r->paid_at) return;
            abort_unless($r->branch_id == $admin->activeConcernId(), 403);
            if (! User::whereKey($r->referrer_id)->where('status', 1)->exists()) self::fail('Agent is inactive.', 'এজেন্ট নিষ্ক্রিয়।');
            $cash = AccountHead::whereKey($cashHeadId)->lockForUpdate()->first();
            if (! $cash || $cash->status != 1 || $cash->nature !== 'Debit' || ! in_array((int) $cash->payment_mode, [1, 2, 3])
                || ($cash->sister_concern_id && $cash->sister_concern_id != $r->branch_id) || ! $cash->isSufficient($r->amount, $r->branch_id)) self::fail('Payment ledger is invalid or has insufficient funds.', 'পেমেন্ট লেজার সঠিক নয় অথবা পর্যাপ্ত টাকা নেই।');
            // Use the payable ledger recorded at accrual, even if settings changed afterwards.
            $payable = self::ledger($r->payable_head_id, 'Credit', 2);
            $v = self::post($r->branch_id, $admin->id, self::PAYMENT, CashoutService::minor($r->amount),
                'Agent referral payment / reward '.$r->id.' / agent '.$r->referrer_id, $payable->id, $cash->id);
            DB::table('membership_referral_rewards')->where('id', $r->id)->update(['payment_voucher_id' => $v->id, 'paid_by' => $admin->id, 'paid_at' => now(), 'updated_at' => now()]);
        }, 3);
    }

    public static function ledger($id, string $nature, int $top): AccountHead
    {
        $h = AccountHead::with('accountGroup')->find($id);
        if (! $h || $h->status != 1 || $h->nature !== $nature || $h->sister_concern_id || $h->accountGroup?->top_reserved_id != $top || $h->payment_mode || $h->supplier_id || $h->employee_id) {
            throw ValidationException::withMessages(['referral' => 'Select an active global referral '.($top === 5 ? 'expense' : 'liability').' ledger in Transaction Settings.']);
        }
        return $h;
    }

    public static function post(int $branchId, int $actor, int $purpose, int $amount, string $note, int $debit, int $credit): Voucher
    {
        $branch = SisterConcern::whereKey($branchId)->where('status', 1)->lockForUpdate()->firstOrFail();
        $no = generateVoucherNo(1, 'vouchers', $branchId);
        $v = Voucher::create(['sister_concern_id' => $branchId, 'voucher_type' => 1, 'amount' => CashoutService::money($amount),
            'date' => $branch->business_date ?: now()->toDateString(), 'voucher_no' => $no,
            'voucher_no_group_sl' => (int) filter_var($no, FILTER_SANITIZE_NUMBER_INT), 'notes' => $note, 'user_id' => $actor, 'payment_purpose' => $purpose]);
        foreach ([$debit => 1, $credit => 2] as $head => $type) {
            Transaction::create(['sister_concern_id' => $branchId, 'voucher_id' => $v->id, 'voucher_type' => 1,
                'account_head_id' => $head, 'transaction_type' => $type, 'amount' => $v->amount, 'date' => $v->date,
                'voucher_no' => $no, 'voucher_no_group_sl' => $v->voucher_no_group_sl, 'notes' => $note, 'user_id' => $actor, 'payment_purpose' => $purpose]);
        }
        return $v;
    }

    private static function fail(string $en, string $bn): never { CashoutService::fail($en, $bn); }
}
