<?php

namespace App\Http\Controllers\API\Membership;

use App\Http\Controllers\Controller;
use App\Models\MembershipCashout;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Services\Membership\CashoutService;
use Illuminate\Http\Request;

class CashoutController extends Controller
{
    public function index(Request $request)
    {
        $customer = KycController::customer($request);
        $setting = membership_transaction_settings('cashout');
        $account = ShomriddhiAccount::where('customer_id', $customer->id)->where('status', 'Active')->first();

        return response()->json(['success' => true, 'data' => [
            'customer_id' => $customer->id,
            'enabled' => (bool) $setting->enabled, 'minimum_amount' => $setting->minimum_amount, 'maximum_amount' => $setting->maximum_amount ?? '0.00',
            'charge_type' => $setting->charge_type ?? 'fixed', 'charge_rate' => $setting->charge_rate ?? '0.00',
            'balance' => $account?->principal_balance ?? '0.00',
            'minimum_balance' => CashoutService::money(CashoutService::minimumBalance()),
            'held_balance' => CashoutService::money($account ? MembershipCashout::held($account->id) : 0),
            'available_balance' => CashoutService::money($account ? CashoutService::available($account) : 0),
            'requests' => MembershipCashout::with('branch')->where('customer_id', $customer->id)->latest('id')->limit(20)->get()->map(fn ($row) => self::data($row)),
        ]]);
    }

    public static function data(MembershipCashout $cashout): array
    {
        return ['id' => $cashout->id, 'reference' => $cashout->reference,
            'request_key' => $cashout->idempotency_key,
            'branch' => $cashout->branch?->name, 'status' => $cashout->effectiveStatus(),
            'amount' => $cashout->amount, 'fee' => $cashout->fee, 'total' => $cashout->total,
            'expires_at' => $cashout->expires_at->toIso8601String(),
            'completed_at' => $cashout->completed_at?->toIso8601String(), 'reason' => $cashout->reason];
    }

    public function quote(Request $request, CashoutService $service)
    {
        $customer = KycController::customer($request);
        $request->validate(['qr_code' => 'required|string|max:256', 'amount' => 'required|numeric|decimal:0,2|min:0.01|max:10000000']);

        return response()->json(['success' => true, 'data' => $service->quote($customer, $request->qr_code, (string) $request->amount)]);
    }

    public function store(Request $request, CashoutService $service)
    {
        $customer = KycController::customer($request);
        $request->validate(['quote' => 'required|string|max:8192', 'pin' => 'required|digits:6', 'idempotency_key' => 'required|uuid']);
        $cashout = $service->request($customer, $request->quote, $request->pin, $request->idempotency_key);

        return response()->json(['success' => true, 'data' => self::data($cashout)], 201);
    }

    public function show(Request $request, MembershipCashout $cashout)
    {
        abort_unless($cashout->customer_id == KycController::customer($request)->id, 403);

        return response()->json(['success' => true, 'data' => self::data($cashout)]);
    }

    public function cancel(Request $request, MembershipCashout $cashout, CashoutService $service)
    {
        abort_unless($cashout->customer_id == KycController::customer($request)->id, 403);

        return response()->json(['success' => true, 'data' => self::data($service->change($cashout, 'cancel'))]);
    }
}
