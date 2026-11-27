<?php

namespace App\Services\Reports;

use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;

class PartyLedgerReport
{
    public function parties(string $kind, ?int $branch): Builder
    {
        return Supplier::withTrashed()->whereIn('type', $kind === 'customer' ? ['Customer', 'Corporate_Customer', 'Dealer'] : ['Supplier'])
            ->where(fn ($q) => $q->whereReportBranch('origin_sister_concern_id', $branch)->orWhereExists(fn ($q) => $q->selectRaw('1')
                ->from('account_heads as h')->join('transactions as t', 't.account_head_id', '=', 'h.id')
                ->whereColumn('h.supplier_id', 'suppliers.id')->where('h.head_type', 'primary')->whereReportBranch('t.sister_concern_id', $branch)->whereNull('t.deleted_at')));
    }

    public function generate(int $party, ?int $branch, string $start, string $end): array
    {
        // Primary trade accounts only. Savings, loan and other linked accounts
        // represent separate liabilities/assets and must not offset trade dues.
        $heads = AccountHead::withTrashed()->where('supplier_id', $party)->where('head_type', 'primary')->pluck('id');
        $query = Transaction::whereIn('account_head_id', $heads)->whereReportBranch('sister_concern_id', $branch)->whereIn('transaction_type', [1, 2]);
        $opening = (clone $query)->where('date', '<', $start)->selectRaw('COALESCE(SUM(CASE WHEN transaction_type = 1 THEN amount ELSE -amount END), 0) AS balance')->value('balance');
        $balance = (int) round((float) $opening * 100);
        $opening = $balance;
        $debit = $credit = 0;
        $rows = [];
        foreach ((clone $query)->whereBetween('date', [$start, $end])->with('payeeDepositorHead')->orderBy('date')->orderBy('voucher_no_group_sl')->orderBy('id')->get() as $entry) {
            $amount = (int) round((float) $entry->amount * 100);
            $dr = (int) $entry->transaction_type === 1 ? $amount : 0;
            $cr = (int) $entry->transaction_type === 2 ? $amount : 0;
            $debit += $dr; $credit += $cr; $balance += $dr - $cr;
            $rows[] = ['date' => $entry->date, 'reference' => $entry->voucher_no ?: 'Entry #'.$entry->id,
                'narration' => trim(implode(' — ', array_filter([$entry->payeeDepositorHead?->name, $entry->notes]))),
                'debit' => $dr, 'credit' => $cr, 'balance' => $balance];
        }
        return compact('opening', 'rows', 'debit', 'credit', 'balance');
    }
}
