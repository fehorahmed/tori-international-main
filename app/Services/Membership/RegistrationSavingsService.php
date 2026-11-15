<?php

namespace App\Services\Membership;

use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\BranchManagement\SisterConcern;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class RegistrationSavingsService
{
    // Only registration and the targeted registration repair command may provision an empty account.
    public static function ensure(Supplier $member): ShomriddhiAccount
    {
        return DB::transaction(function () use ($member) {
            $branch = SisterConcern::whereKey($member->origin_sister_concern_id)->where('status', 1)->lockForUpdate()->firstOrFail();
            $member = Supplier::whereKey($member->id)->where('type', 'Customer')->where('status', 1)->lockForUpdate()->firstOrFail();
            $accounts = ShomriddhiAccount::withTrashed()->where('customer_id', $member->id)->lockForUpdate()->get();
            if ($accounts->count() > 1 || ($accounts->count() === 1 && ($accounts->first()->trashed() || $accounts->first()->status !== 'Active'
                || $accounts->first()->origin_sister_concern_id != $branch->id))) {
                CashoutService::fail('Existing savings account requires branch review; it cannot be recreated or reactivated automatically.');
            }
            $group = AccountGroup::where('reserved_id', 28)->lockForUpdate()->firstOrFail();
            $heads = AccountHead::withTrashed()->where('supplier_id', $member->id)->where('head_type', 'savings')->lockForUpdate()->get();
            if ($heads->count() > 1 || ($heads->count() === 1 && ($heads->first()->trashed() || $heads->first()->status != 1
                || $heads->first()->nature !== 'Credit' || $heads->first()->account_group_id != $group->id
                || ($heads->first()->sister_concern_id && $heads->first()->sister_concern_id != $branch->id)))) {
                CashoutService::fail('Existing savings ledger requires branch review.');
            }
            if ($heads->isEmpty()) {
                if ($accounts->isNotEmpty() && (CashoutService::minor($accounts->first()->principal_balance) !== 0
                    || (float) $accounts->first()->accrued_interest !== 0)) CashoutService::fail('Funded savings account is missing its ledger; branch reconciliation is required.');
                AccountHead::create(['sister_concern_id' => null, 'code' => generateAccountCode(2),
                    'account_group_id' => $group->id, 'supplier_id' => $member->id,
                    'name' => $member->name.' (Savings A/C)', 'nature' => 'Credit', 'head_type' => 'savings',
                    'status' => 1, 'is_system' => 1, 'is_editable' => 0, 'is_control_account' => 0]);
            }
            if ($accounts->isNotEmpty()) return $accounts->first();
            // Never attach an empty account to a ledger with historical postings.
            if ($heads->isNotEmpty() && $heads->first()->transactions()->exists()) CashoutService::fail('Existing savings postings require branch reconciliation.');
            $last = ShomriddhiAccount::withTrashed()->where('origin_sister_concern_id', $branch->id)->whereNotNull('account_number')->latest('id')->first();
            $serial = $last ? (int) substr($last->account_number, -6) + 1 : 1;
            do {
                if ($serial > 999999) CashoutService::fail('Savings account number range is exhausted.');
                $number = str_pad((string) $branch->code, 3, '0', STR_PAD_LEFT).str_pad((string) $serial++, 6, '0', STR_PAD_LEFT);
            } while (ShomriddhiAccount::withTrashed()->where('account_number', $number)->exists());
            return ShomriddhiAccount::create(['account_number' => $number, 'customer_id' => $member->id,
                'origin_sister_concern_id' => $branch->id, 'opening_date' => $branch->business_date ?: now()->toDateString(),
                'principal_balance' => '0.00', 'accrued_interest' => 0, 'status' => 'Active']);
        }, 3);
    }
}
