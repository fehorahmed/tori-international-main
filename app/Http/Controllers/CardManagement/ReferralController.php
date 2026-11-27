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
        $rules = ['type' => 'nullable|in:member,agent', 'referrer_id' => 'nullable|integer|min:1', 'table' => 'nullable|in:summary,details', 'search.value' => 'nullable|string|max:100',
            'start_date' => 'nullable|date_format:Y-m-d', 'end_date' => 'nullable|date_format:Y-m-d'];
        if ($r->filled('start_date')) {
            $rules['end_date'] .= '|after_or_equal:start_date';
        }
        $r->validate($rules);
        $branch = auth()->user()->activeConcernId();
        $cashHeads = AccountHead::where('status', 1)->where('nature', 'Debit')->whereIn('payment_mode', [1, 2, 3])
            ->where(fn ($q) => $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', $branch))->get();
        if (! $r->ajax()) {
            return view('card_management.referrals.index', compact('cashHeads', 'branch'));
        }
        $query = DB::table('membership_referrals as r')
            ->leftJoin('membership_registration_fees as f', 'f.customer_id', '=', 'r.customer_id')
            ->leftJoin('membership_referral_rewards as b', 'b.referral_id', '=', 'r.id')
            ->when($r->type, fn ($q) => $q->where('r.referrer_type', $r->type))
            ->when($r->referrer_id, fn ($q) => $q->where('r.referrer_id', $r->referrer_id));
        // Use the same registration date scope for grouped summary and details.
        if ($r->filled('start_date')) {
            $query->where('r.created_at', '>=', $r->start_date.' 00:00:00');
        }
        if ($r->filled('end_date')) {
            $query->where('r.created_at', '<', \Carbon\Carbon::parse($r->end_date)->addDay()->format('Y-m-d').' 00:00:00');
        }
        $filter = function ($query) use ($r) {
            $term = trim((string) $r->input('search.value', ''));
            if ($term === '') return;
            $query->where(function ($q) use ($term) {
                $q->where('r.referral_code', 'like', '%'.$term.'%')->orWhere('r.referrer_id', $term)
                    ->orWhereExists(function ($s) use ($term) {
                        $s->selectRaw('1')->from('suppliers as m')->whereColumn('m.id', 'r.customer_id')
                            ->where(fn ($m) => $m->where('m.name', 'like', '%'.$term.'%')->orWhere('m.member_no', 'like', '%'.$term.'%')->orWhere('m.mobile_no', 'like', '%'.$term.'%'));
                    })
                    ->orWhereExists(function ($s) use ($term) {
                        $s->selectRaw('1')->from('suppliers as m')->whereColumn('m.id', 'r.referrer_id')->where('r.referrer_type', 'member')
                            ->where(fn ($m) => $m->where('m.name', 'like', '%'.$term.'%')->orWhere('m.member_no', 'like', '%'.$term.'%')->orWhere('m.mobile_no', 'like', '%'.$term.'%'));
                    })
                    ->orWhereExists(function ($s) use ($term) {
                        $s->selectRaw('1')->from('users as u')->whereColumn('u.id', 'r.referrer_id')->where('r.referrer_type', 'agent')
                            ->where(fn ($u) => $u->where('u.name', 'like', '%'.$term.'%')->orWhere('u.username', 'like', '%'.$term.'%'));
                    });
            });
        };
        if ($r->input('table') === 'summary') {
            $summary = $query->selectRaw('r.referrer_type, r.referrer_id, COUNT(*) as referrals, SUM(CASE WHEN f.paid_at IS NOT NULL THEN 1 ELSE 0 END) as fee_paid, SUM(CASE WHEN f.id IS NOT NULL AND f.paid_at IS NULL THEN 1 ELSE 0 END) as fee_pending, SUM(COALESCE(b.points, 0)) as points, SUM(COALESCE(b.amount, 0)) as commission, SUM(CASE WHEN b.paid_at IS NOT NULL THEN b.amount ELSE 0 END) as paid')->groupBy('r.referrer_type', 'r.referrer_id');
            return \Yajra\DataTables\Facades\DataTables::query($summary)->filter($filter)
                ->addColumn('referrer_label', function ($s) {
                    $referrer = $s->referrer_type === 'agent' ? User::withTrashed()->find($s->referrer_id) : Supplier::withTrashed()->find($s->referrer_id);
                    return ucfirst($s->referrer_type).' #'.$s->referrer_id.' — '.($referrer?->name ?? 'Unavailable').' / '.($s->referrer_type === 'agent' ? $referrer?->username : $referrer?->member_no);
                })
                ->addColumn('fee_label', fn ($s) => $s->fee_paid.' / '.$s->fee_pending)
                ->addColumn('points_label', function ($s) {
                    $available = $s->referrer_type === 'member' ? ReferralRewardService::points($s->referrer_id) : 0;
                    $redeemed = $s->referrer_type === 'member' ? DB::table('membership_point_redemptions')->where('customer_id', $s->referrer_id)->sum('points') : 0;
                    $earnedTotal = $s->referrer_type === 'member' ? DB::table('membership_referral_rewards')->where('referrer_type', 'member')->where('referrer_id', $s->referrer_id)->sum('points') : 0;
                    return $s->points.' / '.$redeemed.' / '.($earnedTotal - $redeemed - $available).' / '.$available;
                })
                ->addColumn('commission_label', fn ($s) => number_format($s->commission, 2).' / '.number_format($s->paid, 2).' / '.number_format($s->commission - $s->paid, 2))
                ->addColumn('milestone_label', function ($s) {
                    $q = DB::table('membership_milestone_bonuses')->where('customer_id', $s->referrer_id);
                    return $s->referrer_type === 'member' ? number_format((clone $q)->whereNotNull('paid_at')->sum('amount'), 2).' / '.number_format($q->whereNull('paid_at')->sum('amount'), 2) : '0.00 / 0.00';
                })->toJson();
        }
        $rows = $query->leftJoin('suppliers as c', 'c.id', '=', 'r.customer_id')
            ->select('r.*', 'c.name as customer_name', 'c.member_no', 'f.amount as fee_amount', 'f.paid_at as fee_paid_at',
                'b.id as reward_id', 'b.points', 'b.amount as bonus_amount', 'b.paid_at as bonus_paid_at', 'b.branch_id', 'b.voucher_id', 'b.payment_voucher_id');
        return \Yajra\DataTables\Facades\DataTables::query($rows)->filter($filter)
            ->addColumn('member_label', fn ($row) => $row->customer_name.' / '.$row->member_no)
            ->addColumn('referrer_label', fn ($row) => ucfirst($row->referrer_type).' #'.$row->referrer_id.' / '.$row->referral_code)
            ->addColumn('fee_label', fn ($row) => $row->fee_amount === null ? 'No fee obligation' : 'Tk '.$row->fee_amount.' / '.($row->fee_paid_at ? 'Paid: '.$row->fee_paid_at : 'Pending'))
            ->addColumn('reward_label', fn ($row) => ! $row->reward_id ? 'Not earned' : ($row->referrer_type === 'member' ? $row->points.' points' : 'Tk '.$row->bonus_amount))
            ->addColumn('payment', fn ($row) => view('card_management.referrals.payment', ['r' => $row, 'cashHeads' => $cashHeads, 'branch' => $branch])->render())
            ->rawColumns(['payment'])->toJson();
    }

    public function pay(Request $r, int $reward)
    {
        abort_unless(auth()->user()->role === 'Admin', 403);
        $r->validate(['cash_head_id' => 'required|integer']);
        ReferralRewardService::payAgent($reward, $r->integer('cash_head_id'), auth()->user());
        return back()->with('success', 'Agent referral commission paid.');
    }
}
