<?php
namespace App\Services\Reports;

use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Enumeration\PaymentPurpose;

class MemberSavingsLedgerReport
{
    private function query($ids)
    {
        return ShomriddhiTransaction::whereIn('shomriddhi_transactions.customer_id', $ids)->where('shomriddhi_transactions.status', 'Approved')->whereIn('shomriddhi_transactions.type', ['deposit', 'withdraw', 'interest_capitalization']);
    }
    public function totals($ids, array $filters)
    {
        $period = $this->query($ids);
        if (! empty($filters['start_date'])) $period->where('shomriddhi_transactions.date', '>=', $filters['start_date']);
        if (! empty($filters['end_date'])) $period->where('shomriddhi_transactions.date', '<=', $filters['end_date']);
        $totals = $period->select('customer_id')->selectRaw("SUM(CASE WHEN type = 'withdraw' THEN 0 ELSE amount END) AS deposit, SUM(CASE WHEN type = 'withdraw' THEN amount ELSE 0 END) AS withdraw")
            ->groupBy('customer_id')->get()->keyBy('customer_id');
        $closing = $this->query($ids)->when(! empty($filters['end_date']), fn ($q) => $q->where('shomriddhi_transactions.date', '<=', $filters['end_date']))
            ->select('customer_id')->selectRaw("SUM(CASE WHEN type = 'withdraw' THEN -amount ELSE amount END) AS balance")->groupBy('customer_id')->get()->keyBy('customer_id');
        return collect($ids)->mapWithKeys(fn ($id) => [$id => ['deposit' => (float) ($totals->get($id)?->deposit ?? 0), 'withdraw' => (float) ($totals->get($id)?->withdraw ?? 0), 'balance' => (float) ($closing->get($id)?->balance ?? 0)]]);
    }
    public function details(int $customer, array $filters): array
    {
        $query = $this->query([$customer]);
        $opening = empty($filters['start_date']) ? 0 : (clone $query)->where('shomriddhi_transactions.date', '<', $filters['start_date'])
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'withdraw' THEN -amount ELSE amount END), 0) AS balance")->value('balance');
        $opening = (int) round((float) $opening * 100);
        if (! empty($filters['start_date'])) $query->where('shomriddhi_transactions.date', '>=', $filters['start_date']);
        if (! empty($filters['end_date'])) $query->where('shomriddhi_transactions.date', '<=', $filters['end_date']);
        $query->leftJoin('vouchers as v', 'v.id', '=', 'shomriddhi_transactions.voucher_id')->select('shomriddhi_transactions.*', 'v.voucher_no', 'v.payment_purpose', 'v.deleted_at as voucher_deleted_at');
        $rows = []; $links = []; $balance = $opening; $deposit = $withdraw = 0;
        $rows[] = [$filters['start_date'] ?? 'All dates', '—', 'Opening Balance', '—', '—', number_format($balance / 100, 2), ''];
        $purposes = PaymentPurpose::names();
        foreach ($query->orderBy('shomriddhi_transactions.date')->orderBy('shomriddhi_transactions.id')->get() as $tx) {
            $amount = (int) round((float) $tx->amount * 100);
            $out = $tx->type === 'withdraw';
            if ($out) $withdraw += $amount; else $deposit += $amount;
            $balance += $out ? -$amount : $amount;
            if ($tx->voucher_id && $tx->voucher_no && ! $tx->voucher_deleted_at) $links[count($rows)][1] = $tx->voucher_id;
            $rows[] = [$tx->date, $tx->voucher_no ?: 'Entry #'.$tx->id, $tx->type === 'interest_capitalization' ? 'Interest Capitalization' : ($purposes[$tx->payment_purpose] ?? ucfirst($tx->type)),
                $out ? '—' : number_format($amount / 100, 2), $out ? number_format($amount / 100, 2) : '—', number_format($balance / 100, 2), $tx->note];
        }
        return ['columns' => ['Date', 'Voucher / Reference', 'Transaction', 'Deposit (Tk)', 'Withdraw (Tk)', 'Ledger Balance (Tk)', 'Notes'], 'rows' => $rows, 'voucher_links' => $links,
            'totals' => ['Opening Balance (Tk)' => number_format($opening / 100, 2), 'Total Deposit (Tk)' => number_format($deposit / 100, 2), 'Total Withdraw (Tk)' => number_format($withdraw / 100, 2), 'Closing Ledger Balance (Tk)' => number_format($balance / 100, 2)],
            'note' => 'Approved savings ledger entries only, including transfers, cash-outs, charges, payments, refunds, commissions and capitalized interest when posted. Interest credits and opening-balance postings are included as deposits. Pending/rejected/deleted entries and unposted requests are excluded. Ledger closing balance is derived from recorded history; current savings is the stored account balance and may differ for legacy accounts with incomplete history.'];
    }
}
