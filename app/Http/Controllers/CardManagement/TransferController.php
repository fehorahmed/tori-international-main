<?php

namespace App\Http\Controllers\CardManagement;

use App\Http\Controllers\Controller;
use App\Models\Account\AccountHead;
use App\Models\Account\Voucher;
use App\Models\MembershipTransfer;
use App\Models\MembershipTransferSetting;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->role === 'Admin' || auth()->user()->can('shomriddhi_advance_transaction_approve'), 403);
        $branch = auth()->user()->activeConcernId();
        $transfers = MembershipTransfer::with(['sender', 'recipient'])->where(function ($q) use ($branch) {
            $q->whereIn('voucher_id', Voucher::where('sister_concern_id', $branch)->select('id'))->orWhereIn('recipient_voucher_id', Voucher::where('sister_concern_id', $branch)->select('id'));
        })->latest('id')->paginate(25);

        return view('card_management.transfers.index', compact('transfers'));
    }

    public function settings()
    {
        abort_unless(auth()->user()->role === 'Admin', 403);
        $setting = MembershipTransferSetting::current();
        $incomeHeads = AccountHead::with('accountGroup')->where('nature', 'Credit')->whereNull('sister_concern_id')->where('status', 1)->get()->filter(fn ($h) => $h->accountGroup?->top_reserved_id == 4);

        return view('card_management.transfers.settings', compact('setting', 'incomeHeads'));
    }

    public function update(Request $r)
    {
        abort_unless(auth()->user()->role === 'Admin', 403);
        $d = $r->validate(['maximum_amount' => 'required|numeric|decimal:0,2|min:1|max:10000000', 'charge_type' => 'required|in:fixed,percentage', 'charge_rate' => 'required|numeric|decimal:0,2|min:0|max:10000000', 'income_head_id' => 'nullable|integer']);
        if ($d['charge_type'] === 'percentage' && $d['charge_rate'] > 100) {
            return back()->withErrors(['charge_rate' => 'Percentage cannot exceed 100.'])->withInput();
        }
        if ($d['charge_rate'] > 0) {
            $h = AccountHead::find($d['income_head_id'] ?? null);
            if (! $h || $h->status != 1 || $h->nature !== 'Credit' || $h->sister_concern_id || $h->accountGroup?->top_reserved_id != 4) {
                return back()->withErrors(['income_head_id' => 'Select an active global income ledger.'])->withInput();
            }
        }
        MembershipTransferSetting::updateOrCreate(['id' => 1], $d + ['enabled' => $r->boolean('enabled'), 'posting_user_id' => auth()->id()]);

        return back()->with('success', 'Send Money settings saved.');
    }
}
