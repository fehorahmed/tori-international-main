<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        $group = DB::table('account_groups')->whereNull('deleted_at')->where('reserved_id', 30)->value('id');
        if (! $group) {
            return;
        }
        $ids = DB::table('account_heads')->whereNull('deleted_at')->where('head_type', 'fdr')->where('account_group_id', $group)->where('nature', 'Debit')->pluck('id');
        foreach ($ids as $id) {
            DB::transaction(function () use ($id) {
                $head = DB::table('account_heads')->whereNull('deleted_at')->where('id', $id)->lockForUpdate()->first();
                $accounts = DB::table('fdr_accounts')->where('customer_id', $head->supplier_id)->whereNull('deleted_at')->lockForUpdate()->get();
                $heads = DB::table('account_heads')->whereNull('deleted_at')->where('head_type', 'fdr')->where('supplier_id', $head->supplier_id)->count();
                $minor = fn ($value) => (int) round((float) $value * 100);
                $expected = $accounts->whereIn('status', ['Active', 'Matured'])->sum(fn ($a) => $minor($a->principal_amount));
                $debit = $minor(DB::table('transactions')->whereNull('deleted_at')->where('account_head_id', $id)->where('transaction_type', 1)->sum('amount'));
                $credit = $minor(DB::table('transactions')->whereNull('deleted_at')->where('account_head_id', $id)->where('transaction_type', 2)->sum('amount'));
                if ($heads !== 1 || $credit - $debit !== $expected) {
                    Log::warning('FDR liability nature requires manual reconciliation', ['account_head_id' => $id]);

                    return;
                }
                // Correct normal balance metadata only; do not rewrite journals or customer balances.
                DB::table('account_heads')->whereNull('deleted_at')->where('id', $id)->update(['nature' => 'Credit']);
            }, 3);
        }
    }

    public function down(): void
    {
        // Restoring an incorrect debit nature would misclassify an existing liability.
    }
};
