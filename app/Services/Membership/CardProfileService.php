<?php

namespace App\Services\Membership;

use App\Models\BranchManagement\SisterConcern;
use App\Models\Supplier;

class CardProfileService
{
    public function card(Supplier $customer): ?array
    {
        $card = $customer->cards()->orderByRaw("CASE WHEN status = 'Active' THEN 0 ELSE 1 END")
            ->orderByDesc('id')->first();

        return $card ? [
            'card_number' => $card->card_number,
            'status' => $card->status,
            'holder_name' => $customer->name,
            'member_no' => $customer->member_no,
            'branch' => SisterConcern::find($card->origin_sister_concern_id)?->name,
            'issued_at' => $card->created_at?->toDateString(),
            'fee' => (float) $card->amount,
            'fee_status' => $card->payment_status,
            'printed_at' => $card->logs()->latest('printed_at')->value('printed_at'),
        ] : null;
    }
}
