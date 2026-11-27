<?php

namespace App\Services\Reports;

use App\Models\CustomerKyc;
use App\Models\MembershipAgent;
use App\Models\MembershipAgentTransaction;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MembershipReports
{
    private function dates($query, string $column, array $filters)
    {
        if (! empty($filters['start_date'])) $query->where($column, '>=', $filters['start_date'].' 00:00:00');
        if (! empty($filters['end_date'])) $query->where($column, '<', Carbon::parse($filters['end_date'])->addDay()->format('Y-m-d').' 00:00:00');
        return $query;
    }

    private function money($value): string { return number_format((float) $value, 2); }
    private function minor($value): int { return (int) round((float) $value * 100); }

    public function members(int $branch, array $f): array
    {
        $query = Supplier::where('type', 'Customer')->where('origin_sister_concern_id', $branch)->with('shomriddhiGroup');
        $this->dates($query, 'suppliers.created_at', $f);
        foreach (['customer_id' => 'id', 'group_id' => 'shomriddhi_group_id', 'status' => 'status'] as $filter => $column) {
            if (isset($f[$filter]) && $f[$filter] !== '') $query->where($column, $f[$filter]);
        }
        if (! empty($f['source'])) {
            $f['source'] === 'unknown' ? $query->whereNull('registration_source') : $query->where('registration_source', $f['source']);
        }
        $members = $query->orderBy('member_no')->orderBy('id')->get();
        $accounts = ShomriddhiAccount::where('origin_sister_concern_id', $branch)->where('status', 'Active')->whereIn('customer_id', $members->pluck('id'))
            ->select('customer_id')->selectRaw('SUM(principal_balance) AS principal, SUM(accrued_interest) AS accrued')->groupBy('customer_id')->get()->keyBy('customer_id');
        $kycs = CustomerKyc::whereIn('customer_id', $members->pluck('id'))->orderByDesc('id')->get()->unique('customer_id')->keyBy('customer_id');
        $principal = $accrued = $active = 0;
        $rows = [];
        foreach ($members as $member) {
            $account = $accounts->get($member->id);
            $principal += $this->minor($account?->principal ?? 0);
            $accrued += $this->minor($account?->accrued ?? 0);
            $active += (int) ($member->status == 1);
            $kyc = $kycs->get($member->id);
            $kycStatus = $kyc?->status === 'Verified' && $kyc->submitted_name !== $member->name ? 'Needs resubmission' : ($kyc?->status ?? 'Not submitted');
            $rows[] = [$member->created_at?->format('Y-m-d'), $member->member_no ?? '—', $member->name, $member->mobile_no,
                $member->shomriddhiGroup ? $member->shomriddhiGroup->code.' — '.$member->shomriddhiGroup->name_en : '—',
                $member->registration_source ? ucfirst($member->registration_source) : 'Not recorded', $member->status == 1 ? 'Active' : 'Inactive', $kycStatus,
                $this->money($account?->principal), $this->money($account?->accrued)];
        }
        return ['columns' => ['Joined', 'Member No', 'Name', 'Mobile', 'Group', 'Registration Source', 'Status', 'KYC', 'Current Savings (Tk)', 'Current Accrued Interest (Tk)'],
            'rows' => $rows, 'totals' => ['Members' => count($rows), 'Active Members' => $active, 'Current Savings (Tk)' => $this->money($principal / 100), 'Current Accrued Interest (Tk)' => $this->money($accrued / 100)],
            'note' => 'Date range applies to member joining date. Savings and accrued interest show current active-account balances, not historical balances at the end date. Legacy registrations without a recorded source are shown as Not recorded.'];
    }

    public function agents(int $branch, array $f): array
    {
        $query = MembershipAgent::with('customer')->whereHas('customer', fn ($q) => $q->where('origin_sister_concern_id', $branch));
        if (! empty($f['agent_id'])) $query->whereKey($f['agent_id']);
        if (! empty($f['status'])) $query->where('status', $f['status']);
        $agents = $query->orderBy('agent_code')->get();
        $transactions = MembershipAgentTransaction::whereIn('agent_id', $agents->pluck('id'))->where('status', 'Completed');
        $this->dates($transactions, 'completed_at', $f);
        $activity = $transactions->select('agent_id')->selectRaw("COUNT(*) AS transactions, SUM(CASE WHEN type = 'agent_cashin' THEN amount ELSE 0 END) AS cashin, SUM(CASE WHEN type = 'agent_cashout' THEN amount ELSE 0 END) AS cashout, SUM(customer_charge) AS charge_total, SUM(agent_commission) AS commission_total")
            ->groupBy('agent_id')->get()->keyBy('agent_id');
        $accounts = ShomriddhiAccount::where('origin_sister_concern_id', $branch)->where('status', 'Active')->whereIn('customer_id', $agents->pluck('customer_id'))
            ->select('customer_id')->selectRaw('SUM(principal_balance) AS principal')->groupBy('customer_id')->get()->keyBy('customer_id');
        $sums = ['cashin' => 0, 'cashout' => 0, 'charge_total' => 0, 'commission_total' => 0];
        $count = 0; $rows = [];
        foreach ($agents as $agent) {
            $stats = $activity->get($agent->id);
            foreach ($sums as $key => &$sum) $sum += $this->minor($stats?->$key ?? 0);
            unset($sum);
            $count += (int) ($stats?->transactions ?? 0);
            $rows[] = [$agent->agent_code, $agent->customer?->member_no, $agent->customer?->name, $agent->customer?->mobile_no, $agent->status,
                $stats?->transactions ?? 0, $this->money($stats?->cashin), $this->money($stats?->cashout), $this->money($stats?->charge_total), $this->money($stats?->commission_total),
                $this->money($accounts->get($agent->customer_id)?->principal)];
        }
        return ['columns' => ['Agent Code', 'Member No', 'Agent Name', 'Mobile', 'Current Status', 'Transactions', 'Member Cash-in (Tk)', 'Agent Cash-out (Tk)', 'Customer Charges (Tk)', 'Agent Commission (Tk)', 'Current Savings (Tk)'],
            'rows' => $rows, 'totals' => ['Agents' => count($rows), 'Transactions' => $count, 'Cash-in (Tk)' => $this->money($sums['cashin'] / 100), 'Cash-out (Tk)' => $this->money($sums['cashout'] / 100), 'Customer Charges (Tk)' => $this->money($sums['charge_total'] / 100), 'Commission (Tk)' => $this->money($sums['commission_total'] / 100)],
            'note' => 'Date range applies to completed agent transactions. Agent status and savings balance are current. Agents with no activity in the selected period are included with zero totals; removed agents retain their historical activity.'];
    }

    public function referrals(int $branch, array $f): array
    {
        $rows = []; $points = $cash = $paid = 0;
        $referrer = ! empty($f['referrer_key']) ? explode(':', $f['referrer_key']) : null;
        if (($f['bonus_kind'] ?? '') !== 'milestone') {
            $q = DB::table('membership_referral_rewards as b')->join('membership_referrals as r', 'r.id', '=', 'b.referral_id')
                ->leftJoin('suppliers as c', 'c.id', '=', 'r.customer_id')
                ->leftJoin('suppliers as m', fn ($q) => $q->on('m.id', '=', 'b.referrer_id')->where('b.referrer_type', 'member'))
                ->leftJoin('users as u', fn ($q) => $q->on('u.id', '=', 'b.referrer_id')->where('b.referrer_type', 'agent'))
                ->where('b.branch_id', $branch);
            $this->dates($q, 'b.created_at', $f);
            if ($referrer) $q->where('b.referrer_type', $referrer[0])->where('b.referrer_id', $referrer[1]);
            if (! empty($f['referrer_type'])) $q->where('b.referrer_type', $f['referrer_type']);
            // Points are already credited, and are not pending cash commissions.
            if (($f['payment_status'] ?? '') === 'paid') $q->where(fn ($q) => $q->where('b.referrer_type', 'member')->orWhereNotNull('b.paid_at'));
            if (($f['payment_status'] ?? '') === 'pending') $q->where('b.referrer_type', 'agent')->whereNull('b.paid_at')->where('b.amount', '>', 0);
            $rewards = $q->select('b.*', 'c.name as customer_name', 'c.member_no', 'r.referral_code', 'm.name as member_referrer', 'm.member_no as referrer_member_no', 'u.name as employee_referrer', 'u.username')->orderBy('b.created_at')->orderBy('b.id')->get();
            foreach ($rewards as $reward) {
                $isMember = $reward->referrer_type === 'member';
                $points += (int) $reward->points;
                $cash += $this->minor($reward->amount);
                if ($reward->paid_at) $paid += $this->minor($reward->amount);
                $rows[] = [substr($reward->created_at, 0, 10), $isMember ? 'Referral points' : 'Employee referral commission',
                    ($isMember ? $reward->member_referrer : $reward->employee_referrer) ?? 'Unavailable',
                    ($isMember ? $reward->referrer_member_no : $reward->username) ?? $reward->referrer_id,
                    trim(($reward->customer_name ?? '').' / '.($reward->member_no ?? ''), ' /'), $reward->points, $this->money($reward->amount),
                    $isMember ? 'Points credited' : ($reward->paid_at ? 'Paid' : 'Pending'), $reward->paid_at ?? '—', $reward->payment_voucher_id ?? $reward->voucher_id ?? '—'];
            }
        }
        if (($f['bonus_kind'] ?? '') !== 'referral' && ($f['referrer_type'] ?? '') !== 'agent' && (! $referrer || $referrer[0] === 'member')) {
            $q = DB::table('membership_milestone_bonuses as b')->leftJoin('suppliers as m', 'm.id', '=', 'b.customer_id')
                ->leftJoin('shomriddhi_accounts as a', 'a.id', '=', 'b.account_id')->leftJoin('vouchers as v', 'v.id', '=', 'b.voucher_id')
                ->whereRaw('COALESCE(v.sister_concern_id, a.origin_sister_concern_id, m.origin_sister_concern_id) = ?', [$branch]);
            $this->dates($q, 'b.created_at', $f);
            if ($referrer) $q->where('b.customer_id', $referrer[1]);
            if (($f['payment_status'] ?? '') === 'paid') $q->whereNotNull('b.paid_at');
            if (($f['payment_status'] ?? '') === 'pending') $q->whereNull('b.paid_at');
            foreach ($q->select('b.*', 'm.name', 'm.member_no')->orderBy('b.created_at')->orderBy('b.id')->get() as $bonus) {
                $cash += $this->minor($bonus->amount);
                if ($bonus->paid_at) $paid += $this->minor($bonus->amount);
                $rows[] = [substr($bonus->created_at, 0, 10), 'Milestone bonus ('.$bonus->threshold.' points)', $bonus->name ?? 'Unavailable', $bonus->member_no ?? $bonus->customer_id,
                    '—', 0, $this->money($bonus->amount), $bonus->paid_at ? 'Paid' : 'Pending', $bonus->paid_at ?? '—', $bonus->voucher_id ?? '—'];
            }
        }
        usort($rows, fn ($a, $b) => strcmp($a[0], $b[0]));
        return ['columns' => ['Earned Date', 'Bonus Type', 'Referrer / Beneficiary', 'Member No / Employee Code', 'Referred Member', 'Points', 'Cash Bonus (Tk)', 'Payment Status', 'Paid At', 'Voucher ID'],
            'rows' => $rows, 'totals' => ['Bonus Entries' => count($rows), 'Points Earned' => $points, 'Cash Bonus (Tk)' => $this->money($cash / 100), 'Paid Cash (Tk)' => $this->money($paid / 100), 'Pending Cash (Tk)' => $this->money(($cash - $paid) / 100)],
            'note' => 'Date range applies to bonus earning date. Includes referral rewards and milestone cash bonuses. Points and cash are separate; credited points are not pending cash. Internal referrer type agent represents Employee, not a cash-in/cash-out member agent. Point redemptions and shopping are not new bonus earnings.'];
    }
}
