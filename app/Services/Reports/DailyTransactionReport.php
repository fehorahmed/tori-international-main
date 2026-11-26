<?php

namespace App\Services\Reports;

use App\Enumeration\ReservedAccountGroup;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use Illuminate\Database\Eloquent\Builder;

class DailyTransactionReport
{
    public const SECTIONS = [
        'income' => 'Company Income',
        'expense' => 'Company Expense',
        'cash' => 'Cash / Bank Movement',
        'other' => 'Other Ledger Movements (Assets, Liabilities & Equity)',
    ];

    public function query(string $start, string $end, ?int $branch): Builder
    {
        // Accounting entries are the posting source. Opening balances and deleted
        // entries are excluded; reversals remain and offset the original entries.
        return Transaction::whereBetween('date', [$start, $end])
            ->where('is_opening_balance', 0)
            ->when($branch !== null, fn ($q) => $q->where('sister_concern_id', $branch));
    }

    public function summary(string $start, string $end, ?int $branch): array
    {
        $entries = $this->query($start, $end, $branch)
            ->select('account_head_id')
            ->selectRaw('COUNT(*) AS entry_count')
            ->selectRaw('SUM(CASE WHEN transaction_type = 1 THEN CAST(amount AS DECIMAL(24,2)) ELSE 0 END) AS debit')
            ->selectRaw('SUM(CASE WHEN transaction_type = 2 THEN CAST(amount AS DECIMAL(24,2)) ELSE 0 END) AS credit')
            ->groupBy('account_head_id')->get();
        // Retired/inactive heads still belong to historical reports.
        $heads = AccountHead::withTrashed()->whereIn('id', $entries->pluck('account_head_id'))->get()->keyBy('id');
        $groups = AccountGroup::withTrashed()->get()->keyBy('id');
        $sections = array_fill_keys(array_keys(self::SECTIONS), []);
        $totals = ['income' => 0, 'expense' => 0, 'cash_in' => 0, 'cash_out' => 0];
        foreach ($entries as $entry) {
            $head = $heads->get($entry->account_head_id);
            $section = $this->section($head, $groups);
            $debit = (int) round((float) $entry->debit * 100);
            $credit = (int) round((float) $entry->credit * 100);
            $net = $section === 'income' ? $credit - $debit : $debit - $credit;
            $sections[$section][] = [
                'id' => $entry->account_head_id,
                'name' => $head?->name ?? 'Missing account head #'.$entry->account_head_id,
                'code' => $head?->code,
                'count' => (int) $entry->entry_count,
                'debit' => $debit / 100, 'credit' => $credit / 100, 'net' => $net / 100,
            ];
            if (in_array($section, ['income', 'expense'], true)) {
                $totals[$section] += $net;
            } elseif ($section === 'cash') {
                $totals['cash_in'] += $debit;
                $totals['cash_out'] += $credit;
            }
        }
        foreach ($sections as &$rows) {
            usort($rows, fn ($a, $b) => strnatcasecmp($a['name'], $b['name']));
        }
        unset($rows);
        $totals['net_income'] = $totals['income'] - $totals['expense'];
        return ['sections' => $sections, 'totals' => array_map(fn ($v) => $v / 100, $totals)];
    }

    private function section(?AccountHead $head, $groups): string
    {
        $group = $groups->get($head?->account_group_id);
        $visited = [];
        while ($group && ! isset($visited[$group->id])) {
            $visited[$group->id] = true;
            if ((int) $group->reserved_id === ReservedAccountGroup::REVENUE) {
                return 'income';
            }
            if ((int) $group->reserved_id === ReservedAccountGroup::EXPENSES) {
                return 'expense';
            }
            $group = $groups->get($group->account_group_id);
        }
        return $head && (int) $head->payment_mode > 0 ? 'cash' : 'other';
    }
}
