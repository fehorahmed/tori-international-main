<?php

namespace App\Http\Controllers\API\Membership;

use App\Http\Controllers\Controller;
use App\Models\BranchManagement\SisterConcern;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function show(Request $request)
    {
        $customer = KycController::customer($request);
        $card = $customer->cards()->orderByRaw("CASE WHEN status = 'Active' THEN 0 ELSE 1 END")
            ->orderByDesc('id')->first();

        return response()->json(['success' => true, 'data' => ['card' => $card ? [
            'card_number' => $card->card_number,
            'status' => $card->status,
            'holder_name' => $customer->name,
            'member_no' => $customer->member_no,
            'branch' => SisterConcern::find($card->origin_sister_concern_id)?->name,
            'issued_at' => $card->created_at?->toDateString(),
            'fee' => (float) $card->amount,
            'fee_status' => $card->payment_status,
            'printed_at' => $card->logs()->latest('printed_at')->value('printed_at'),
        ] : null]]);
    }
}
