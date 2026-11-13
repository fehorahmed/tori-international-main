<?php

namespace App\Http\Controllers\API\Membership;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function show(Request $request)
    {
        $customer = KycController::customer($request);
        return response()->json(['success' => true, 'data' => [
            'card' => app(\App\Services\Membership\CardProfileService::class)->card($customer),
        ]]);
    }
}
