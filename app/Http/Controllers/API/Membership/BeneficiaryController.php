<?php

namespace App\Http\Controllers\API\Membership;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\Membership\CashoutService;
use App\Services\Membership\RecipientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BeneficiaryController extends Controller
{
    public function index(Request $r)
    {
        $c = KycController::customer($r);
        $rows = DB::table('membership_beneficiaries')->where('customer_id', $c->id)->orderByDesc('id')->get();
        $recipients = Supplier::whereIn('id', $rows->pluck('recipient_id'))->get()->keyBy('id');

        return response()->json(['success' => true, 'data' => ['limit' => 10, 'items' => $rows->map(function ($row) use ($recipients) {
            $recipient = $recipients->get($row->recipient_id);

            return ['id' => $row->id, 'nickname' => $row->nickname] + ($recipient ? RecipientService::data($recipient) :
                ['recipient_id' => $row->recipient_id, 'name' => 'Unavailable member', 'member_no' => null, 'active' => false, 'image' => null]);
        })]]);
    }

    public function lookup(Request $r)
    {
        $c = KycController::customer($r);
        $r->validate(['recipient' => 'required|string|max:64']);

        return response()->json(['success' => true, 'data' => RecipientService::data(RecipientService::resolve($c, $r->recipient))]);
    }

    public function store(Request $r)
    {
        $c = KycController::customer($r);
        $data = $r->validate(['recipient_id' => 'required|integer|min:1', 'nickname' => 'nullable|string|max:60']);

        return DB::transaction(function () use ($c, $data) {
            Supplier::whereKey($c->id)->lockForUpdate()->firstOrFail();
            $recipient = RecipientService::resolve($c, '', $data['recipient_id']);
            $old = DB::table('membership_beneficiaries')->where('customer_id', $c->id)->where('recipient_id', $recipient->id)->first();
            if ($old) {
                return response()->json(['success' => true, 'data' => ['id' => $old->id], 'message' => 'Already saved in Quick Send.']);
            }
            if (DB::table('membership_beneficiaries')->where('customer_id', $c->id)->count() >= 10) {
                CashoutService::fail('You can save up to 10 Quick Send recipients.', 'সর্বোচ্চ ১০ জন প্রাপক রাখা যাবে।');
            }
            $id = DB::table('membership_beneficiaries')->insertGetId(['customer_id' => $c->id, 'recipient_id' => $recipient->id,
                'nickname' => trim($data['nickname'] ?? '') ?: null, 'created_at' => now(), 'updated_at' => now()]);

            return response()->json(['success' => true, 'data' => ['id' => $id]], 201);
        });
    }

    public function update(Request $r, int $beneficiary)
    {
        $c = KycController::customer($r);
        $data = $r->validate(['nickname' => 'nullable|string|max:60']);
        $row = DB::table('membership_beneficiaries')->where('customer_id', $c->id)->where('id', $beneficiary)->first();
        abort_unless($row, 404);
        DB::table('membership_beneficiaries')->where('id', $row->id)->update(['nickname' => trim($data['nickname'] ?? '') ?: null, 'updated_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $r, int $beneficiary)
    {
        $c = KycController::customer($r);
        abort_unless(DB::table('membership_beneficiaries')->where('customer_id', $c->id)->where('id', $beneficiary)->delete(), 404);

        return response()->json(['success' => true]);
    }
}
