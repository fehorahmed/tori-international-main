<?php

namespace App\Http\Controllers\API\Membership;

use App\Http\Controllers\Controller;
use App\Models\MembershipAgent;
use App\Models\MembershipTransfer;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Services\Membership\AgentTransactionService;
use App\Services\Membership\CashoutService;
use App\Services\Membership\KycEligibilityService;
use App\Services\Membership\TransactionWindowService;
use Illuminate\Http\Request;

class AgentTransactionController extends Controller
{
    public function index(Request $r, string $kind)
    {
        $c = KycController::customer($r);
        $s = membership_transaction_settings($kind);
        $agent = MembershipAgent::data($c->id);
        $window = TransactionWindowService::status();
        $account = ShomriddhiAccount::where('customer_id', $c->id)->where('status', 'Active')->get();

        return response()->json(['success' => true, 'data' => ['customer_id' => $c->id,
            'kyc_approved' => KycEligibilityService::status($c) === 'Verified', 'agent' => $agent,
            'enabled' => $s->enabled && ($kind !== 'agent_cashin' || ($agent && $agent['status'] === 'Active' && ! $window['blocked'])),
            'transaction_window' => $kind === 'agent_cashin' ? $window : null,
            'minimum_amount' => $s->minimum_amount, 'maximum_amount' => $s->maximum_amount,
            'charge_type' => $s->charge_type, 'charge_rate' => $s->charge_rate,
            'available_balance' => $account->count() === 1 ? CashoutService::money(CashoutService::available($account->first())) : '0.00',
            'transfers' => MembershipTransfer::with(['sender', 'recipient'])->where('channel', $kind)
                ->where(fn ($q) => $q->where('sender_id', $c->id)->orWhere('recipient_id', $c->id))->latest('id')->limit(30)->get()
                ->map(fn ($t) => TransferController::data($t, $c->id))]]);
    }

    public function quote(Request $r, string $kind, AgentTransactionService $s)
    {
        $c = KycController::customer($r);
        $d = $r->validate(['recipient' => 'required_without:recipient_id|nullable|string|max:64', 'recipient_id' => 'nullable|integer|min:1', 'amount' => 'required|numeric|decimal:0,2|min:0.01|max:10000000']);

        return response()->json(['success' => true, 'data' => $s->quote($c, $kind, (string) ($d['recipient'] ?? ''), (string) $d['amount'], isset($d['recipient_id']) ? (int) $d['recipient_id'] : null)]);
    }

    public function store(Request $r, string $kind, AgentTransactionService $s)
    {
        $c = KycController::customer($r);
        $d = $r->validate(['quote' => 'required|string|max:8192', 'pin' => 'required|digits:6', 'idempotency_key' => 'required|uuid']);
        $record = $s->complete($c, $kind, $d['quote'], $d['pin'], $d['idempotency_key']);
        $data = TransferController::data($record->transfer->load(['sender', 'recipient']), $c->id);
        $data['commission'] = $record->agent_commission;

        return response()->json(['success' => true, 'data' => $data], 201);
    }
}
