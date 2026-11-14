<?php

namespace App\Services\Membership;

use App\Enumeration\ReservedAccountHead;
use App\Models\Account\AccountHead;
use App\Models\Account\InterCompanySettlement;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\BranchManagement\SisterConcern;
use App\Models\MembershipCashout;
use App\Models\MembershipCashoutBooth;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CashoutService
{
    public const PURPOSE = 26;

    public const FEE_PURPOSE = 27;

    public static function minor($value): int
    {
        $value = is_float($value) ? number_format($value, 2, '.', '') : (string) $value;
        $negative = str_starts_with($value, '-');
        $parts = explode('.', ltrim($value, '-'));
        $number = ((int) $parts[0] * 100) + (int) substr(str_pad($parts[1] ?? '', 2, '0'), 0, 2);

        return $negative ? -$number : $number;
    }

    public static function money(int $minor): string
    {
        return number_format($minor / 100, 2, '.', '');
    }

    public static function fail(string $english, string $bengali = ''): never
    {
        $message = request()->getPreferredLanguage(['en', 'bn']) === 'bn' && $bengali ? $bengali : $english;
        throw ValidationException::withMessages(['cashout' => $message]);
    }

    public static function minimumBalance(): int
    {
        return max(0, self::minor(membership_transaction_setting('membership.account_hold_amount', '0.00')));
    }

    public static function available(ShomriddhiAccount $account, ?int $excludeCashoutId = null): int
    {
        return max(0, self::minor($account->principal_balance) - self::minimumBalance()
            - MembershipCashout::held($account->id, $excludeCashoutId));
    }

    private function account(Supplier $customer, bool $lock = false): ShomriddhiAccount
    {
        $query = ShomriddhiAccount::where('customer_id', $customer->id)->where('status', 'Active');
        if ($lock) {
            $query->lockForUpdate();
        }
        $account = $query->first();
        if (! $account) {
            self::fail('No active savings account is available.', 'সক্রিয় সঞ্চয় হিসাব পাওয়া যায়নি।');
        }

        return $account;
    }

    private function config(): object
    {
        $setting = membership_transaction_settings('cashout');
        if (! $setting->enabled) {
            self::fail('Cash-out is currently unavailable.', 'ক্যাশ আউট বর্তমানে বন্ধ আছে।');
        }

        return $setting;
    }

    private function version(object $setting): string
    {
        return hash('sha256', json_encode([$setting->minimum_amount, $setting->maximum_amount, $setting->charge_type,
            $setting->charge_rate, $setting->hold_minutes, $setting->income_head_id]));
    }

    private function booth(string $code): MembershipCashoutBooth
    {
        $token = str_starts_with($code, 'tori:cashout:') ? substr($code, 13) : '';
        $booth = MembershipCashoutBooth::with('branch')->where('qr_token', $token)->where('enabled', true)->first();
        if (! $booth || ! $booth->branch || $booth->branch->status != 1) {
            self::fail('Invalid or inactive branch QR code.', 'শাখার কিউআর কোড ভুল অথবা নিষ্ক্রিয়।');
        }

        return $booth;
    }

    public function quote(Supplier $customer, string $code, string $amount): array
    {
        $setting = $this->config();
        $booth = $this->booth($code);
        $account = $this->account($customer);
        $net = self::minor($amount);
        if ($net < max(1, self::minor($setting->minimum_amount)) || $net > self::minor($setting->maximum_amount)) {
            self::fail('Amount is outside the configured minimum and maximum per transaction.', 'পরিমাণ প্রতি লেনদেনের নির্ধারিত সর্বনিম্ন ও সর্বোচ্চ সীমার বাইরে।');
        }
        $fee = $setting->charge_type === 'percentage'
            ? intdiv($net * self::minor($setting->charge_rate) + 5000, 10000)
            : self::minor($setting->charge_rate);
        if ($net + $fee > self::available($account)) {
            self::fail('Insufficient available savings balance.', 'ব্যবহারযোগ্য সঞ্চয় ব্যালেন্স পর্যাপ্ত নয়।');
        }
        $quote = ['customer_id' => $customer->id, 'account_id' => $account->id,
            'branch_id' => $booth->branch_id, 'code' => $code, 'version' => $this->version($setting),
            'amount' => self::money($net), 'fee' => self::money($fee), 'total' => self::money($net + $fee),
            'expires' => now()->addMinutes(2)->timestamp];

        return ['quote' => Crypt::encryptString(json_encode($quote)), 'branch' => $booth->branch->name,
            'amount' => $quote['amount'], 'fee' => $quote['fee'], 'total' => $quote['total'],
            'available_balance' => self::money(self::available($account)), 'minimum_amount' => $setting->minimum_amount, 'maximum_amount' => $setting->maximum_amount];
    }

    public function request(Supplier $customer, string $token, string $pin, string $key): MembershipCashout
    {
        if (! $customer->pin || ! Hash::check($pin, $customer->pin)) {
            self::fail('Incorrect PIN.', 'ভুল পিন দিয়েছেন।');
        }
        try {
            $quote = json_decode(Crypt::decryptString($token), true, flags: JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            self::fail('Invalid quote. Scan the branch QR again.', 'কোটেশন ভুল। শাখার কিউআর আবার স্ক্যান করুন।');
        }
        if ($quote['customer_id'] !== $customer->id) {
            abort(403);
        }

        return DB::transaction(function () use ($customer, $quote, $token, $key) {
            $account = $this->account($customer, true);
            $existing = MembershipCashout::where('customer_id', $customer->id)->where('idempotency_key', $key)->first();
            if ($existing) {
                abort_unless(hash_equals($existing->quote_hash, hash('sha256', $token)), 409, 'Request key was already used for another quote.');

                return $existing;
            }
            $setting = $this->config();
            $booth = $this->booth($quote['code']);
            if ($quote['expires'] < now()->timestamp || $quote['version'] !== $this->version($setting)) {
                self::fail('Quote expired or settings changed. Review a new quote.', 'কোটেশনের মেয়াদ শেষ অথবা সেটিং পরিবর্তন হয়েছে। নতুন কোটেশন নিন।');
            }
            if ($account->id !== $quote['account_id'] || self::minor($quote['total']) > self::available($account)) {
                self::fail('Insufficient available savings balance.', 'ব্যবহারযোগ্য সঞ্চয় ব্যালেন্স পর্যাপ্ত নয়।');
            }

            return MembershipCashout::create([
                'reference' => 'CO-'.Str::ulid(), 'customer_id' => $customer->id, 'account_id' => $account->id,
                'branch_id' => $booth->branch_id, 'cash_head_id' => $booth->cash_head_id,
                'income_head_id' => $setting->income_head_id, 'idempotency_key' => $key,
                'quote_hash' => hash('sha256', $token), 'amount' => $quote['amount'], 'fee' => $quote['fee'],
                'total' => $quote['total'], 'charge_type' => $setting->charge_type, 'charge_rate' => $setting->charge_rate,
                'status' => 'Pending', 'expires_at' => now()->addMinutes($setting->hold_minutes),
            ]);
        }, 3);
    }

    public function change(MembershipCashout $cashout, string $action, ?User $operator = null, ?string $reason = null): MembershipCashout
    {
        if ($operator) {
            abort_unless($operator->activeConcernId() == $cashout->branch_id, 403);
            abort_unless($operator->hasPermissionTo($action === 'reject'
                ? 'shomriddhi_advance_transaction_reject' : 'shomriddhi_advance_transaction_approve'), 403);
        } else {
            abort_unless($action === 'cancel', 403);
        }

        return DB::transaction(function () use ($cashout, $action, $operator, $reason) {
            $account = ShomriddhiAccount::whereKey($cashout->account_id)->lockForUpdate()->firstOrFail();
            $cashout = MembershipCashout::whereKey($cashout->id)->lockForUpdate()->firstOrFail();
            if ($action === 'complete' && $cashout->status === 'Completed') {
                return $cashout;
            }
            if ($action === 'cancel' && $cashout->status === 'Cancelled') {
                return $cashout;
            }
            if ($action === 'accept' && $cashout->status === 'Accepted') {
                return $cashout;
            }
            if ($action === 'cancel') {
                if ($cashout->effectiveStatus() !== 'Pending') {
                    self::fail('Only a pending request can be cancelled.', 'শুধু অপেক্ষমাণ অনুরোধ বাতিল করা যাবে।');
                }
                $cashout->update(['status' => 'Cancelled']);
            } elseif ($action === 'reject') {
                if (! in_array($cashout->status, ['Pending', 'Accepted'], true)) {
                    self::fail('Request is already closed.');
                }
                $cashout->update(['status' => 'Rejected', 'closed_by' => $operator->id, 'reason' => $reason]);
            } elseif ($action === 'accept') {
                if ($cashout->effectiveStatus() !== 'Pending') {
                    self::fail('Request expired or is no longer pending.');
                }
                $this->validatePayout($cashout, $account);
                $cashout->update(['status' => 'Accepted', 'accepted_by' => $operator->id, 'accepted_at' => now()]);
            } elseif ($action === 'complete') {
                if ($cashout->status !== 'Accepted') {
                    self::fail('Accept the request before cash payment.');
                }
                $this->validatePayout($cashout, $account);
                $this->post($cashout, $account, $operator);
            } else {
                abort(422);
            }

            return $cashout->fresh();
        }, 3);
    }

    private function validatePayout(MembershipCashout $cashout, ShomriddhiAccount $account): void
    {
        if (! $cashout->branch || $cashout->branch->status != 1) {
            self::fail('Cash-out branch is inactive.');
        }
        if ($account->status !== 'Active' || ! $cashout->customer || $cashout->customer->status != 1) {
            self::fail('Savings account or member is inactive.');
        }
        if (self::available($account, $cashout->id) < self::minor($cashout->total)) {
            self::fail('Insufficient available savings balance after minimum balance and cash-out holds.', 'ন্যূনতম ব্যালেন্স ও ক্যাশ আউটের সংরক্ষিত টাকা বাদে ব্যবহারযোগ্য সঞ্চয় পর্যাপ্ত নয়।');
        }
        $head = AccountHead::whereKey($cashout->cash_head_id)->lockForUpdate()->first();
        if (! $head || $head->status != 1 || $head->payment_mode != 2 || $head->nature !== 'Debit'
            || ($head->sister_concern_id && $head->sister_concern_id != $cashout->branch_id)) {
            self::fail('Branch cash account is invalid.');
        }
        $otherReserved = MembershipCashout::where('cash_head_id', $head->id)->where('branch_id', $cashout->branch_id)
            ->where('status', 'Accepted')->whereKeyNot($cashout->id)->sum('amount');
        if (self::minor($head->getCurrentBalance($cashout->branch_id)) - self::minor($otherReserved) < self::minor($cashout->amount)) {
            self::fail('Branch cash balance is insufficient.');
        }
    }

    private function voucher(int $branch, int $type, int $amount, User $operator, string $note, int $purpose, ?int $cashHead = null, ?int $payeeHead = null): Voucher
    {
        $number = generateVoucherNo($type, 'vouchers', $branch);

        return Voucher::create(['sister_concern_id' => $branch, 'voucher_type' => $type,
            'amount' => self::money($amount), 'date' => SisterConcern::findOrFail($branch)->business_date ?: now()->toDateString(),
            'voucher_no' => $number, 'voucher_no_group_sl' => (int) filter_var($number, FILTER_SANITIZE_NUMBER_INT),
            'notes' => $note, 'user_id' => $operator->id, 'payment_purpose' => $purpose,
            'account_head_payee_depositor_id' => $payeeHead,
            'payment_account_head_id' => $cashHead, 'payment_type_id' => $cashHead ? 2 : null]);
    }

    private function entry(Voucher $voucher, int $head, int $type, int $amount): void
    {
        Transaction::create(['sister_concern_id' => $voucher->sister_concern_id, 'voucher_id' => $voucher->id,
            'account_head_id' => $head, 'voucher_type' => $voucher->voucher_type, 'transaction_type' => $type,
            'account_head_payee_depositor_id' => $voucher->account_head_payee_depositor_id,
            'payment_account_head_id' => $voucher->payment_account_head_id, 'payment_type_id' => $voucher->payment_type_id,
            'amount' => self::money($amount), 'date' => $voucher->date, 'user_id' => $voucher->user_id,
            'notes' => $voucher->notes, 'voucher_no' => $voucher->voucher_no,
            'voucher_no_group_sl' => $voucher->voucher_no_group_sl, 'payment_purpose' => $voucher->payment_purpose]);
    }

    private function withdrawal(MembershipCashout $cashout, Voucher $voucher, int $amount): void
    {
        ShomriddhiTransaction::create(['customer_id' => $cashout->customer_id, 'type' => 'withdraw',
            'amount' => self::money($amount), 'date' => $voucher->date, 'note' => $voucher->notes,
            'status' => 'Approved', 'voucher_id' => $voucher->id, 'created_by' => $voucher->user_id,
            'approved_by' => $voucher->user_id, 'approved_at' => now()]);
    }

    private function post(MembershipCashout $cashout, ShomriddhiAccount $account, User $operator): void
    {
        $origin = (int) $account->origin_sister_concern_id;
        $branch = (int) $cashout->branch_id;
        SisterConcern::whereIn('id', [$origin, $branch])->orderBy('id')->lockForUpdate()->get();
        $savings = AccountHead::where('supplier_id', $cashout->customer_id)->where('head_type', 'savings')->where('status', 1)->first();
        if (! $savings || $savings->nature !== 'Credit') {
            self::fail('Customer savings ledger head is missing.');
        }
        $net = self::minor($cashout->amount);
        $fee = self::minor($cashout->fee);
        $total = $net + $fee;
        $income = $fee ? AccountHead::find($cashout->income_head_id) : null;
        if ($fee && (! $income || $income->status != 1 || $income->nature !== 'Credit'
            || $income->accountGroup?->top_reserved_id != 4 || ($income->sister_concern_id && $income->sister_concern_id != $branch))) {
            self::fail('Cash-out charge income head is invalid.');
        }
        $note = $cashout->reference.' cash-out '.self::money($net).' + charge '.self::money($fee);
        $payoutDebit = $savings->id;
        $source = null;
        if ($origin !== $branch) {
            $receivable = AccountHead::where('reserved_id', ReservedAccountHead::INTER_COMPANY_RECEIVABLE)->where('status', 1)->whereNull('sister_concern_id')->first();
            $payable = AccountHead::where('reserved_id', ReservedAccountHead::INTER_COMPANY_PAYABLE)->where('status', 1)->whereNull('sister_concern_id')->first();
            if (! $receivable || ! $payable) {
                self::fail('Inter-branch receivable/payable heads are missing.');
            }
            $source = $this->voucher($origin, 1, $total, $operator, $note.' (origin branch)', self::PURPOSE, null, $savings->id);
            $this->entry($source, $savings->id, 1, $total);
            $this->entry($source, $payable->id, 2, $total);
            $this->withdrawal($cashout, $source, $total);
            $payoutDebit = $receivable->id;
        }
        $payout = $this->voucher($branch, 2, $net, $operator, $note.' (cash paid)', self::PURPOSE, $cashout->cash_head_id, $savings->id);
        $this->entry($payout, $payoutDebit, 1, $net);
        $this->entry($payout, $cashout->cash_head_id, 2, $net);
        if (! $source) {
            $this->withdrawal($cashout, $payout, $net);
        }
        $feeVoucher = null;
        if ($fee) {
            $feeVoucher = $this->voucher($branch, 1, $fee, $operator, $note.' (charge)', self::FEE_PURPOSE, null, $savings->id);
            $this->entry($feeVoucher, $payoutDebit, 1, $fee);
            $this->entry($feeVoucher, $income->id, 2, $fee);
            if (! $source) {
                $this->withdrawal($cashout, $feeVoucher, $fee);
            }
        }
        $settlement = $source ? InterCompanySettlement::create([
            'payment_no' => $cashout->reference, 'from_sister_concern_id' => $origin, 'to_sister_concern_id' => $branch,
            'date' => $payout->date, 'amount' => self::money($total), 'sender_voucher_id' => $source->id,
            'receiver_voucher_id' => $payout->id, 'status' => 2, 'create_user_id' => $operator->id,
            'received_user_id' => $operator->id, 'notes' => $note,
        ]) : null;
        // Release this hold and debit principal in the same DB transaction.
        $cashout->update(['status' => 'Completed', 'completed_by' => $operator->id, 'completed_at' => now(),
            'voucher_id' => $payout->id, 'fee_voucher_id' => $feeVoucher?->id,
            'source_voucher_id' => $source?->id, 'settlement_id' => $settlement?->id]);
        $account->principal_balance = self::money(self::minor($account->principal_balance) - $total);
        $account->save();
    }
}
