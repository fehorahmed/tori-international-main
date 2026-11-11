<?php

namespace App\Http\Controllers\API\Membership;

use App\Http\Controllers\Controller;
use App\Models\MembershipTransfer;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Services\Membership\CashoutService;
use App\Services\Membership\TransferService;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function index(Request $r)
    {
        $c = KycController::customer($r);
        $s = membership_transaction_settings('transfer');
        $a = ShomriddhiAccount::where('customer_id', $c->id)->where('status', 'Active')->first();

        return response()->json(['success' => true, 'data' => ['customer_id' => $c->id, 'enabled' => $s->enabled, 'maximum_amount' => $s->maximum_amount, 'charge_type' => $s->charge_type, 'charge_rate' => $s->charge_rate, 'available_balance' => CashoutService::money($a ? CashoutService::available($a) : 0), 'transfers' => MembershipTransfer::with(['sender', 'recipient'])->where(fn ($q) => $q->where('sender_id', $c->id)->orWhere('recipient_id', $c->id))->latest('id')->limit(30)->get()->map(fn ($t) => self::data($t, $c->id))]]);
    }

    public static function data(MembershipTransfer $t, int $viewer): array
    {
        $out = $t->sender_id == $viewer;

        return ['id' => $t->id, 'reference' => $t->reference, 'request_key' => $out ? $t->idempotency_key : null, 'direction' => $out ? 'sent' : 'received', 'name' => $out ? $t->recipient?->name : $t->sender?->name, 'amount' => $t->amount, 'fee' => $out ? $t->fee : '0.00', 'total' => $out ? $t->total : $t->amount, 'status' => 'Completed', 'created_at' => $t->created_at->toIso8601String()];
    }

    public function quote(Request $r, TransferService $s)
    {
        $c = KycController::customer($r);
        $r->validate(['recipient' => 'required|string|max:64', 'amount' => 'required|numeric|decimal:0,2|min:1|max:10000000']);

        return response()->json(['success' => true, 'data' => $s->quote($c, $r->recipient, (string) $r->amount)]);
    }

    public function store(Request $r, TransferService $s)
    {
        $c = KycController::customer($r);
        $r->validate(['quote' => 'required|string|max:8192', 'pin' => 'required|digits:6', 'idempotency_key' => 'required|uuid']);

        return response()->json(['success' => true, 'data' => self::data($s->send($c, $r->quote, $r->pin, $r->idempotency_key), $c->id)], 201);
    }
}
