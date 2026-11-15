<?php
namespace App\Services\Membership;

use App\Models\Supplier;
use App\Models\User;
use App\Models\Account\AccountHead;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use Illuminate\Support\Facades\DB;

class MilestoneBonusService
{
    public const PURPOSE = 37;

    public static function collect(int $customerId): void
    {
        DB::transaction(function () use ($customerId) {
            $member = Supplier::whereKey($customerId)->where('type', 'Customer')->where('status', 1)->lockForUpdate()->first();
            if (! $member) return;
            $config = membership_transaction_settings('referral_milestones');
            $referral = membership_transaction_settings('referral');
            if (! $config->enabled || ! $referral->enabled) return;
            $points = ReferralRewardService::points($customerId, true);
            foreach ($config->tiers as $tier) {
                if (! $tier['enabled'] || $points < $tier['threshold']) continue;
                $value = CashoutService::minor($referral->point_value);
                $percent = CashoutService::minor($tier['percentage']);
                // Calculate in paisa, round once at the final monetary amount.
                $amount = intdiv((int) $tier['threshold'] * $percent * $value + 5000, 10000);
                if ($amount <= 0) continue;
                DB::table('membership_milestone_bonuses')->insertOrIgnore([
                    'customer_id' => $customerId, 'tier_id' => $tier['id'], 'threshold' => $tier['threshold'],
                    'qualifying_points' => $points, 'percentage' => $tier['percentage'],
                    'point_value' => $referral->point_value, 'amount' => CashoutService::money($amount),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            $pending = DB::table('membership_milestone_bonuses')->where('customer_id', $customerId)->whereNull('paid_at')->orderBy('id')->lockForUpdate()->get();
            if ($pending->isEmpty()) return;
            $accounts = ShomriddhiAccount::where('customer_id', $customerId)->where('status', 'Active')->lockForUpdate()->get();
            // Preserve an earned bonus pending rather than fail someone else's incoming transfer.
            if ($accounts->count() !== 1) return;
            $a = $accounts->first();
            if (! \App\Models\BranchManagement\SisterConcern::whereKey($a->origin_sister_concern_id)->where('status', 1)->exists()) return;
            $heads = AccountHead::where('supplier_id', $customerId)->where('head_type', 'savings')->where('status', 1)->where('nature', 'Credit')->get();
            if ($heads->count() !== 1 || ($heads->first()->sister_concern_id && $heads->first()->sister_concern_id != $a->origin_sister_concern_id)) return;
            $actor = User::whereKey($referral->posting_user_id)->where('status', 1)->first();
            if (! $actor) return;
            try { $expense = ReferralRewardService::ledger($referral->expense_head_id, 'Debit', 5); }
            catch (\Illuminate\Validation\ValidationException) { return; }
            foreach ($pending as $bonus) {
                $amount = CashoutService::minor($bonus->amount);
                $v = ReferralRewardService::post($a->origin_sister_concern_id, $actor->id, self::PURPOSE, $amount,
                    'Referral milestone bonus / '.$bonus->threshold.' points / bonus '.$bonus->id, $expense->id, $heads->first()->id);
                ShomriddhiTransaction::create(['customer_id' => $customerId, 'type' => 'deposit', 'amount' => $bonus->amount,
                    'date' => $v->date, 'note' => $v->notes, 'status' => 'Approved', 'voucher_id' => $v->id,
                    'created_by' => $actor->id, 'approved_by' => $actor->id, 'approved_at' => now()]);
                $a->principal_balance = CashoutService::money(CashoutService::minor($a->principal_balance) + $amount);
                $a->save();
                DB::table('membership_milestone_bonuses')->where('id', $bonus->id)->update([
                    'account_id' => $a->id, 'voucher_id' => $v->id, 'paid_at' => now(), 'updated_at' => now(),
                ]);
            }
        }, 3);
    }

    public static function summary(int $customerId): array
    {
        $s = membership_transaction_settings('referral_milestones');
        return ['enabled' => (bool) $s->enabled, 'tiers' => $s->tiers,
            'bonuses' => DB::table('membership_milestone_bonuses')->where('customer_id', $customerId)->orderBy('threshold')->get([
                'tier_id', 'threshold', 'qualifying_points', 'percentage', 'point_value', 'amount', 'paid_at', 'created_at',
            ])];
    }
}
