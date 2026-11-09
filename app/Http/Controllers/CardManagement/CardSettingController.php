<?php

namespace App\Http\Controllers\CardManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CardManagement\CardSetting;
use App\Models\CardManagement\Card;

class CardSettingController extends Controller
{
    public function index()
    {
        $setting = CardSetting::first();
        if (!$setting) {
            $setting = CardSetting::create();
        }
        $pendingCardsCount = Card::where('payment_status', 'Pending')->count();
        $pageTitle = 'Card Distribution Settings';
        return view('card_management.settings.index', compact('setting', 'pageTitle', 'pendingCardsCount'));
    }

    public function update(Request $request)
    {
        $pendingCardsCount = Card::where('payment_status', 'Pending')->count();
        if ($pendingCardsCount > 0) {
            return back()->with('error', 'Cannot update settings while there are pending card payments.');
        }

        $request->validate([
            'card_fee' => 'required|numeric|min:0',
            'first_card_agent_commission' => 'required|numeric|min:0',
            'first_card_member_bonus' => 'required|numeric|min:0',
            'first_card_company_income' => 'required|numeric|min:0',
            'replacement_card_agent_commission' => 'required|numeric|min:0',
            'replacement_card_member_bonus' => 'required|numeric|min:0',
            'replacement_card_company_income' => 'required|numeric|min:0',
        ]);

        $fee = $request->card_fee;

        $firstSum = $request->first_card_agent_commission + $request->first_card_member_bonus + $request->first_card_company_income;
        if (abs($firstSum - $fee) > 0.01) {
            return back()->with('error', 'First Card distribution total (' . $firstSum . ') must equal Card Fee (' . $fee . ')');
        }

        $repSum = $request->replacement_card_agent_commission + $request->replacement_card_member_bonus + $request->replacement_card_company_income;
        if (abs($repSum - $fee) > 0.01) {
            return back()->with('error', 'Replacement Card distribution total (' . $repSum . ') must equal Card Fee (' . $fee . ')');
        }

        $setting = CardSetting::first();
        $setting->update($request->all());

        return back()->with('success', 'Card Settings updated successfully.');
    }
}
