<?php

namespace App\Services\Membership;

use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\BranchManagement\SisterConcern;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class RegistrationFeeService
{
    public const PURPOSE = 30;

    // Snapshot only for customers created after deployment; never charge existing members retroactively.
    public static function enroll(Supplier $customer): void
    {
        $amount = CashoutService::minor(membership_transaction_setting('membership.registration_fee', '0.00'));
        if ($customer->type !== 'Customer' || $amount <= 0) {
            return;
        }
        DB::table('membership_registration_fees')->insertOrIgnore([
            'customer_id' => $customer->id, 'amount' => CashoutService::money($amount),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    // Call inside the funding transaction after crediting principal and posting its journal.
    public static function collect(ShomriddhiAccount $account, int $actor): void
    {
        DB::transaction(function () use ($account, $actor) {
            $account = ShomriddhiAccount::whereKey($account->id)->lockForUpdate()->firstOrFail();
            $fee = DB::table('membership_registration_fees')->where('customer_id', $account->customer_id)->lockForUpdate()->first();
            if (! $fee || $fee->paid_at || $account->status !== 'Active') {
                return;
            }
            $amount = CashoutService::minor($fee->amount);
            if (CashoutService::available($account) < $amount) {
                return;
            }
            $income = AccountHead::with('accountGroup')->find(membership_transaction_setting('membership.income_head_id'));
            if (! $income || $income->status != 1 || $income->nature !== 'Credit'
                || $income->sister_concern_id || $income->accountGroup?->top_reserved_id != 4) {
                CashoutService::fail('Configure an active registration fee income ledger in Transaction Settings.', 'Transaction Settings-এ সক্রিয় রেজিস্ট্রেশন ফি আয় লেজার নির্বাচন করুন।');
            }
            $savings = AccountHead::where('supplier_id', $account->customer_id)->where('head_type', 'savings')->where('status', 1)->first();
            if (! $savings || $savings->nature !== 'Credit') {
                CashoutService::fail('Customer savings ledger head is missing.');
            }
            $branch = SisterConcern::whereKey($account->origin_sister_concern_id)->lockForUpdate()->firstOrFail();
            $number = generateVoucherNo(1, 'vouchers', $branch->id);
            $voucher = Voucher::create([
                'sister_concern_id' => $branch->id, 'voucher_type' => 1, 'amount' => $fee->amount,
                'date' => $branch->business_date ?: now()->toDateString(), 'voucher_no' => $number,
                'voucher_no_group_sl' => (int) filter_var($number, FILTER_SANITIZE_NUMBER_INT),
                'notes' => 'Membership registration fee', 'user_id' => $actor, 'payment_purpose' => self::PURPOSE,
            ]);
            foreach ([$savings->id => 1, $income->id => 2] as $head => $type) {
                Transaction::create([
                    'sister_concern_id' => $branch->id, 'voucher_id' => $voucher->id, 'voucher_type' => 1,
                    'account_head_id' => $head, 'transaction_type' => $type, 'amount' => $fee->amount,
                    'date' => $voucher->date, 'voucher_no' => $number, 'voucher_no_group_sl' => $voucher->voucher_no_group_sl,
                    'notes' => $voucher->notes, 'user_id' => $actor, 'payment_purpose' => self::PURPOSE,
                ]);
            }
            ShomriddhiTransaction::create([
                'customer_id' => $account->customer_id, 'type' => 'withdraw', 'amount' => $fee->amount,
                'date' => $voucher->date, 'note' => $voucher->notes, 'status' => 'Approved',
                'voucher_id' => $voucher->id, 'created_by' => $actor, 'approved_by' => $actor, 'approved_at' => now(),
            ]);
            $account->principal_balance = CashoutService::money(CashoutService::minor($account->principal_balance) - $amount);
            $account->save();
            DB::table('membership_registration_fees')->where('id', $fee->id)->update([
                'voucher_id' => $voucher->id, 'paid_at' => now(), 'updated_at' => now(),
            ]);
        }, 3);
    }
}
