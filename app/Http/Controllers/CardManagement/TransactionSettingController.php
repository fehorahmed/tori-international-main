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
        $cashout = membership_transaction_settings('cashout');
        $transfer = membership_transaction_settings('transfer');
        $incomeHeads = AccountHead::with('accountGroup')->where('nature', 'Credit')->whereNull('sister_concern_id')
            ->where('status', 1)->get()->filter(fn ($head) => $head->accountGroup?->top_reserved_id == 4);

        return view('card_management.transaction_settings.index', compact('cashout', 'transfer', 'incomeHeads'));
    }

    public function update(Request $request, string $group)
    {
        abort_unless(auth()->user()->role === 'Admin', 403);
        abort_unless(in_array($group, ['cashout', 'transfer'], true), 404);
        $rules = ['maximum_amount' => 'required|numeric|decimal:0,2|min:1|max:10000000',
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
