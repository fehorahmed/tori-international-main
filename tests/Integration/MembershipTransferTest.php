<?php

namespace Tests\Integration;

use App\Http\Controllers\CardManagement\TransferController;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\BranchManagement\SisterConcern;
use App\Models\MembershipCashout;
use App\Models\MembershipCashoutBooth;
use App\Models\MembershipCashoutSetting;
use App\Models\MembershipTransferSetting;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Membership\CashoutService;
use App\Services\Membership\RecentTransactionService;
use App\Services\Membership\TransferService;
use App\Services\ShomriddhiAdvance\ShomriddhiAdvanceService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Financial tests use temporary fixture rows in rollback transactions; no seeding or schema reset. */
class MembershipTransferTest extends TestCase
{
    use DatabaseTransactions;

    private Supplier $customer;

    private ShomriddhiAccount $account;

    private User $operator;

    private SisterConcern $branch;

    private AccountHead $cash;

    private AccountHead $savings;

    private AccountHead $income;

    private CashoutService $service;

    private string $code;

    private Supplier $recipient;

    private ShomriddhiAccount $recipientAccount;

    private AccountHead $recipientHead;

    private TransferService $transfer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        $this->branch = SisterConcern::where('status', 1)->firstOrFail();
        $this->customer = Supplier::create(['type' => 'Customer', 'name' => 'Cashout rollback test',
            'status' => 1, 'origin_sister_concern_id' => $this->branch->id, 'pin' => Hash::make('763921')]);
        $this->account = ShomriddhiAccount::create(['customer_id' => $this->customer->id,
            'origin_sister_concern_id' => $this->branch->id, 'principal_balance' => '10000.00', 'status' => 'Active']);
        $this->operator = User::firstOrFail();
        $this->operator->givePermissionTo(['shomriddhi_advance_transaction_approve', 'shomriddhi_advance_transaction_reject']);
        $this->actingAs($this->operator, 'web')->withSession(['active_sister_concern_id' => $this->branch->id]);
        $this->cash = $this->fixtureHead('Cash', 'Debit', 7, ['payment_mode' => 2, 'sister_concern_id' => $this->branch->id]);
        $this->savings = $this->fixtureHead('Savings', 'Credit', 28, ['supplier_id' => $this->customer->id, 'head_type' => 'savings']);
        $this->income = $this->fixtureHead('Fee income', 'Credit', 23);
        $this->fund($this->branch->id, $this->cash->id, $this->savings->id);
        MembershipCashoutSetting::updateOrCreate(['id' => 1], ['enabled' => true, 'maximum_amount' => '5000.00',
            'charge_type' => 'fixed', 'charge_rate' => '10.00', 'hold_minutes' => 10, 'income_head_id' => $this->income->id]);
        $booth = MembershipCashoutBooth::updateOrCreate(['branch_id' => $this->branch->id],
            ['qr_token' => Str::random(48), 'cash_head_id' => $this->cash->id, 'enabled' => true]);
        $this->code = 'tori:cashout:'.$booth->qr_token;
        $this->service = app(CashoutService::class);
        $this->recipient = Supplier::create(['type' => 'Customer', 'name' => 'Transfer receiver', 'member_no' => 'SM-'.Str::random(12), 'mobile_no' => '017'.random_int(10000000, 99999999), 'status' => 1, 'origin_sister_concern_id' => $this->branch->id]);
        $this->recipientAccount = ShomriddhiAccount::create(['customer_id' => $this->recipient->id, 'origin_sister_concern_id' => $this->branch->id, 'principal_balance' => '0.00', 'status' => 'Active']);
        $this->recipientHead = $this->fixtureHead('Recipient savings', 'Credit', 28, ['supplier_id' => $this->recipient->id, 'head_type' => 'savings']);
        MembershipTransferSetting::updateOrCreate(['id' => 1], ['enabled' => true, 'maximum_amount' => '5000.00', 'charge_type' => 'fixed', 'charge_rate' => '10.00', 'income_head_id' => $this->income->id, 'posting_user_id' => $this->operator->id]);
        $this->transfer = app(TransferService::class);

    }

    private function fixtureHead(string $name, string $nature, int $group, array $extra = []): AccountHead
    {
        return AccountHead::create($extra + ['name' => 'Test '.$name, 'nature' => $nature,
            'account_group_id' => AccountGroup::where('reserved_id', $group)->firstOrFail()->id,
            'status' => 1]);
    }

    private function fund(int $branch, int $debit, int $credit): void
    {
        $voucher = Voucher::create(['sister_concern_id' => $branch, 'voucher_type' => 3, 'amount' => '10000.00',
            'date' => now()->toDateString(), 'voucher_no' => 'TEST-'.Str::random(12), 'voucher_no_group_sl' => 1, 'user_id' => $this->operator->id]);
        foreach ([$debit => 1, $credit => 2] as $head => $type) {
            Transaction::create([
                'sister_concern_id' => $branch, 'voucher_id' => $voucher->id, 'voucher_type' => 3,
                'account_head_id' => $head, 'transaction_type' => $type, 'amount' => '10000.00',
                'date' => now()->toDateString(), 'voucher_no' => $voucher->voucher_no,
                'voucher_no_group_sl' => 1, 'user_id' => $this->operator->id,
            ]);
        }
    }

    private function requestCash(string $amount = '1000.00'): MembershipCashout
    {
        $quote = $this->service->quote($this->customer, $this->code, $amount);

        return $this->service->request($this->customer, $quote['quote'], '763921', (string) Str::uuid());
    }

    private function transferQuote(string $amount = '1000.00'): array
    {
        return $this->transfer->quote($this->customer, $this->recipient->member_no, $amount);
    }

    public function test_same_branch_transfer_posts_balanced_journals_and_both_savings(): void
    {
        $q = $this->transferQuote();
        $key = (string) Str::uuid();
        $t = $this->transfer->send($this->customer, $q['quote'], '763921', $key);
        $this->assertSame('8990.00', $this->account->fresh()->principal_balance);
        $this->assertSame('1000.00', $this->recipientAccount->fresh()->principal_balance);
        $this->assertSame(100000, CashoutService::minor($this->recipientHead->getCurrentBalance()));
        $this->assertSame(899000, CashoutService::minor($this->savings->getCurrentBalance()));
        $this->assertSame(1000, CashoutService::minor($this->income->getCurrentBalance($this->branch->id)));
        $this->assertSame(3, ShomriddhiTransaction::whereIn('customer_id', [$this->customer->id, $this->recipient->id])->count());
        foreach ([$t->voucher_id, $t->fee_voucher_id] as $id) {
            $v = Voucher::findOrFail($id);
            $this->assertTrue($v->is_locked());
            $this->assertSame(CashoutService::minor(Transaction::where('voucher_id', $id)->where('transaction_type', 1)->sum('amount')), CashoutService::minor(Transaction::where('voucher_id', $id)->where('transaction_type', 2)->sum('amount')));
        }
        $this->travel(3)->minutes();
        $this->assertSame($t->id, $this->transfer->send($this->customer, $q['quote'], '763921', $key)->id);
        $this->assertSame($t->id, $this->transfer->send($this->customer, $q['quote'], '000000', $key)->id);
        $this->assertSame('8990.00', $this->account->fresh()->principal_balance);
        $this->assertFalse(app(ShomriddhiAdvanceService::class)->deleteTransaction(ShomriddhiTransaction::where('voucher_id', $t->voucher_id)->first())['success']);
    }

    public function test_cross_branch_transfer_posts_settlement_and_balances(): void
    {
        $branch = SisterConcern::where('status', 1)->whereKeyNot($this->branch->id)->firstOrFail();
        $this->recipientAccount->update(['origin_sister_concern_id' => $branch->id]);
        $q = $this->transferQuote();
        $t = $this->transfer->send($this->customer, $q['quote'], '763921', (string) Str::uuid());
        $this->assertNotNull($t->settlement_id);
        $this->assertNotSame($t->voucher_id, $t->recipient_voucher_id);
        foreach ([$t->voucher_id, $t->recipient_voucher_id, $t->fee_voucher_id] as $id) {
            $this->assertSame(CashoutService::minor(Transaction::where('voucher_id', $id)->where('transaction_type', 1)->sum('amount')), CashoutService::minor(Transaction::where('voucher_id', $id)->where('transaction_type', 2)->sum('amount')));
        }
        $this->assertSame(100000, CashoutService::minor($this->recipientHead->getCurrentBalance($branch->id)));
        $this->assertSame('8990.00', $this->account->fresh()->principal_balance);
    }

    public function test_wrong_pin_is_rejected_without_posting(): void
    {
        $q = $this->transferQuote();
        $this->expectException(ValidationException::class);
        $this->transfer->send($this->customer, $q['quote'], '000000', (string) Str::uuid());
    }

    public function test_self_transfer_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->transfer->quote($this->recipient, $this->recipient->member_no, '1.00');
    }

    public function test_transfer_respects_pending_cashout_holds(): void
    {
        $q = $this->transferQuote('5000.00');
        $this->requestCash('5000.00');
        $this->expectException(ValidationException::class);
        $this->transfer->send($this->customer, $q['quote'], '763921', (string) Str::uuid());
    }

    public function test_expired_or_changed_quote_requires_review(): void
    {
        $q = $this->transferQuote();
        $this->travel(3)->minutes();
        $this->expectException(ValidationException::class);
        $this->transfer->send($this->customer, $q['quote'], '763921', (string) Str::uuid());
    }

    public function test_api_member_transfer_and_recipient_history(): void
    {
        Sanctum::actingAs($this->customer);
        $q = $this->postJson('/api/membership/v1/transfers/quote', ['recipient' => $this->recipient->mobile_no, 'amount' => '50.00'])->assertOk()->json('data');
        $response = $this->postJson('/api/membership/v1/transfers', ['quote' => $q['quote'], 'pin' => '763921', 'idempotency_key' => (string) Str::uuid()])->assertCreated();
        $this->assertSame('Completed', $response->json('data.status'));
        Sanctum::actingAs($this->recipient);
        $this->getJson('/api/membership/v1/transfers')->assertOk()->assertJsonPath('data.transfers.0.direction', 'received')->assertJsonPath('data.transfers.0.amount', '50.00')->assertJsonPath('data.transfers.0.request_key', null);
    }

    public function test_percentage_charge_and_limit(): void
    {
        MembershipTransferSetting::whereKey(1)->update(['charge_type' => 'percentage', 'charge_rate' => '1.25']);
        $q = $this->transferQuote('100.40');
        $this->assertSame('1.26', $q['fee']);
        $this->expectException(ValidationException::class);
        $this->transferQuote('5000.01');
    }

    public function test_invalid_fee_ledger_rolls_back_both_accounts_and_all_postings(): void
    {
        $q = $this->transferQuote();
        $before = Voucher::count();
        $this->income->update(['status' => 0]);
        try {
            $this->transfer->send($this->customer, $q['quote'], '763921', (string) Str::uuid());
            $this->fail('Expected invalid fee ledger rejection');
        } catch (ValidationException $e) {
            $this->assertSame('10000.00', $this->account->fresh()->principal_balance);
            $this->assertSame('0.00', $this->recipientAccount->fresh()->principal_balance);
            $this->assertSame($before, Voucher::count());
        }
    }

    public function test_same_quote_cannot_be_spent_again_with_a_different_key(): void
    {
        $q = $this->transferQuote();
        $this->transfer->send($this->customer, $q['quote'], '763921', (string) Str::uuid());
        $this->expectException(ValidationException::class);
        $this->transfer->send($this->customer, $q['quote'], '763921', (string) Str::uuid());
    }

    public function test_recipient_inactivated_after_review_is_rejected(): void
    {
        $q = $this->transferQuote();
        $this->recipient->update(['status' => 0]);
        $this->expectException(ValidationException::class);
        $this->transfer->send($this->customer, $q['quote'], '763921', (string) Str::uuid());
    }

    public function test_changed_charge_requires_fresh_review(): void
    {
        $q = $this->transferQuote();
        MembershipTransferSetting::whereKey(1)->update(['charge_rate' => '20.00']);
        $this->expectException(ValidationException::class);
        $this->transfer->send($this->customer, $q['quote'], '763921', (string) Str::uuid());
    }

    public function test_unrelated_member_cannot_see_history_and_staff_cannot_transfer(): void
    {
        $q = $this->transferQuote();
        $this->transfer->send($this->customer, $q['quote'], '763921', (string) Str::uuid());
        $other = Supplier::create(['type' => 'Customer', 'name' => 'Unrelated member', 'status' => 1]);
        Sanctum::actingAs($other);
        $this->getJson('/api/membership/v1/transfers')->assertOk()->assertJsonCount(0, 'data.transfers');
        Sanctum::actingAs($this->operator);
        $this->postJson('/api/membership/v1/transfers/quote', ['recipient' => $this->recipient->member_no, 'amount' => '1.00'])->assertForbidden();
    }

    public function test_admin_history_uses_posting_branch_and_settings_store_authorized_actor(): void
    {
        $q = $this->transferQuote();
        $t = $this->transfer->send($this->customer, $q['quote'], '763921', (string) Str::uuid());
        $controller = new TransferController;
        $this->assertSame($t->id, $controller->index()->getData()['transfers']->first()->id);
        $this->assertSame('card_management.transfers.settings', $controller->settings()->name());
        $controller->update(Request::create('/', 'POST', ['enabled' => 1, 'maximum_amount' => '3000.00', 'charge_type' => 'fixed', 'charge_rate' => '0.00']));
        $this->assertSame($this->operator->id, MembershipTransferSetting::current()->posting_user_id);
        $this->assertSame('3000.00', MembershipTransferSetting::current()->maximum_amount);
    }

    public function test_recent_transactions_group_fees_and_are_member_scoped(): void
    {
        $q = $this->transferQuote();
        $t = $this->transfer->send($this->customer, $q['quote'], '763921', (string) Str::uuid());
        $service = app(RecentTransactionService::class);
        $sent = $service->forCustomer($this->customer);
        $received = $service->forCustomer($this->recipient);
        $this->assertCount(1, $sent);
        $this->assertCount(1, $received);
        $this->assertSame($t->reference, $sent[0]['reference']);
        $this->assertSame('1010.00', $sent[0]['amount']);
        $this->assertSame('10.00', $sent[0]['fee']);
        $this->assertFalse($sent[0]['is_credit']);
        $this->assertSame('1000.00', $received[0]['amount']);
        $this->assertSame('0.00', $received[0]['fee']);
        $this->assertTrue($received[0]['is_credit']);
        Sanctum::actingAs($this->customer);
        $this->getJson('/api/membership/v1/dashboard')->assertOk()->assertJsonCount(1, 'data.recent_transactions')->assertJsonPath('data.recent_transactions.0.reference', $t->reference);
    }

    public function test_recent_cashout_shows_completed_total_once_and_hides_pending(): void
    {
        $c = $this->requestCash();
        $service = app(RecentTransactionService::class);
        $this->assertCount(0, $service->forCustomer($this->customer));
        $this->service->change($c, 'accept', $this->operator);
        $this->service->change($c, 'complete', $this->operator);
        $rows = $service->forCustomer($this->customer);
        $this->assertCount(1, $rows);
        $this->assertSame($c->reference, $rows[0]['reference']);
        $this->assertSame('1010.00', $rows[0]['amount']);
        $this->assertSame('1000.00',$rows[0]['net_amount']);
    }
}
