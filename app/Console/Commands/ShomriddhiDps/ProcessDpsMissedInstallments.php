<?php

namespace App\Console\Commands\ShomriddhiDps;

use Illuminate\Console\Command;
use App\Models\ShomriddhiDps\DpsAccount;
use App\Models\ShomriddhiDps\DpsTransaction;
use App\Models\Account\AccountHead;
use App\Models\Account\AccountGroup;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Enumeration\ReservedAccountGroup;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessDpsMissedInstallments extends Command
{
    protected $signature   = 'shomriddhi-dps:check-missed-installments';
    protected $description = 'Check for missed DPS installments, apply penalties, and flag defaulted accounts.';

    /**
     * Missed installments threshold before marking as Defaulted.
     */
    const DEFAULTED_THRESHOLD = 3;

    public function handle(): void
    {
        $this->info('Starting DPS Missed Installment check...');

        $today = Carbon::today()->format('Y-m-d');

        /** @var \Illuminate\Database\Eloquent\Collection<DpsAccount> $overdueAccounts */
        $overdueAccounts = DpsAccount::with(['scheme', 'customer'])
            ->where('status', 'Active')
            ->whereNotNull('next_installment_date')
            ->whereDate('next_installment_date', '<', $today) // strictly past due
            ->get();

        if ($overdueAccounts->isEmpty()) {
            $this->info('No overdue DPS accounts found. All good!');
            return;
        }

        $this->info("Found {$overdueAccounts->count()} overdue account(s). Processing...");

        /** @var DpsAccount $account */
        foreach ($overdueAccounts as $account) {
            $this->processMissedInstallment($account, $today);
        }

        $this->info('DPS Missed Installment check completed.');
    }

    private function processMissedInstallment(DpsAccount $account, string $today): void
    {
        DB::beginTransaction();
        try {
            $scheme = $account->scheme;
            $penaltyAmount = 0;
            if ($scheme && $scheme->penalty_amount > 0) {
                if ($scheme->penalty_type === 'Percentage') {
                    $penaltyAmount = round($account->installment_amount * ($scheme->penalty_amount / 100), 2);
                } else {
                    $penaltyAmount = $scheme->penalty_amount;
                }
            }

            // Log the missed installment as a Penalty transaction
            $dpsTxn = DpsTransaction::create([
                'dps_account_id'          => $account->id,
                'installment_no'          => null,
                'type'                    => 'Penalty',
                'amount'                  => 0,
                'penalty_amount'          => $penaltyAmount,
                'payment_account_head_id' => null,
                'date'                    => $today,
                'note'                    => "Missed installment penalty. Due date: {$account->next_installment_date->format('d-M-Y')}",
                'created_by'              => 1, // System user
            ]);

            // Update account counters
            $account->increment('missed_installments');
            $account->increment('total_penalty_charged', $penaltyAmount);

            // Post penalty GL voucher only if penalty > 0
            if ($penaltyAmount > 0) {
                $this->postPenaltyVoucher($account, $dpsTxn, $penaltyAmount, $today);
            }

            // Advance next_installment_date by one month to avoid re-triggering tomorrow
            $account->update([
                'next_installment_date' => Carbon::parse($account->next_installment_date)->addMonth()->format('Y-m-d'),
            ]);

            // Mark as Defaulted if threshold exceeded
            if ($account->missed_installments >= self::DEFAULTED_THRESHOLD) {
                $account->update(['status' => 'Defaulted']);
                $this->warn("DPS-{$account->dps_no} marked as DEFAULTED after {$account->missed_installments} missed installments.");
            } else {
                $this->line("DPS-{$account->dps_no} — Missed installment #{$account->missed_installments}. Penalty: ৳{$penaltyAmount}");
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("DPS Missed Installment Error [Account #{$account->id}]: " . $e->getMessage());
            $this->error("Failed to process missed installment for DPS-{$account->dps_no}: " . $e->getMessage());
        }
    }

    private function postPenaltyVoucher(DpsAccount $account, DpsTransaction $dpsTxn, float $penaltyAmount, string $date): void
    {
        $scId      = $account->origin_sister_concern_id;
        $noteText  = "DPS Penalty — DPS-{$account->dps_no}";

        // DPS Liability Head
        $dpsGroup = AccountGroup::where('name', 'DPS Deposits')->first();
        $dpsHead  = AccountHead::where('supplier_id', $account->customer_id)
            ->where('head_type', 'dps')
            ->when($dpsGroup, fn($q) => $q->where('account_group_id', $dpsGroup->id))
            ->first();

        // Penalty Income Head
        $incGroup      = AccountGroup::where('reserved_id', ReservedAccountGroup::OTHER_INCOME)->first();
        $penaltyIncHead = AccountHead::where('name', 'DPS Penalty Income')->where('is_system', 1)->first();
        if (!$penaltyIncHead) {
            $penaltyIncHead = AccountHead::firstOrCreate(
                ['name' => 'DPS Penalty Income'],
                [
                    'sister_concern_id' => null,
                    'account_group_id'  => $incGroup->id ?? null,
                    'code'              => generateAccountCode($incGroup->top_reserved_id ?? ReservedAccountGroup::REVENUE),
                    'nature'            => 'Credit',
                    'head_type'         => 'primary',
                    'is_system'         => 1,
                    'is_editable'       => 0,
                    'status'            => 1,
                ]
            );
        }

        if (!$dpsHead || !$penaltyIncHead) {
            return; // Skip GL if heads not found — safety guard
        }

        $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER, 'vouchers', $scId);
        $voucherSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

        $voucher = Voucher::create([
            'sister_concern_id'   => $scId,
            'voucher_type'        => VoucherType::$JOURNAL_VOUCHER,
            'amount'              => $penaltyAmount,
            'date'                => $date,
            'voucher_no'          => $voucherNo,
            'voucher_no_group_sl' => $voucherSl,
            'notes'               => $noteText,
            'user_id'             => 1, // System user
        ]);

        // DR Customer DPS Head (Liability increases due to penalty)
        Transaction::create([
            'sister_concern_id'   => $scId,
            'voucher_id'          => $voucher->id,
            'account_head_id'     => $dpsHead->id,
            'voucher_type'        => VoucherType::$JOURNAL_VOUCHER,
            'transaction_type'    => TransactionType::$DEBIT,
            'amount'              => $penaltyAmount,
            'date'                => $date,
            'user_id'             => 1,
            'notes'               => $noteText,
            'voucher_no'          => $voucherNo,
            'voucher_no_group_sl' => $voucherSl,
        ]);

        // CR Penalty Income
        Transaction::create([
            'sister_concern_id'   => $scId,
            'voucher_id'          => $voucher->id,
            'account_head_id'     => $penaltyIncHead->id,
            'voucher_type'        => VoucherType::$JOURNAL_VOUCHER,
            'transaction_type'    => TransactionType::$CREDIT,
            'amount'              => $penaltyAmount,
            'date'                => $date,
            'user_id'             => 1,
            'notes'               => $noteText,
            'voucher_no'          => $voucherNo,
            'voucher_no_group_sl' => $voucherSl,
        ]);

        $dpsTxn->update(['voucher_id' => $voucher->id]);
    }
}
