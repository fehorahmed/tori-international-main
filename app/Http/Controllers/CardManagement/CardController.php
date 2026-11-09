<?php

namespace App\Http\Controllers\CardManagement;

use App\Http\Controllers\Controller;
use App\Models\CardManagement\Card;
use App\Models\Supplier;

class CardController extends Controller
{
    public function verifyPublic($cardNumber)
    {
        $card = Card::where('card_number', $cardNumber)
            ->with(['cardable', 'sisterConcern'])
            ->first();

        if (!$card) {
            return view('card_management.verify_public', [
                'success' => false,
                'message' => 'Invalid Card Number or Card not found.'
            ]);
        }

        $isCustomer = $card->cardable_type === Supplier::class;
        
        $photoUrl = null;
        $somitiName = 'N/A';
        $memberNo = 'N/A';
        $role = 'N/A';
        $department = 'N/A';

        if ($isCustomer) {
            $member = \App\Models\Microfinance\MfMember::where('supplier_id', $card->cardable_id)->first();
            if ($member) {
                if ($member->photo) {
                    $photoUrl = asset($member->photo);
                }
                $somitiName = $member->somiti ? $member->somiti->name : 'N/A';
                $memberNo = $member->member_no;
            }
        } else {
            if ($card->cardable) {
                if ($card->cardable->photo) {
                    $photoUrl = asset($card->cardable->photo);
                }
                $role = $card->cardable->designation ? $card->cardable->designation->name : 'Staff';
                $department = $card->cardable->department ? $card->cardable->department->name : 'N/A';
            }
        }

        $viewPath = $isCustomer ? 'card_management.member.verify_public' : 'card_management.employee.verify_public';

        return view($viewPath, [
            'success' => true,
            'card' => $card,
            'isCustomer' => $isCustomer,
            'photoUrl' => $photoUrl,
            'somitiName' => $somitiName,
            'memberNo' => $memberNo,
            'role' => $role,
            'department' => $department
        ]);
    }
}
