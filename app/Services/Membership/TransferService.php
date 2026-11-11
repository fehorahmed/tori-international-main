<?php

namespace App\Services\Membership;

use App\Models\Account\AccountHead;
use App\Models\Account\InterCompanySettlement;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\BranchManagement\SisterConcern;
use App\Models\MembershipTransfer;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TransferService
{
    private function fail(string $en, string $bn): never
    {
        CashoutService::fail($en, $bn);
    }

    private function account(int $id): ShomriddhiAccount
    {
        $rows = ShomriddhiAccount::where('customer_id', $id)->where('status', 'Active')->get();
        if ($rows->count() !== 1) {
            $this->fail('Exactly one active savings account is required.', 'একটি সক্রিয় সঞ্চয় হিসাব থাকতে হবে।');
        }

        return $rows->first();
    }

    private function config(): object
    {
        $s = membership_transaction_settings('transfer');
        if (! $s->enabled || ! $s->posting_user_id) {
            $this->fail('Send Money is currently unavailable.', 'সেন্ড মানি বর্তমানে বন্ধ আছে।');
        }

        return $s;
    }

    private function version($s): string
    {
        return hash('sha256', json_encode([$s->enabled, $s->minimum_amount, $s->maximum_amount, $s->charge_type, $s->charge_rate, $s->income_head_id, $s->posting_user_id]));
    }

    public function quote(Supplier $sender, string $identifier, string $amount): array
    {
        $s = $this->config();
        $identifier = trim($identifier);
        $digits = preg_replace('/\D/', '', $identifier);
        if (str_starts_with($digits, '880') && strlen($digits) === 13) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            $digits = '0'.$digits;
        }
        $rows = Supplier::where('type', 'Customer')->where('status', 1)->where(function ($q) use ($identifier, $digits) {
            $q->where('member_no', $identifier);
            if (preg_match('/^01[3-9]\d{8}$/', $digits)) {
                $q->orWhereIn('mobile_no', [$digits, substr($digits, 1), '88'.$digits, '+88'.$digits]);
            }
        })->get();
        if ($rows->count() !== 1) {
            $this->fail('Recipient not found or the identifier is ambiguous.', 'প্রাপক পাওয়া যায়নি অথবা এই নম্বরে একাধিক হিসাব আছে।');
        }
        $recipient = $rows->first();
        if ($recipient->id === $sender->id) {
            $this->fail('You cannot send money to yourself.', 'নিজের হিসাবে টাকা পাঠানো যাবে না।');
        }
        $source = $this->account($sender->id);
        $target = $this->account($recipient->id);
        $net = CashoutService::minor($amount);
        if ($net < max(1, CashoutService::minor($s->minimum_amount)) || $net > CashoutService::minor($s->maximum_amount)) {
            $this->fail('Amount is outside the configured minimum and maximum per transfer.', 'পরিমাণ প্রতি ট্রান্সফারের নির্ধারিত সর্বনিম্ন ও সর্বোচ্চ সীমার বাইরে।');
        }
        $fee = $s->charge_type === 'percentage' ? intdiv($net * CashoutService::minor($s->charge_rate) + 5000, 10000) : CashoutService::minor($s->charge_rate);
        if ($net + $fee > CashoutService::available($source)) {
            $this->fail('Insufficient available savings balance.', 'ব্যবহারযোগ্য সঞ্চয় ব্যালেন্স পর্যাপ্ত নয়।');
        }
        $payload = ['sender' => $sender->id, 'recipient' => $recipient->id, 'source' => $source->id, 'target' => $target->id, 'amount' => $net, 'fee' => $fee, 'version' => $this->version($s), 'expires' => now()->addMinutes(2)->timestamp, 'nonce' => Str::random(24)];

        return ['quote' => Crypt::encryptString(json_encode($payload)), 'recipient_name' => $recipient->name, 'recipient_member_no' => $recipient->member_no, 'minimum_amount' => $s->minimum_amount, 'maximum_amount' => $s->maximum_amount, 'amount' => CashoutService::money($net), 'fee' => CashoutService::money($fee), 'total' => CashoutService::money($net + $fee)];
    }

    public function send(Supplier $sender, string $quote, string $pin, string $key): MembershipTransfer
    {
        try {
            $p = json_decode(Crypt::decryptString($quote), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            $this->fail('Invalid transfer quote.', 'ট্রান্সফার তথ্য সঠিক নয়।');
        }
        if (($p['sender'] ?? null) !== $sender->id) {
            $this->fail('Invalid transfer quote.', 'ট্রান্সফার তথ্য সঠিক নয়।');
        }

        return DB::transaction(function () use ($sender, $p, $quote, $key, $pin) {
            $customers = Supplier::whereIn('id', [$sender->id, $p['recipient']])->orderBy('id')->lockForUpdate()->get();
            $accounts = ShomriddhiAccount::whereIn('id', [$p['source'], $p['target']])->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $source = $accounts->get($p['source']);
            $target = $accounts->get($p['target']);
            $existing = MembershipTransfer::where('sender_id', $sender->id)->where('idempotency_key', $key)->first();
            if ($existing) {
                if (! hash_equals($existing->quote_hash, hash('sha256', $quote))) {
                    $this->fail('Request key already used.', 'অনুরোধটি আগে ব্যবহার হয়েছে।');
                }

                return $existing;
            }
            if ($customers->count() !== 2 || $customers->contains(fn ($c) => $c->type !== 'Customer' || $c->status != 1)) {
                $this->fail('Member account is inactive.', 'মেম্বারের হিসাব নিষ্ক্রিয়।');
            }
            if (MembershipTransfer::where('sender_id', $sender->id)->where('quote_hash', hash('sha256', $quote))->exists()) {
                $this->fail('This reviewed transfer was already completed. Review a new transfer.', 'এই ট্রান্সফার ইতিমধ্যে সম্পন্ন হয়েছে। নতুন ট্রান্সফার পর্যালোচনা করুন।');
            }
            $lockedSender = $customers->firstWhere('id', $sender->id);
            if (! $lockedSender->pin || ! Hash::check($pin, $lockedSender->pin)) {
                $this->fail('Incorrect PIN.', 'পিন সঠিক নয়।');
            }
            $s = $this->config();
            if ($p['expires'] < now()->timestamp || $p['version'] !== $this->version($s)) {
                $this->fail('Review a fresh transfer quote.', 'আবার ট্রান্সফারের তথ্য পর্যালোচনা করুন।');
            }
            if (! $source || ! $target || $source->status !== 'Active' || $target->status !== 'Active' || $source->customer_id != $sender->id || $target->customer_id != $p['recipient']) {
                $this->fail('Savings account is unavailable.', 'সঞ্চয় হিসাব পাওয়া যায়নি।');
            }
            $total = $p['amount'] + $p['fee'];
            if (CashoutService::available($source) < $total) {
                $this->fail('Insufficient available savings balance.', 'ব্যবহারযোগ্য সঞ্চয় ব্যালেন্স পর্যাপ্ত নয়।');
            }
            $origin = (int) $source->origin_sister_concern_id;
            $destination = (int) $target->origin_sister_concern_id;
            $branches = SisterConcern::whereIn('id', [$origin, $destination])->orderBy('id')->lockForUpdate()->get();
            if ($branches->count() !== count(array_unique([$origin, $destination])) || $branches->contains(fn ($b) => $b->status != 1)) {
                $this->fail('Branch is inactive.', 'শাখা নিষ্ক্রিয়।');
            }
            User::findOrFail($s->posting_user_id);
            $head = function ($id) {
                $heads = AccountHead::where('supplier_id', $id)->where('head_type', 'savings')->where('status', 1)->where('nature', 'Credit')->get();
                if ($heads->count() !== 1) {
                    $this->fail('Savings ledger is missing or ambiguous.', 'সঞ্চয় লেজার পাওয়া যায়নি অথবা একাধিক আছে।');
                }

                return $heads->first();
            };
            $from = $head($sender->id);
            $to = $head($p['recipient']);
            if (($from->sister_concern_id && $from->sister_concern_id != $origin) || ($to->sister_concern_id && $to->sister_concern_id != $destination)) {
                $this->fail('Savings ledger branch does not match the account.', 'সঞ্চয় লেজারের শাখা হিসাবের সাথে মিলছে না।');
            }
            $t = MembershipTransfer::create(['reference' => 'SM-'.Str::upper((string) Str::ulid()), 'sender_id' => $sender->id, 'recipient_id' => $p['recipient'], 'sender_account_id' => $source->id, 'recipient_account_id' => $target->id, 'idempotency_key' => $key, 'quote_hash' => hash('sha256', $quote), 'amount' => CashoutService::money($p['amount']), 'fee' => CashoutService::money($p['fee']), 'total' => CashoutService::money($total)]);
            $v = $this->voucher($origin, $p['amount'], $s->posting_user_id, $t->reference, 28);
            $this->entry($v, $from->id, 1, $p['amount']);
            $receiver = $v;
            if ($origin === $destination) {
                $this->entry($v, $to->id, 2, $p['amount']);
            } else {
                $payable = AccountHead::where('reserved_id', 5)->whereNull('sister_concern_id')->where('status', 1)->firstOrFail();
                $receivable = AccountHead::where('reserved_id', 4)->whereNull('sister_concern_id')->where('status', 1)->firstOrFail();
                $this->entry($v, $payable->id, 2, $p['amount']);
                $receiver = $this->voucher($destination, $p['amount'], $s->posting_user_id, $t->reference, 28);
                $this->entry($receiver, $receivable->id, 1, $p['amount']);
                $this->entry($receiver, $to->id, 2, $p['amount']);
                $settlement = InterCompanySettlement::create(['payment_no' => $t->reference, 'from_sister_concern_id' => $origin, 'to_sister_concern_id' => $destination, 'date' => $v->date, 'amount' => $t->amount, 'sender_voucher_id' => $v->id, 'receiver_voucher_id' => $receiver->id, 'status' => 2, 'create_user_id' => $s->posting_user_id, 'received_user_id' => $s->posting_user_id, 'notes' => 'Member Send Money '.$t->reference]);
                $t->settlement_id = $settlement->id;
            }
            $this->log($sender->id, 'withdraw', $v, $p['amount']);
            $this->log($p['recipient'], 'deposit', $receiver, $p['amount']);
            if ($p['fee']) {
                $income = AccountHead::with('accountGroup')->find($s->income_head_id);
                if (! $income || $income->status != 1 || $income->nature !== 'Credit' || $income->sister_concern_id || $income->accountGroup?->top_reserved_id != 4) {
                    $this->fail('Charge income ledger is invalid.', 'চার্জের আয় লেজার সঠিক নয়।');
                }
                $fee = $this->voucher($origin, $p['fee'], $s->posting_user_id, $t->reference.' charge', 29);
                $this->entry($fee, $from->id, 1, $p['fee']);
                $this->entry($fee, $income->id, 2, $p['fee']);
                $this->log($sender->id, 'withdraw', $fee, $p['fee']);
                $t->fee_voucher_id = $fee->id;
            }
            $t->voucher_id = $v->id;
            $t->recipient_voucher_id = $receiver->id;
            $t->save();
            $source->principal_balance = CashoutService::money(CashoutService::minor($source->principal_balance) - $total);
            $source->save();
            $target->principal_balance = CashoutService::money(CashoutService::minor($target->principal_balance) + $p['amount']);
            $target->save();

            return $t;
        }, 3);
    }

    private function voucher(int $branch, int $amount, int $actor, string $note, int $purpose): Voucher
    {
        $no = generateVoucherNo(1, 'vouchers', $branch);

        return Voucher::create(['sister_concern_id' => $branch, 'voucher_type' => 1, 'amount' => CashoutService::money($amount), 'date' => SisterConcern::findOrFail($branch)->business_date ?: now()->toDateString(), 'voucher_no' => $no, 'voucher_no_group_sl' => (int) filter_var($no, FILTER_SANITIZE_NUMBER_INT), 'notes' => $note, 'user_id' => $actor, 'payment_purpose' => $purpose]);
    }

    private function entry(Voucher $v, int $head, int $type, int $amount): void
    {
        Transaction::create(['sister_concern_id' => $v->sister_concern_id, 'voucher_id' => $v->id, 'voucher_type' => 1, 'account_head_id' => $head, 'transaction_type' => $type, 'amount' => CashoutService::money($amount), 'date' => $v->date, 'user_id' => $v->user_id, 'notes' => $v->notes, 'voucher_no' => $v->voucher_no, 'voucher_no_group_sl' => $v->voucher_no_group_sl, 'payment_purpose' => $v->payment_purpose]);
    }

    private function log(int $customer, string $type, Voucher $v, int $amount): void
    {
        ShomriddhiTransaction::create(['customer_id' => $customer, 'type' => $type, 'amount' => CashoutService::money($amount), 'date' => $v->date, 'note' => $v->notes, 'status' => 'Approved', 'voucher_id' => $v->id, 'created_by' => $v->user_id, 'approved_by' => $v->user_id, 'approved_at' => now()]);
    }
}
