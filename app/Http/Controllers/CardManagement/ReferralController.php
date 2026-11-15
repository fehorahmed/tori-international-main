<?php

namespace App\Http\Controllers\CardManagement;

use App\Http\Controllers\Controller;
use App\Models\Account\AccountHead;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Membership\ReferralRewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    public function index(Request $r)
    {
        abort_unless(auth()->user()->role === 'Admin', 403);
        $r->validate(['type' => 'nullable|in:member,agent', 'referrer_id' => 'nullable|integer|min:1']);
        $query = DB::table('membership_referrals as r')
            ->leftJoin('membership_registration_fees as f', 'f.customer_id', '=', 'r.customer_id')
            ->leftJoin('membership_referral_rewards as b', 'b.referral_id', '=', 'r.id')
            ->when($r->type, fn ($q) => $q->where('r.referrer_type', $r->type))
            ->when($r->referrer_id, fn ($q) => $q->where('r.referrer_id', $r->referrer_id));
        $summary = (clone $query)->selectRaw('r.referrer_type, r.referrer_id, COUNT(*) as referrals, SUM(CASE WHEN f.paid_at IS NOT NULL THEN 1 ELSE 0 END) as fee_paid, SUM(CASE WHEN f.id IS NOT NULL AND f.paid_at IS NULL THEN 1 ELSE 0 END) as fee_pending, SUM(COALESCE(b.points, 0)) as points, SUM(COALESCE(b.amount, 0)) as commission, SUM(CASE WHEN b.paid_at IS NOT NULL THEN b.amount ELSE 0 END) as paid')->groupBy('r.referrer_type', 'r.referrer_id')->paginate(25, ['*'], 'summary_page');
        foreach ($summary as $row) {
            $row->referrer = $row->referrer_type === 'agent' ? User::withTrashed()->find($row->referrer_id) : Supplier::withTrashed()->find($row->referrer_id);
            $row->milestone_paid = $row->referrer_type === 'member' ? DB::table('membership_milestone_bonuses')->where('customer_id', $row->referrer_id)->whereNotNull('paid_at')->sum('amount') : 0;
            $row->milestone_pending = $row->referrer_type === 'member' ? DB::table('membership_milestone_bonuses')->where('customer_id', $row->referrer_id)->whereNull('paid_at')->sum('amount') : 0;
            $row->redeemed_points = $row->referrer_type === 'member' ? DB::table('membership_point_redemptions')->where('customer_id', $row->referrer_id)->sum('points') : 0;
        }
        $rows = (clone $query)->leftJoin('suppliers as c', 'c.id', '=', 'r.customer_id')
            ->select('r.*', 'c.name as customer_name', 'c.member_no', 'f.amount as fee_amount', 'f.paid_at as fee_paid_at',
                'b.id as reward_id', 'b.points', 'b.amount as bonus_amount', 'b.paid_at as bonus_paid_at', 'b.branch_id', 'b.voucher_id', 'b.payment_voucher_id')
            ->latest('r.id')->paginate(30, ['*'], 'detail_page');
        $branch = auth()->user()->activeConcernId();
        $cashHeads = AccountHead::where('status', 1)->where('nature', 'Debit')->whereIn('payment_mode', [1, 2, 3])
            ->where(fn ($q) => $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', $branch))->get();
        return view('card_management.referrals.index', compact('rows', 'summary', 'cashHeads', 'branch'));
    }

    public function pay(Request $r, int $reward)
    {
        abort_unless(auth()->user()->role === 'Admin', 403);
        $r->validate(['cash_head_id' => 'required|integer']);
        ReferralRewardService::payAgent($reward, $r->integer('cash_head_id'), auth()->user());
        return back()->with('success', 'Agent referral commission paid.');
    }
}
