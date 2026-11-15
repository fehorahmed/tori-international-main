<?php

namespace App\Http\Controllers\API\Membership;

use App\Http\Controllers\Controller;
use App\Services\Membership\ReferralRewardService;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function index(Request $r)
    {
        $c = KycController::customer($r);
        return response()->json(['success' => true, 'data' => ReferralRewardService::summary($c->id)]);
    }

    public function collectMilestones(Request $r)
    {
        $c = KycController::customer($r);
        \App\Services\Membership\MilestoneBonusService::collect($c->id);
        return response()->json(['success' => true, 'data' => ReferralRewardService::summary($c->id)]);
    }

    public function redeem(Request $r)
    {
        $c = KycController::customer($r);
        $r->validate(['points' => 'required|integer|min:1|max:1000000', 'point_value' => 'required|numeric|decimal:0,2|min:0.01|max:10000',
            'pin' => 'required|digits:6', 'request_key' => 'required|uuid']);
        $row = ReferralRewardService::redeem($c, (int) $r->points, (string) $r->point_value, $r->pin, $r->request_key);
        return response()->json(['success' => true, 'data' => ['points' => $row->points, 'point_value' => $row->point_value, 'amount' => $row->amount, 'request_key' => $row->request_key]], 201);
    }
}
