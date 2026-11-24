<?php

namespace App\Services\Membership;

use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\MembershipAgent;
use App\Models\MembershipAgentTransaction;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\Supplier;
use App\Support\ApplicationDate;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class AgentTransactionService
{
    public static function commission(int $amount, object $s): int
    {
        return $s->commission_type === 'percentage'
            ? intdiv($amount * CashoutService::minor($s->commission_rate) + 5000, 10000)
            : CashoutService::minor($s->commission_rate);
    }

    public function quote(Supplier $sender, string $kind, string $identifier, string $amount, ?int $recipientId = null): array
    {
        abort_unless(in_array($kind, ['agent_cashin', 'agent_cashout'], true), 404);
        $agent = $kind === 'agent_cashout' ? MembershipAgent::where('agent_code', $identifier)->first() : null;
        $q = (new TransferService($kind))->quote($sender, $identifier, $amount, $recipientId ?? $agent?->customer_id);
        $q['commission'] = $kind === 'agent_cashout' ? CashoutService::money(self::commission(CashoutService::minor($q['amount']), (object) json_decode(Crypt::decryptString($q['quote']), true)['settings'])) : '0.00';

        return $q;
    }

    public function complete(Supplier $sender, string $kind, string $quote, string $pin, string $key): MembershipAgentTransaction
    {
        abort_unless(in_array($kind, ['agent_cashin', 'agent_cashout'], true), 404);

        return DB::transaction(function () use ($sender, $kind, $quote, $pin, $key) {
            // The shared transfer engine locks both members and savings accounts in ID order.
            $t = (new TransferService($kind))->send($sender, $quote, $pin, $key);
            $existing = MembershipAgentTransaction::where('transfer_id', $t->id)->first();
            if ($existing) {
                return $existing;
            }
            $agentCustomer = $kind === 'agent_cashin' ? $t->sender_id : $t->recipient_id;
            $agent = MembershipAgent::where('customer_id', $agentCustomer)->lockForUpdate()->firstOrFail();
            $settings = (object) json_decode(Crypt::decryptString($quote), true)['settings'];
            $commission = $kind === 'agent_cashout' ? self::commission(CashoutService::minor($t->amount), $settings) : 0;
            $record = MembershipAgentTransaction::create(['reference' => $t->reference, 'type' => $kind,
                'agent_id' => $agent->id, 'customer_id' => $kind === 'agent_cashin' ? $t->recipient_id : $t->sender_id,
                'initiator_id' => $sender->id, 'transfer_id' => $t->id, 'idempotency_key' => $key, 'quote_hash' => hash('sha256', $quote),
                'amount' => $t->amount, 'customer_charge' => $t->fee, 'agent_commission' => CashoutService::money($commission),
                'settings_snapshot' => (array) $settings, 'status' => 'Completed', 'completed_at' => now()]);
            if ($commission) {
                $expense = AccountHead::with('accountGroup')->find($settings->expense_head_id);
                if (! $expense || $expense->status != 1 || $expense->nature !== 'Debit' || $expense->sister_concern_id || $expense->accountGroup?->top_reserved_id != 5) {
                    CashoutService::fail('Agent commission expense ledger is invalid.', 'এজেন্ট কমিশনের ব্যয় লেজার সঠিক নয়।');
                }
                $account = ShomriddhiAccount::whereKey($t->recipient_account_id)->lockForUpdate()->firstOrFail();
                $heads = AccountHead::where('supplier_id', $agentCustomer)->where('head_type', 'savings')->where('status', 1)->where('nature', 'Credit')->get();
                if ($heads->count() !== 1 || ($heads->first()->sister_concern_id && $heads->first()->sister_concern_id != $account->origin_sister_concern_id)) {
                    CashoutService::fail('Invalid agent savings ledger.', 'এজেন্টের সঞ্চয় লেজার সঠিক নয়।');
                }
                $branch = $account->origin_sister_concern_id;
                $no = generateVoucherNo(1, 'vouchers', $branch);
                $v = Voucher::create(['sister_concern_id' => $branch, 'voucher_type' => 1, 'amount' => CashoutService::money($commission),
                    'date' => ApplicationDate::today(), 'voucher_no' => $no, 'voucher_no_group_sl' => (int) filter_var($no, FILTER_SANITIZE_NUMBER_INT),
                    'user_id' => $settings->posting_user_id, 'notes' => $t->reference.' agent commission']);
                foreach ([[$expense->id, 1], [$heads->first()->id, 2]] as [$head, $side]) {
                    Transaction::create(['sister_concern_id' => $branch, 'voucher_id' => $v->id, 'account_head_id' => $head, 'voucher_type' => 1,
                        'transaction_type' => $side, 'amount' => $v->amount, 'date' => $v->date, 'user_id' => $v->user_id,
                        'notes' => $v->notes, 'voucher_no' => $no, 'voucher_no_group_sl' => $v->voucher_no_group_sl]);
                }
                $account->principal_balance = CashoutService::money(CashoutService::minor($account->principal_balance) + $commission);
                $account->save();
                ShomriddhiTransaction::create(['customer_id' => $agentCustomer, 'type' => 'deposit', 'amount' => $v->amount, 'date' => $v->date,
                    'note' => $v->notes, 'status' => 'Approved', 'voucher_id' => $v->id, 'created_by' => $v->user_id, 'approved_by' => $v->user_id, 'approved_at' => now()]);
                $record->update(['commission_voucher_id' => $v->id]);
            }

            return $record;
        }, 3);
    }
}
