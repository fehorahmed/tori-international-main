<?php

namespace App\Services\ShomriddhiFdr;

use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\BranchManagement\SisterConcern;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\ShomriddhiFdr\FdrAccount;
use App\Models\ShomriddhiFdr\FdrEncashmentRequest;
use App\Models\ShomriddhiFdr\FdrScheme;
use App\Models\ShomriddhiFdr\FdrTransaction;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Membership\CashoutService;
use App\Services\Membership\KycEligibilityService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class FdrAccountService
{
    public const FREQUENCIES = ['Monthly' => 1, 'Quarterly' => 3, 'Half-Yearly' => 6, 'Yearly' => 12, 'At Maturity' => 0];

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['fdr' => $message]);
    }

    public function member(int $id, bool $kyc = false): Supplier
    {
        $m = Supplier::whereKey($id)->where('type', 'Customer')->where('status', 1)->lockForUpdate()->first();
        if (! $m) {
            $this->fail('Active membership is required.');
        }
        if ($kyc && KycEligibilityService::status($m) !== 'Verified') {
            $this->fail('Approved KYC is required for FDR.');
        }

        return $m;
    }

    public function savings(int $customer): ShomriddhiAccount
    {
        $rows = ShomriddhiAccount::where('customer_id', $customer)->where('status', 'Active')->lockForUpdate()->get();
        if ($rows->count() !== 1) {
            $this->fail('Exactly one active savings account is required.');
        }

        return $rows->first();
    }

    public function branch(int $id): SisterConcern
    {
        $b = SisterConcern::whereKey($id)->where('status', 1)->lockForUpdate()->first();
        if (! $b) {
            $this->fail('An active branch is required.');
        }

        return $b;
    }

    private function pin(Supplier $m, string $pin): void
    {
        if (! $m->pin || ! Hash::check($pin, $m->pin)) {
            $this->fail('Incorrect transaction PIN.');
        }
    }

    public function termInterest($principal, $rate, int $months): int
    {
        return (int) round(CashoutService::minor($principal) * CashoutService::minor($rate) * $months / 120000);
    }

    public function nextDate(FdrAccount $a): string
    {
        $months = self::FREQUENCIES[$a->interest_payout_frequency] ?? 0;

        return Carbon::parse($a->opening_date)->addMonthsNoOverflow($months ?: $a->duration_months)->min(Carbon::parse($a->maturity_date))->toDateString();
    }

    public function termsHash(FdrScheme $s): string
    {
        return hash('sha256', json_encode([(int) $s->id, (int) $s->duration, CashoutService::minor($s->interest_rate), CashoutService::minor($s->penalty_rate ?? 0), CashoutService::minor($s->min_amount), CashoutService::minor($s->max_amount ?? 0), $s->status, (bool) $s->visible_on_app]));
    }

    public function schemeQuote(FdrScheme $s, string $amount): array
    {
        $n = CashoutService::minor($amount);
        if ($s->status !== 'Active' || ! $s->visible_on_app) {
            $this->fail('This scheme is not available in the app.');
        }
        if ($n <= 0 || $n < CashoutService::minor($s->min_amount) || ($s->max_amount && $n > CashoutService::minor($s->max_amount))) {
            $this->fail('Amount is outside the scheme minimum/maximum.');
        }

        return ['principal' => CashoutService::money($n), 'term_interest' => CashoutService::money($this->termInterest($amount, $s->interest_rate, $s->duration)),
            'total_term_value' => CashoutService::money($n + $this->termInterest($amount, $s->interest_rate, $s->duration)),
            'terms' => 'Premature closure earns no interest. Previously paid interest and the scheme penalty are recovered from settlement.'];
    }

    public function openFromSavings(Supplier $customer, array $d): FdrAccount
    {
        return DB::transaction(function () use ($customer, $d) {
            $m = $this->member($customer->id, true);
            $this->pin($m, $d['pin']);
            $hash = hash('sha256', json_encode([$d['scheme_id'], CashoutService::minor($d['amount']), $d['frequency'], $d['auto_renew'] ?? false, $d['nominees'] ?? []]));
            $old = FdrAccount::withTrashed()->where('customer_id', $m->id)->where('idempotency_key', $d['idempotency_key'])->first();
            if ($old) {
                if ($old->trashed() || $old->request_hash !== $hash) {
                    $this->fail('This request key was used for different FDR details.');
                }

                return $old;
            }
            $savings = $this->savings($m->id);
            $branch = $this->branch($savings->origin_sister_concern_id);
            $scheme = FdrScheme::whereKey($d['scheme_id'])->lockForUpdate()->firstOrFail();
            $q = $this->schemeQuote($scheme, $d['amount']);
            if (! hash_equals($this->termsHash($scheme), $d['terms_hash'] ?? '')) {
                $this->fail('Scheme terms changed. Review a fresh FDR preview.');
            }
            $amount = CashoutService::minor($d['amount']);
            if (CashoutService::available($savings) < $amount) {
                $this->fail('Insufficient available savings balance. Protected minimum and reserved funds cannot be spent.');
            }
            $nominees = $d['nominees'] ?? [];
            if ($nominees && (abs(array_sum(array_column($nominees, 'share_percentage')) - 100) > 0.001 || count(array_unique(array_column($nominees, 'id'))) !== count($nominees))) {
                $this->fail('Distinct nominees must have a total share of 100%.');
            }
            foreach ($nominees as $nom) {
                if (! DB::table('nominees')->where('id', $nom['id'])->where('customer_id', $m->id)->whereNull('deleted_at')->exists()) {
                    $this->fail('Select your own nominee.');
                }
            }
            $serial = FdrAccount::withTrashed()->where('customer_id', $m->id)->count() + 1;
            do {
                $no = $savings->account_number.'-F'.str_pad((string) $serial++, 2, '0', STR_PAD_LEFT);
            } while (FdrAccount::withTrashed()->where('fdr_no', $no)->exists());
            $a = FdrAccount::create(['fdr_no' => $no, 'customer_id' => $m->id, 'scheme_id' => $scheme->id, 'origin_sister_concern_id' => $branch->id,
                'principal_amount' => $q['principal'], 'interest_rate' => $scheme->interest_rate, 'duration_months' => $scheme->duration, 'penalty_rate' => $scheme->penalty_rate ?? 0,
                'opening_date' => $branch->business_date, 'maturity_date' => Carbon::parse($branch->business_date)->addMonthsNoOverflow($scheme->duration)->toDateString(),
                'payable_amount_at_maturity' => $q['total_term_value'], 'interest_payout_frequency' => $d['frequency'], 'auto_renew' => $d['auto_renew'] ?? false,
                'status' => 'Pending', 'funding_source' => 'savings', 'savings_account_id' => $savings->id, 'idempotency_key' => $d['idempotency_key'], 'request_hash' => $hash, 'created_by' => null]);
            foreach ($nominees as $nom) {
                DB::table('fdr_nominees')->insert(['fdr_account_id' => $a->id, 'nominee_id' => $nom['id'], 'share_percentage' => $nom['share_percentage'], 'created_at' => now(), 'updated_at' => now()]);
            }
            $a->next_interest_date = $this->nextDate($a);
            $a->save();
            $actor = $this->systemActor();
            $this->fund($a, $savings, $this->savingsHead($m->id, $branch->id), $actor, true);

            return $a->fresh();
        }, 3);
    }

    public function approve(FdrAccount $account, int $paymentAccountHeadId, string $source = 'cash_bank'): array
    {
        return DB::transaction(function () use ($account, $paymentAccountHeadId, $source) {
            $m = $this->member($account->customer_id, $source === 'savings');
            $savings = $this->savings($m->id);
            $b = $this->branch($account->origin_sister_concern_id);
            $a = FdrAccount::whereKey($account->id)->lockForUpdate()->firstOrFail();
            $this->adminBranch($a);
            if ($a->status !== 'Pending') {
                $this->fail('Only pending FDR accounts can be approved.');
            }
            if ($savings->origin_sister_concern_id != $b->id) {
                $this->fail('Savings and FDR branches must match.');
            }
            $a->funding_source = $source;
            $a->savings_account_id = $savings->id;
            $head = $source === 'savings' ? $this->savingsHead($m->id, $b->id) : $this->paymentHead($paymentAccountHeadId, $b->id);
            $this->fund($a, $savings, $head, auth()->id(), $source === 'savings');

            return ['success' => true, 'message' => 'FDR approved and funded.'];
        }, 3);
    }

    private function fund(FdrAccount $a, ShomriddhiAccount $s, AccountHead $head, int $actor, bool $fromSavings): void
    {
        if (substr((string) $a->opening_date, 0, 10) !== $this->branch($a->origin_sister_concern_id)->business_date) {
            $this->fail('Set the pending FDR opening date to the current Bangladesh date before approval.');
        }
        $n = CashoutService::minor($a->principal_amount);
        if ($n <= 0 || ($fromSavings && CashoutService::available($s) < $n)) {
            $this->fail('Insufficient available savings balance.');
        }
        $v = $this->voucher($a, $n, $actor, 'FDR opening '.$a->fdr_no, [$head->id => [1, $n], $this->fdrHead($a)->id => [2, $n]], $fromSavings ? 1 : 3);
        if ($fromSavings) {
            $this->savingsMovement($s, 'withdraw', $n, $v, $actor);
        }
        FdrTransaction::create(['fdr_account_id' => $a->id, 'type' => 'Deposit', 'amount' => $a->principal_amount, 'date' => $v->date, 'created_by' => $actor, 'voucher_id' => $v->id, 'posting_key' => 'open:'.$a->id, 'note' => 'Initial FDR deposit']);
        $a->status = 'Active';
        $a->approved_by = $actor;
        $a->approved_at = now();
        $a->save();
    }

    public function adminBranch(FdrAccount $a): void
    {
        abort_unless(auth()->user() && $a->origin_sister_concern_id == auth()->user()->activeConcernId(), 403);
    }

    public function settlement(FdrAccount $a, string $date): array
    {
        if (! $a->duration_months || $a->interest_rate === null) {
            $this->fail('Historical FDR terms require branch review.');
        }
        if ($date < substr((string) $a->opening_date, 0, 10)) {
            $this->fail('Current date is earlier than FDR opening date.');
        }
        if (FdrTransaction::where('fdr_account_id', $a->id)->whereDate('date', '>', $date)->exists()) {
            $this->fail('Current date is earlier than existing FDR postings. Reconciliation required.');
        }
        $mature = $date >= substr((string) $a->maturity_date, 0, 10);
        $principal = CashoutService::minor($a->principal_amount);
        $earned = $mature ? $this->termInterest($a->principal_amount, $a->interest_rate, $a->duration_months) : 0;
        $paid = CashoutService::minor(FdrTransaction::where('fdr_account_id', $a->id)->where('type', 'Interest Payout')->whereDate('date', '>=', $a->opening_date)->where(function ($q) use ($a) {
            $q->where('period_start', $a->opening_date)->orWhereNull('period_start');
        })->sum('amount'));
        $penalty = $mature ? 0 : (int) round($principal * CashoutService::minor($a->penalty_rate ?? 0) / 10000);
        $net = $principal + $earned - $paid - $penalty;
        if ($net < 0) {
            $this->fail('Settlement would be negative. Branch reconciliation is required.');
        }

        return ['date' => $date, 'is_premature' => ! $mature, 'principal' => CashoutService::money($principal), 'earned_interest' => CashoutService::money($earned),
            'paid_interest' => CashoutService::money($paid), 'interest_adjustment' => CashoutService::money($earned - $paid), 'penalty_amount' => CashoutService::money($penalty), 'net_payable' => CashoutService::money($net)];
    }

    public function requestEncash(Supplier $customer, int $id, string $pin, string $key): FdrEncashmentRequest
    {
        return DB::transaction(function () use ($customer, $id, $pin, $key) {
            $m = $this->member($customer->id, true);
            $this->pin($m, $pin);
            $s = $this->savings($m->id);
            $b = $this->branch($s->origin_sister_concern_id);
            $a = FdrAccount::whereKey($id)->where('customer_id', $m->id)->lockForUpdate()->firstOrFail();
            $old = FdrEncashmentRequest::where('customer_id', $m->id)->where('idempotency_key', $key)->first();
            if ($old) {
                if ($old->fdr_account_id != $id) {
                    $this->fail('Request key already used.');
                }

                return $old;
            }
            if (! in_array($a->status, ['Active', 'Matured']) || $a->origin_sister_concern_id != $b->id) {
                $this->fail('FDR is unavailable for encashment.');
            }
            if (FdrEncashmentRequest::where('fdr_account_id', $id)->where('status', 'Pending')->exists()) {
                $this->fail('An encashment request is already pending.');
            }

            return FdrEncashmentRequest::create(['fdr_account_id' => $id, 'customer_id' => $m->id, 'origin_sister_concern_id' => $b->id, 'idempotency_key' => $key, 'requested_date' => $b->business_date, 'preview' => $this->settlement($a, $b->business_date)]);
        }, 3);
    }

    public function reviewEncash(int $id, bool $approve, ?string $note = null): FdrEncashmentRequest
    {
        $r = FdrEncashmentRequest::findOrFail($id);

        return DB::transaction(function () use ($r, $approve, $note) {
            $m = $approve ? $this->member($r->customer_id, true) : Supplier::whereKey($r->customer_id)->lockForUpdate()->firstOrFail();
            $s = $approve ? $this->savings($m->id) : null;
            $b = $this->branch($r->origin_sister_concern_id);
            $a = FdrAccount::whereKey($r->fdr_account_id)->lockForUpdate()->firstOrFail();
            $this->adminBranch($a);
            $request = FdrEncashmentRequest::whereKey($r->id)->lockForUpdate()->firstOrFail();
            if ($request->status !== 'Pending') {
                return $request;
            }
            if ($approve) {
                if ($s->origin_sister_concern_id != $b->id) {
                    $this->fail('Savings branch mismatch.');
                }
                $v = $this->close($a, $this->savingsHead($m->id, $b->id), auth()->id(), $s);
                $request->voucher_id = $v->id;
            }
            $request->status = $approve ? 'Approved' : 'Rejected';
            $request->reviewed_by = auth()->id();
            $request->reviewed_at = now();
            $request->review_note = $note;
            $request->save();

            return $request;
        }, 3);
    }

    public function encash(FdrAccount $account, array $data): array
    {
        return DB::transaction(function () use ($account, $data) {
            $m = $this->member($account->customer_id);
            $s = $this->savings($m->id);
            $b = $this->branch($account->origin_sister_concern_id);
            $a = FdrAccount::whereKey($account->id)->lockForUpdate()->firstOrFail();
            $this->adminBranch($a);
            if (FdrEncashmentRequest::where('fdr_account_id', $a->id)->where('status', 'Pending')->exists()) {
                $this->fail('Review the pending app encashment request first.');
            }
            $toSavings = ($data['payment_source'] ?? 'cash_bank') === 'savings';
            if ($toSavings && $s->origin_sister_concern_id != $b->id) {
                $this->fail('Savings branch mismatch.');
            }
            $this->close($a, $toSavings ? $this->savingsHead($m->id, $b->id) : $this->paymentHead((int) ($data['payment_mode'] ?? 0), $b->id), auth()->id(), $toSavings ? $s : null);

            return ['success' => true, 'message' => 'FDR encashed using server-calculated settlement.'];
        }, 3);
    }

    private function close(FdrAccount $a, AccountHead $target, int $actor, ?ShomriddhiAccount $savings): Voucher
    {
        if (! in_array($a->status, ['Active', 'Matured'])) {
            $this->fail('FDR is already closed or not active.');
        }
        $q = $this->settlement($a, $this->branch($a->origin_sister_concern_id)->business_date);
        $n = CashoutService::minor($q['net_payable']);
        $interest = CashoutService::minor($q['interest_adjustment']);
        $penalty = CashoutService::minor($q['penalty_amount']);
        $lines = [[$this->fdrHead($a)->id, 1, CashoutService::minor($a->principal_amount)]];
        if ($interest !== 0) {
            $lines[] = [$this->systemHead(46, 'FDR Interest Expense', 26, 'Debit')->id, $interest > 0 ? 1 : 2, abs($interest)];
        }
        if ($penalty) {
            $lines[] = [$this->systemHead(47, 'FDR Penalty Income', 23, 'Credit')->id, 2, $penalty];
        }
        if ($n) {
            $lines[] = [$target->id, 2, $n];
        }
        $v = $this->voucherLines($a, $n, $actor, 'FDR settlement '.$a->fdr_no, $lines, $savings ? 1 : 2);
        if ($savings && $n) {
            $this->savingsMovement($savings, 'deposit', $n, $v, $actor);
        }
        FdrTransaction::create(['fdr_account_id' => $a->id, 'type' => 'Encashment', 'amount' => $q['net_payable'], 'date' => $q['date'], 'note' => json_encode($q), 'created_by' => $actor, 'voucher_id' => $v->id, 'posting_key' => 'close:'.$a->id]);
        $a->update(['status' => 'Closed', 'next_interest_date' => null, 'closed_at' => now()]);

        return $v;
    }

    public function process(int $id): void
    {
        $initial = FdrAccount::findOrFail($id);
        DB::transaction(function () use ($initial) {
            $this->member($initial->customer_id);
            $s = $this->savings($initial->customer_id);
            $b = $this->branch($initial->origin_sister_concern_id);
            $a = FdrAccount::whereKey($initial->id)->lockForUpdate()->firstOrFail();
            if ($a->status !== 'Active') {
                return;
            }
            if ($s->origin_sister_concern_id != $b->id) {
                $this->fail('Savings branch mismatch.');
            }
            $today = $b->business_date;
            $actor = $this->systemActor();
            $limit = 0;
            while ($a->next_interest_date && substr((string) $a->next_interest_date, 0, 10) <= $today) {
                if (++$limit > 1200) {
                    $this->fail('Too many historical FDR periods. Reconciliation required.');
                }
                $due = substr((string) $a->next_interest_date, 0, 10);
                $start = substr((string) $a->opening_date, 0, 10);
                $end = substr((string) $a->maturity_date, 0, 10);
                $months = self::FREQUENCIES[$a->interest_payout_frequency] ?? 0;
                $elapsed = $due === $end ? $a->duration_months : (int) round(Carbon::parse($start)->diffInMonths(Carbon::parse($due)));
                $total = $this->termInterest($a->principal_amount, $a->interest_rate, $elapsed);
                $paid = CashoutService::minor(FdrTransaction::where('fdr_account_id', $a->id)->where('type', 'Interest Payout')->where('period_start', $start)->sum('amount'));
                if (FdrTransaction::where('fdr_account_id', $a->id)->where('type', 'Interest Payout')->whereNull('period_start')->exists()) {
                    $this->fail('Legacy payouts require period reconciliation before automatic processing.');
                }
                $amount = $total - $paid;
                if ($amount < 0) {
                    $this->fail('FDR payout exceeds term entitlement.');
                }
                if ($amount > 0) {
                    $v = $this->voucher($a, $amount, $actor, 'FDR interest '.$a->fdr_no.' due '.$due, [$this->systemHead(46, 'FDR Interest Expense', 26, 'Debit')->id => [1, $amount], $this->savingsHead($a->customer_id, $b->id)->id => [2, $amount]]);
                    $this->savingsMovement($s, 'deposit', $amount, $v, $actor);
                    FdrTransaction::create(['fdr_account_id' => $a->id, 'type' => 'Interest Payout', 'amount' => CashoutService::money($amount), 'date' => $v->date, 'created_by' => $actor, 'voucher_id' => $v->id, 'posting_key' => 'interest:'.$a->id.':'.$start.':'.$due, 'period_start' => $start, 'period_end' => $due, 'note' => 'Interest credited to savings']);
                }
                if ($due === $end) {
                    $pending = FdrEncashmentRequest::where('fdr_account_id', $a->id)->where('status', 'Pending')->exists();
                    if ($a->auto_renew && ! $pending) {
                        $a->opening_date = $end;
                        $a->maturity_date = Carbon::parse($end)->addMonthsNoOverflow($a->duration_months)->toDateString();
                        $a->renewal_count++;
                        $a->next_interest_date = $this->nextDate($a);
                    } else {
                        $a->status = 'Matured';
                        $a->next_interest_date = null;
                    }
                } else {
                    $nextMonths = min($a->duration_months, $elapsed + $months);
                    $a->next_interest_date = Carbon::parse($start)->addMonthsNoOverflow($nextMonths)->toDateString();
                }
                $a->save();
            }
        }, 3);
    }

    private function paymentHead(int $id, int $branch): AccountHead
    {
        $h = AccountHead::whereKey($id)->where('status', 1)->where('payment_mode', '>', 0)->first();
        if (! $h || ($h->sister_concern_id && $h->sister_concern_id != $branch)) {
            $this->fail('Invalid Cash/Bank payment account.');
        }

        return $h;
    }

    private function savingsHead(int $customer, int $branch): AccountHead
    {
        $rows = AccountHead::where('supplier_id', $customer)->where('head_type', 'savings')->where('status', 1)->where('nature', 'Credit')->get();
        if ($rows->count() !== 1 || ($rows->first()->sister_concern_id && $rows->first()->sister_concern_id != $branch)) {
            $this->fail('Savings ledger is missing, ambiguous or belongs to another branch.');
        }

        return $rows->first();
    }

    private function fdrHead(FdrAccount $a): AccountHead
    {
        $group = AccountGroup::where('reserved_id', 30)->lockForUpdate()->firstOrFail();
        $heads = AccountHead::where('supplier_id', $a->customer_id)->where('head_type', 'fdr')->get();
        if ($heads->count() > 1) {
            $this->fail('Ambiguous FDR liability ledger.');
        }
        $h = $heads->first() ?? AccountHead::create(['supplier_id' => $a->customer_id, 'head_type' => 'fdr', 'account_group_id' => $group->id, 'sister_concern_id' => null, 'name' => 'FDR - '.$a->customer_id, 'code' => generateAccountCode(2), 'nature' => 'Credit', 'status' => 1, 'is_editable' => 0]);
        if ($h->status != 1 || $h->nature !== 'Credit' || $h->account_group_id != $group->id || ($h->sister_concern_id && $h->sister_concern_id != $a->origin_sister_concern_id)) {
            $this->fail('Invalid FDR liability ledger.');
        }

        return $h;
    }

    private function systemHead(int $reserved, string $name, int $group, string $nature): AccountHead
    {
        $g = AccountGroup::where('reserved_id', $group)->lockForUpdate()->firstOrFail();
        $h = AccountHead::where('reserved_id', $reserved)->first() ?? AccountHead::create(['reserved_id' => $reserved, 'name' => $name, 'account_group_id' => $g->id, 'code' => generateAccountCode($nature === 'Debit' ? 5 : 4), 'nature' => $nature, 'status' => 1, 'is_system' => 1, 'is_editable' => 0]);
        if ($h->status != 1 || $h->nature !== $nature || $h->account_group_id != $g->id || $h->sister_concern_id) {
            $this->fail('Invalid FDR interest/penalty ledger.');
        }

        return $h;
    }

    private function systemActor(): int
    {
        $id = (int) (membership_transaction_settings('transfer')->posting_user_id ?? 0);
        if (! $id) {
            $id = 1;
        }
        User::findOrFail($id);

        return $id;
    }

    private function voucher(FdrAccount $a, int $n, int $actor, string $note, array $entries, int $type = 1): Voucher
    {
        $lines = [];
        foreach ($entries as $head => [$side,$amount]) {
            $lines[] = [$head, $side, $amount];
        }

        return $this->voucherLines($a, $n, $actor, $note, $lines, $type);
    }

    private function voucherLines(FdrAccount $a, int $n, int $actor, string $note, array $lines, int $type): Voucher
    {
        $dr = 0;
        $cr = 0;
        foreach ($lines as [$h,$side,$value]) {
            $side === 1 ? $dr += $value : $cr += $value;
        }
        if ($dr !== $cr) {
            $this->fail('FDR ledger is not balanced.');
        }
        $b = $this->branch($a->origin_sister_concern_id);
        $no = generateVoucherNo($type, 'vouchers', $b->id);
        $v = Voucher::create(['sister_concern_id' => $b->id, 'voucher_type' => $type, 'amount' => CashoutService::money($n), 'date' => $b->business_date, 'voucher_no' => $no, 'voucher_no_group_sl' => (int) filter_var($no, FILTER_SANITIZE_NUMBER_INT), 'notes' => $note, 'user_id' => $actor]);
        foreach ($lines as [$head,$side,$value]) {
            if ($value) {
                Transaction::create(['sister_concern_id' => $b->id, 'voucher_id' => $v->id, 'account_head_id' => $head, 'voucher_type' => $type, 'transaction_type' => $side, 'amount' => CashoutService::money($value), 'date' => $v->date, 'user_id' => $actor, 'notes' => $note, 'voucher_no' => $no, 'voucher_no_group_sl' => $v->voucher_no_group_sl]);
            }
        }

        return $v;
    }

    private function savingsMovement(ShomriddhiAccount $s, string $type, int $n, Voucher $v, int $actor): void
    {
        $s->principal_balance = CashoutService::money(CashoutService::minor($s->principal_balance) + ($type === 'deposit' ? $n : -$n));
        $s->save();
        ShomriddhiTransaction::create(['customer_id' => $s->customer_id, 'voucher_id' => $v->id, 'type' => $type, 'amount' => CashoutService::money($n), 'date' => $v->date, 'status' => 'Approved', 'created_by' => $actor, 'approved_by' => $actor, 'approved_at' => now(), 'note' => $v->notes]);
    }
}
