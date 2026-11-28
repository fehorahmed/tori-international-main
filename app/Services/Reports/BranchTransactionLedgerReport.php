<?php

namespace App\Services\Reports;

use App\Enumeration\PaymentPurpose;
use App\Models\Account\Transaction;
use App\Models\BranchManagement\SisterConcern;

class BranchTransactionLedgerReport
{
    public function generate(?int $branch, string $start, string $end): array
    {
        // All journal lines, once each. Do not join voucher counter-entries back
        // onto these rows: that duplicates postings and corrupts the totals.
        $query = Transaction::whereReportBranch('sister_concern_id', $branch)->whereIn('transaction_type', [1, 2]);
        $openings = (clone $query)->where('date', '<', $start)->select('sister_concern_id')
            ->selectRaw('SUM(CASE WHEN transaction_type = 1 THEN amount ELSE -amount END) AS balance')
            ->groupBy('sister_concern_id')->get()->keyBy(fn ($r) => (string) $r->sister_concern_id);
        $entries = (clone $query)->whereBetween('date', [$start, $end])->with([
            'accountHead' => fn ($q) => $q->withTrashed(), 'payeeDepositorHead' => fn ($q) => $q->withTrashed(), 'voucher',
        ])->orderBy('date')->orderBy('voucher_no_group_sl')->orderBy('voucher_id')->orderBy('id')->get()
            ->groupBy(fn ($r) => (string) $r->sister_concern_id);
        $branches = SisterConcern::orderBy('code')->orderBy('id')->get()->keyBy('id');
        $ids = $branch !== null ? collect([(string) $branch]) : $entries->keys()->merge($openings->keys())->unique()->sortBy(fn ($id) => $branches->get($id)?->code ?? (string) $id);
        $purposes = PaymentPurpose::names();
        $statements = []; $totalDebit = $totalCredit = $count = 0;
        foreach ($ids as $id) {
            $opening = (int) round((float) ($openings->get($id)?->balance ?? 0) * 100);
            $balance = $opening; $debit = $credit = 0; $rows = [];
            foreach ($entries->get($id, collect()) as $entry) {
                $amount = (int) round((float) $entry->amount * 100);
                $dr = (int) $entry->transaction_type === 1 ? $amount : 0;
                $cr = (int) $entry->transaction_type === 2 ? $amount : 0;
                $debit += $dr; $credit += $cr; $balance += $dr - $cr;
                $narration = trim(implode(' — ', array_filter([$entry->payeeDepositorHead?->name, $entry->notes])));
                if ($narration === '') $narration = $entry->voucher?->notes ?: ($entry->is_opening_balance ? 'Opening balance adjustment' : ($purposes[$entry->voucher?->payment_purpose] ?? 'Ledger posting'));
                $rows[] = ['date' => $entry->date, 'reference' => $entry->voucher_no ?: 'Entry #'.$entry->id, 'voucher_id' => $entry->voucher?->id,
                    'account' => trim(($entry->accountHead?->code ? $entry->accountHead->code.' — ' : '').($entry->accountHead?->name ?? 'Account #'.$entry->account_head_id)),
                    'narration' => $narration, 'debit' => $dr, 'credit' => $cr, 'balance' => $balance];
            }
            if ($branch === null && ! $rows && $opening === 0) continue;
            $statementBranch = $branches->get($id);
            if (! $statementBranch) {
                $statementBranch = new SisterConcern();
                $statementBranch->id = $id ?: null;
                $statementBranch->name = $id === '' ? 'Unassigned Branch' : 'Branch #'.$id;
            }
            $report = compact('opening', 'rows', 'debit', 'credit', 'balance');
            $statements[] = ['branch' => $statementBranch, 'report' => $report];
            $totalDebit += $debit; $totalCredit += $credit; $count += count($rows);
        }
        return ['statements' => $statements, 'debit' => $totalDebit, 'credit' => $totalCredit, 'count' => $count];
    }
}
