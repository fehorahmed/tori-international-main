<?php

namespace App\Http\Controllers\CardManagement;

use App\Http\Controllers\Controller;
use App\Models\Account\AccountHead;
use App\Models\MembershipTransactionSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransactionSettingController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->role === 'Admin', 403);
        $milestones = membership_transaction_settings('referral_milestones');
        $referral = membership_transaction_settings('referral');
        $referralHeads = AccountHead::with('accountGroup')->whereNull('supplier_id')->whereNull('employee_id')->whereNull('sister_concern_id')->where('status', 1)->where(fn ($q) => $q->whereNull('payment_mode')->orWhere('payment_mode', 0))->get();
        $cashout = membership_transaction_settings('cashout');
        $transfer = membership_transaction_settings('transfer');
        $membership = membership_transaction_settings('membership');
        $membershipDiscount = membership_transaction_settings('membership_discount');
        $incomeHeads = AccountHead::with('accountGroup')->where('nature', 'Credit')->whereNull('sister_concern_id')
            ->where('status', 1)->get()->filter(fn ($head) => $head->accountGroup?->top_reserved_id == 4);

        return view('card_management.transaction_settings.index', compact('cashout', 'transfer', 'membership', 'membershipDiscount', 'incomeHeads', 'referral', 'referralHeads', 'milestones'));
    }

    public function update(Request $request, string $group)
    {
        abort_unless(auth()->user()->role === 'Admin', 403);
        abort_unless(in_array($group, ['cashout', 'transfer', 'membership', 'membership_discount', 'referral', 'referral_milestones'], true), 404);
        if ($group === 'referral_milestones') {
            $data = $request->validate([
                'enabled' => 'sometimes|boolean', 'tiers' => 'required|array|min:1|max:100',
                'tiers.*.id' => 'nullable|uuid|distinct', 'tiers.*.threshold' => 'required|integer|min:1|max:1000000|distinct',
                'tiers.*.percentage' => 'required|numeric|decimal:0,2|min:0.01|max:1000',
                'tiers.*.enabled' => 'required|boolean',
            ]);
            $old = collect(membership_transaction_settings('referral_milestones')->tiers)->keyBy('id');
            $tiers = [];
            foreach ($data['tiers'] as $tier) {
                if (! empty($tier['id']) && ! $old->has($tier['id'])) throw ValidationException::withMessages(['tiers' => 'Unknown milestone ID. Reload this page.']);
                $tiers[] = ['id' => ($tier['id'] ?? null) ?: (string) \Illuminate\Support\Str::uuid(),
                    'threshold' => (int) $tier['threshold'], 'percentage' => number_format((float) $tier['percentage'], 2, '.', ''), 'enabled' => (bool) $tier['enabled']];
            }
            // Existing IDs cannot be removed/recreated to bypass the lifetime once-only rule.
            $ids = array_column($tiers, 'id');
            foreach ($old as $id => $tier) if (! in_array($id, $ids, true)) $tiers[] = array_merge($tier, ['enabled' => false]);
            usort($tiers, fn ($a, $b) => $a['threshold'] <=> $b['threshold']);
            MembershipTransactionSetting::writeGroup($group, ['enabled' => $request->boolean('enabled'), 'tiers' => $tiers]);
            return redirect()->to(route('card-management.transaction-settings.index').'#referral-milestones')->with('success', 'Referral milestone settings saved.');
        }
        if ($group === 'referral') {
            $data = $request->validate([
                'enabled' => 'sometimes|boolean', 'member_points' => 'required|integer|min:0|max:1000000',
                'point_value' => 'required|numeric|decimal:0,2|min:0.01|max:10000',
                'agent_bonus' => 'required|numeric|decimal:0,2|min:0|max:10000000',
                'expense_head_id' => 'nullable|integer', 'payable_head_id' => 'nullable|integer',
            ]);
            $data['enabled'] = $request->boolean('enabled');
            if ($data['enabled']) {
                \App\Services\Membership\ReferralRewardService::ledger($data['expense_head_id'] ?? null, 'Debit', 5);
                if ($data['agent_bonus'] > 0) \App\Services\Membership\ReferralRewardService::ledger($data['payable_head_id'] ?? null, 'Credit', 2);
            }
            $data['posting_user_id'] = auth()->id();
            MembershipTransactionSetting::writeGroup('referral', $data);
            return redirect()->to(route('card-management.transaction-settings.index').'#referral')->with('success', 'Referral settings saved.');
        }
        if ($group === 'membership_discount') {
            $data = $request->validate(['minimum_balance' => 'required|numeric|decimal:0,2|min:0|max:10000000', 'enabled' => 'sometimes|boolean']);
            $data['enabled'] = $request->boolean('enabled');
            MembershipTransactionSetting::writeGroup($group, $data);
            return redirect()->to(route('card-management.transaction-settings.index').'#membership-discount')->with('success', 'Membership discount settings saved.');
        }
        if ($group === 'membership') {
            $data = $request->validate([
                'registration_fee' => 'required|numeric|decimal:0,2|min:0|max:10000000',
                'account_hold_amount' => 'required|numeric|decimal:0,2|min:0|max:10000000',
                'income_head_id' => 'nullable|integer',
            ]);
            $head = AccountHead::with('accountGroup')->find($data['income_head_id'] ?? null);
            if ($data['registration_fee'] > 0 && (! $head || $head->status != 1 || $head->nature !== 'Credit'
                || $head->sister_concern_id || $head->accountGroup?->top_reserved_id != 4)) {
                throw ValidationException::withMessages(['income_head_id' => 'Select an active global income ledger for membership registration fees.']);
            }
            $data['income_head_id'] = $data['income_head_id'] ?? null;
            MembershipTransactionSetting::writeGroup('membership', $data);

            return redirect()->to(route('card-management.transaction-settings.index').'#membership')->with('success', 'Membership settings saved.');
        }
        $rules = ['minimum_amount' => 'required|numeric|decimal:0,2|min:0.01|max:10000000|lte:maximum_amount',
            'maximum_amount' => 'required|numeric|decimal:0,2|min:0.01|max:10000000',
            'charge_type' => 'required|in:fixed,percentage', 'charge_rate' => 'required|numeric|decimal:0,2|min:0|max:10000000',
            'income_head_id' => 'nullable|integer', 'enabled' => 'sometimes|boolean'];
        if ($group === 'cashout') {
            $rules['hold_minutes'] = 'required|integer|min:1|max:60';
        }
        $data = $request->validate($rules);
        if ($data['charge_type'] === 'percentage' && $data['charge_rate'] > 100) {
            throw ValidationException::withMessages(['charge_rate' => 'Percentage cannot exceed 100.']);
        }
        if ($data['charge_rate'] > 0) {
            $head = AccountHead::find($data['income_head_id'] ?? null);
            if (! $head || $head->status != 1 || $head->nature !== 'Credit' || $head->sister_concern_id || $head->accountGroup?->top_reserved_id != 4) {
                throw ValidationException::withMessages(['income_head_id' => 'Select an active global income ledger for transaction charges.']);
            }
        }
        $data['enabled'] = $request->boolean('enabled');
        $data['income_head_id'] = $data['income_head_id'] ?? null;
        if ($group === 'transfer') {
            $data['posting_user_id'] = auth()->id();
        }
        MembershipTransactionSetting::writeGroup($group, $data);

        return redirect()->to(route('card-management.transaction-settings.index').'#'.$group)->with('success', 'Transaction settings saved.');
    }
}
