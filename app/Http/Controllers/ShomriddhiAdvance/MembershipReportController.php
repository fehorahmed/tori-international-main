<?php

namespace App\Http\Controllers\ShomriddhiAdvance;

use App\Http\Controllers\Controller;
use App\Models\BranchManagement\SisterConcern;
use App\Models\MembershipAgent;
use App\Models\ShomriddhiAdvance\ShomriddhiGroup;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Reports\MembershipReports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MembershipReportController extends Controller
{
    public function members(Request $request) { return $this->report($request, 'members'); }
    public function agents(Request $request) { return $this->report($request, 'agents'); }
    public function referrals(Request $request) { return $this->report($request, 'referrals'); }

    public function deposit_withdraw(Request $request) { return $this->report($request, 'deposit_withdraw'); }

    private function report(Request $request, string $kind)
    {
        // Report permission is applied at the route. Agent and referral financial
        // reports additionally retain the corresponding admin-list restrictions.
        if (in_array($kind, ['agents', 'referrals'], true)) abort_unless(auth()->user()->role === 'Admin', 403);
        $branchId = \App\Support\ReportBranch::id();
        $branch = $branchId === null ? new SisterConcern(['name' => 'All Branches']) : SisterConcern::findOrFail($branchId);
        if ($kind === 'deposit_withdraw' && ! $request->has('status')) $request->merge(['status' => 'Approved']);
        $rules = ['start_date' => 'nullable|date_format:Y-m-d', 'end_date' => 'nullable|date_format:Y-m-d'];
        if ($request->filled('start_date')) $rules['end_date'] .= '|after_or_equal:start_date';
        $rules += match ($kind) {
            'deposit_withdraw' => ['customer_id' => 'nullable|integer|min:1', 'type' => 'nullable|in:deposit,withdraw', 'status' => 'nullable|in:Approved,Pending,Rejected', 'source' => 'nullable|in:savings,branch_cashout'],
            'members' => ['customer_id' => 'nullable|integer|min:1', 'group_id' => 'nullable|integer|min:1', 'status' => 'nullable|in:0,1', 'source' => 'nullable|in:app,ecommerce,unknown'],
            'agents' => ['agent_id' => 'nullable|integer|min:1', 'status' => 'nullable|in:Active,Suspended,Removed'],
            'referrals' => ['referrer_type' => 'nullable|in:member,agent', 'referrer_key' => ['nullable', 'regex:/^(member|agent):[1-9][0-9]*$/'],
                'bonus_kind' => 'nullable|in:referral,milestone', 'payment_status' => 'nullable|in:paid,pending'],
        };
        $filters = $request->validate($rules);
        if (\App\Support\ReportBranch::allowed()) $filters['report_branch_id'] = $branchId ?? 'all';
        $title = ['members' => 'Member Report', 'agents' => 'Agent Report', 'referrals' => 'Referral Bonus Report', 'deposit_withdraw' => 'Deposit / Withdraw Report'][$kind];
        $dateLabel = ['members' => 'Joining Date', 'agents' => 'Transaction Date', 'referrals' => 'Bonus Earned Date', 'deposit_withdraw' => 'Transaction Date'][$kind];
        $options = [];
        if ($kind === 'members') {
            $options = [
                'customer_id' => ['Member', Supplier::where('type', 'Customer')->whereReportBranch('origin_sister_concern_id', $branchId)->orderBy('name')->get()->mapWithKeys(fn ($m) => [$m->id => implode(' / ', array_filter([$m->name, $m->member_no, $m->mobile_no]))])->all()],
                'group_id' => ['Group', ShomriddhiGroup::orderBy('code')->get()->mapWithKeys(fn ($g) => [$g->id => $g->code.' — '.$g->name_en])->all()],
                'status' => ['Status', ['1' => 'Active', '0' => 'Inactive']],
                'source' => ['Registration Source', ['app' => 'App', 'ecommerce' => 'Ecommerce', 'unknown' => 'Not recorded']],
            ];
        } elseif ($kind === 'agents') {
            $options = [
                'agent_id' => ['Agent', MembershipAgent::with('customer')->whereHas('customer', fn ($q) => $q->whereReportBranch('origin_sister_concern_id', $branchId))->orderBy('agent_code')->get()
                    ->mapWithKeys(fn ($a) => [$a->id => implode(' / ', array_filter([$a->customer?->name, $a->agent_code, $a->customer?->mobile_no]))])->all()],
                'status' => ['Current Agent Status', ['Active' => 'Active', 'Suspended' => 'Suspended', 'Removed' => 'Removed / Regular Member']],
            ];
        } elseif ($kind === 'deposit_withdraw') {
            $members = Supplier::withTrashed()->where(fn ($q) => $q
                ->whereExists(fn ($q) => $q->selectRaw('1')->from('shomriddhi_accounts as a')->whereColumn('a.customer_id', 'suppliers.id')->whereReportBranch('a.origin_sister_concern_id', $branchId))
                ->orWhereIn('id', DB::table('membership_cashouts')->whereReportBranch('branch_id', $branchId)->where('status', 'Completed')->select('customer_id')))
                ->orderBy('name')->get()->mapWithKeys(fn ($m) => [$m->id => implode(' / ', array_filter([$m->name, $m->member_no, $m->mobile_no]))])->all();
            $options = ['customer_id' => ['Member', $members], 'type' => ['Type', ['deposit' => 'Deposit', 'withdraw' => 'Withdraw']],
                'status' => ['Status', ['Approved' => 'Approved', 'Pending' => 'Pending', 'Rejected' => 'Rejected']],
                'source' => ['Source', ['savings' => 'Savings Deposit / Withdraw', 'branch_cashout' => 'App Branch Cash-out']]];
        } else {
            $memberIds = DB::table('membership_referral_rewards')->whereReportBranch('branch_id', $branchId)->where('referrer_type', 'member')->pluck('referrer_id');
            // Include members eligible for milestone-only history, including retired records.
            $referrers = Supplier::withTrashed()->where(fn ($q) => $q->whereIn('id', $memberIds)->orWhereReportBranch('origin_sister_concern_id', $branchId))
                ->orderBy('name')->get()->mapWithKeys(fn ($m) => ['member:'.$m->id => 'Member / '.$m->name.' / '.$m->member_no])->all();
            $employeeIds = DB::table('membership_referral_rewards')->whereReportBranch('branch_id', $branchId)->where('referrer_type', 'agent')->pluck('referrer_id');
            foreach (User::withTrashed()->whereIn('id', $employeeIds)->orderBy('name')->get() as $employee) {
                $referrers['agent:'.$employee->id] = 'Employee / '.$employee->name.' / '.$employee->username;
            }
            $options = ['referrer_key' => ['Referrer / Beneficiary', $referrers],
                'referrer_type' => ['Referrer Type', ['member' => 'Member', 'agent' => 'Employee']],
                'bonus_kind' => ['Bonus Type', ['referral' => 'Referral Points / Commission', 'milestone' => 'Milestone Cash Bonus']],
                'payment_status' => ['Payment Status', ['paid' => 'Paid / Points Credited', 'pending' => 'Pending Cash']],
            ];
        }
        $filterLabels = [];
        foreach ($options as $key => [$label, $values]) {
            if (isset($filters[$key]) && $filters[$key] !== '') $filterLabels[$label] = $values[$filters[$key]] ?? 'No matching selection';
        }
        $report = $request->has('search') ? app(MembershipReports::class)->$kind($branch->id, $filters) : null;
        return view($report === null ? 'shomriddhi_advance.reports.membership_filters' : 'shomriddhi_advance.reports.membership_report',
            compact('kind', 'title', 'dateLabel', 'branch', 'options', 'filters', 'filterLabels', 'report'));
    }
}
