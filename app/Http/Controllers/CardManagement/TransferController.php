<?php

namespace App\Http\Controllers\CardManagement;

use App\Http\Controllers\Controller;
use App\Models\Account\Voucher;
use App\Models\MembershipTransfer;

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
}
