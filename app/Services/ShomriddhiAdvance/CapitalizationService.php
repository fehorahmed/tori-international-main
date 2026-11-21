<?php

namespace App\Services\ShomriddhiAdvance;

use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\BranchManagement\SisterConcern;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiDailyInterest;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\User;
use App\Services\Membership\CashoutService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CapitalizationService
{
    /** Full withdrawal may capitalize new interest even after the month's posting. */
    public function capitalize(ShomriddhiAccount $account, ?string $date = null): void
    {
        $this->post($account->id, $date ?? now()->toDateString(), false);
    }

    /** Duplicate checks must run under the same account lock as the posting. */
    public function capitalizeMonthly(ShomriddhiAccount $account, ?string $date = null): void
    {
        $this->post($account->id, $date ?? now()->toDateString(), true);
    }

    /** Recover closed periods in order; leave the current unfinished month alone. */
    public function recoverMonthly(ShomriddhiAccount $account, ?string $throughDate = null): void
    {
        $through = Carbon::parse($throughDate ?? now()->toDateString())->startOfDay();
        $lastEnd = $through->isLastOfMonth() ? $through->copy() : $through->copy()->startOfMonth()->subDay();
        $firstPending = ShomriddhiDailyInterest::where('customer_id', $account->customer_id)
            ->where('status', 'Pending')->where('date', '<=', $lastEnd->toDateString())->min('date');
        if (! $firstPending) {
            if ($account->accrued_interest >= 0.01 && ! ShomriddhiDailyInterest::where('customer_id', $account->customer_id)->exists()) {
                throw new \RuntimeException('Interest history is missing; monthly recovery requires reconciliation.');
            }
            return;
        }
        for ($end = Carbon::parse($firstPending)->endOfMonth()->startOfDay(); $end->lte($lastEnd); $end->addDay()->endOfMonth()->startOfDay()) {
            $this->capitalizeMonthly($account, $end->toDateString());
        }
    }

    private function post(int $accountId, string $date, bool $monthly): void
    {
        DB::transaction(function () use ($accountId, $date, $monthly) {
            $account = ShomriddhiAccount::whereKey($accountId)->lockForUpdate()->firstOrFail();
            if ($account->status !== 'Active') return;
            $period = Carbon::parse($date)->startOfMonth();
            if ($monthly && ShomriddhiTransaction::where('customer_id', $account->customer_id)
                ->where('type', 'interest_capitalization')->where('status', 'Approved')
                ->where('date', '>=', $period->toDateString())->where('date', '<', $period->copy()->addMonth()->toDateString())
                ->exists()) return;

            $units = (int) round((float) $account->accrued_interest * 10000);
            if ($monthly) {
                if (! $account->last_interest_date || $account->last_interest_date < $date) {
                    throw new \RuntimeException('Daily interest is incomplete through '.$date.'. Run daily recovery before monthly posting.');
                }
                // Accrued interest may also contain later months. Capitalize only
                // this period and earlier carry, retaining all future daily accruals.
                $future = ShomriddhiDailyInterest::where('customer_id', $account->customer_id)
                    ->where('status', 'Pending')->where('date', '>', $date)->sum('earned_interest');
                $futureUnits = (int) round((float) $future * 10000);
                if ($futureUnits > $units) throw new \RuntimeException('Accrued interest does not match daily history; reconcile before recovery.');
                $units -= $futureUnits;
            }
            $minor = intdiv($units, 100);
            if ($minor <= 0) return;
            $amount = CashoutService::money($minor);

            // Serialize journal numbering for this branch, including different accounts.
            $branch = SisterConcern::whereKey($account->origin_sister_concern_id)->lockForUpdate()->first();
            if (! $branch) throw new \RuntimeException('Interest capitalization branch is missing.');
            if (! User::whereKey(1)->exists()) throw new \RuntimeException('Interest capitalization system user is missing.');
            $savings = AccountHead::where('supplier_id', $account->customer_id)->where('head_type', 'savings')
                ->where('status', 1)->where('nature', 'Credit')->lockForUpdate()->get();
            if ($savings->count() !== 1) throw new \RuntimeException('Exactly one active customer savings ledger head is required.');
            $savings = $savings->first();
            if ($savings->sister_concern_id && $savings->sister_concern_id != $branch->id) {
                throw new \RuntimeException('Customer savings ledger belongs to another branch.');
            }

            // The group lock prevents concurrent jobs creating duplicate expense heads.
            $group = AccountGroup::where('reserved_id', ReservedAccountGroup::FINANCIAL_EXPENSES)->lockForUpdate()->first();
            if (! $group) throw new \RuntimeException('Interest expense account group is missing.');
            $expense = AccountHead::where('reserved_id', ReservedAccountHead::SHOMRIDDHI_INTEREST_EXPENSE)->lockForUpdate()->first();
            if (! $expense) {
                $expense = AccountHead::create(['sister_concern_id' => null,
                    'code' => generateAccountCode(ReservedAccountGroup::FINANCIAL_EXPENSES),
                    'reserved_id' => ReservedAccountHead::SHOMRIDDHI_INTEREST_EXPENSE,
                    'account_group_id' => $group->id, 'name' => 'Shomriddhi Interest Expense', 'nature' => 'Debit',
                    'head_type' => 'primary', 'status' => 1, 'is_system' => 1, 'is_editable' => 0, 'is_control_account' => 0]);
            }
            if ($expense->status != 1 || $expense->nature !== 'Debit'
                || ($expense->sister_concern_id && $expense->sister_concern_id != $branch->id)) {
                throw new \RuntimeException('Interest expense ledger head is inactive or invalid.');
            }

            $number = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER, 'vouchers', $branch->id);
            $voucher = Voucher::create(['sister_concern_id' => $branch->id, 'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                'amount' => $amount, 'date' => $date, 'voucher_no' => $number,
                'voucher_no_group_sl' => abs((int) filter_var($number, FILTER_SANITIZE_NUMBER_INT)),
                'notes' => 'Auto Interest Capitalization', 'user_id' => 1]);
            foreach ([$expense->id => TransactionType::$DEBIT, $savings->id => TransactionType::$CREDIT] as $head => $type) {
                Transaction::create(['sister_concern_id' => $branch->id, 'voucher_id' => $voucher->id,
                    'account_head_id' => $head, 'voucher_type' => $voucher->voucher_type, 'transaction_type' => $type,
                    'amount' => $amount, 'date' => $date, 'user_id' => 1, 'notes' => 'Shomriddhi Monthly Interest Capitalization',
                    'voucher_no' => $number, 'voucher_no_group_sl' => $voucher->voucher_no_group_sl]);
            }
            ShomriddhiTransaction::create(['customer_id' => $account->customer_id, 'type' => 'interest_capitalization',
                'amount' => $amount, 'date' => $date, 'note' => 'System Auto Capitalization', 'status' => 'Approved',
                'created_by' => 1, 'approved_by' => 1, 'approved_at' => now(), 'voucher_id' => $voucher->id]);
            $account->principal_balance = CashoutService::money(CashoutService::minor($account->principal_balance) + $minor);
            $account->accrued_interest = number_format(((int) round((float) $account->accrued_interest * 10000) - $minor * 100) / 10000, 4, '.', '');
            $account->save();
            ShomriddhiDailyInterest::where('customer_id', $account->customer_id)->where('status', 'Pending')
                ->where('date', '<=', $date)->update(['status' => 'Capitalized']);
        }, 3);
    }
}
