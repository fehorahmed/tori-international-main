<?php

namespace Tests\Integration;

use App\Http\Controllers\CardManagement\CashoutController;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\BranchManagement\SisterConcern;
use App\Models\MembershipCashout;
use App\Models\MembershipCashoutBooth;
use App\Models\MembershipCashoutSetting;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Membership\CashoutService;
use App\Services\ShomriddhiAdvance\ShomriddhiAdvanceService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/** Financial tests use temporary fixture rows in rollback transactions; no seeding or schema reset. */
class MembershipCashoutTest extends TestCase
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

    public function test_admin_cashout_routes_belong_to_card_management(): void
    {
        $this->assertSame('/admin/card-management/cashout', route('card-management.cashout.index', [], false));
        $this->assertSame('/admin/card-management/cashout/settings', route('card-management.cashout.settings', [], false));
        $controller = new CashoutController;
        $this->assertSame('card_management.cashout.settings', $controller->settingsPage()->name());
        $this->assertSame('card_management.cashout.index', $controller->index()->name());
    }

    public function test_fixed_charge_hold_and_payment_update_all_ledgers_once(): void
    {
        $cashout = $this->requestCash();
        $this->assertSame(101000, MembershipCashout::held($this->account->id));
        $this->assertSame(899000, CashoutService::available($this->account->fresh()));
        $this->assertSame(1000000, CashoutService::minor($this->account->fresh()->principal_balance));
        $this->assertSame(0, ShomriddhiTransaction::where('customer_id', $this->customer->id)->count());
        $this->service->change($cashout, 'accept', $this->operator);
        $done = $this->service->change($cashout, 'complete', $this->operator);
        $this->assertSame('Completed', $done->status);
        $this->assertSame(899000, CashoutService::minor($this->account->fresh()->principal_balance));
        $this->assertSame(0, MembershipCashout::held($this->account->id));
        $this->assertSame(900000, CashoutService::minor($this->cash->getCurrentBalance($this->branch->id)));
        $this->assertSame(1000, CashoutService::minor($this->income->getCurrentBalance($this->branch->id)));
        $this->assertSame(899000, CashoutService::minor($this->savings->getCurrentBalance()));
        $this->assertSame(101000, CashoutService::minor(ShomriddhiTransaction::where('customer_id', $this->customer->id)->sum('amount')));
        foreach ([$done->voucher_id, $done->fee_voucher_id] as $id) {
            $this->assertSame(CashoutService::minor(Transaction::where('voucher_id', $id)->where('transaction_type', 1)->sum('amount')),
                CashoutService::minor(Transaction::where('voucher_id', $id)->where('transaction_type', 2)->sum('amount')));
            $this->assertTrue(Voucher::findOrFail($id)->is_locked());
        }
        $this->service->change($cashout, 'complete', $this->operator);
        $this->assertSame(899000, CashoutService::minor($this->account->fresh()->principal_balance));
        $result = app(ShomriddhiAdvanceService::class)->deleteTransaction(ShomriddhiTransaction::where('voucher_id', $done->voucher_id)->firstOrFail());
        $this->assertFalse($result['success']);
    }

    public function test_percentage_rounding_limit_and_insufficient_total_balance(): void
    {
        MembershipCashoutSetting::find(1)->update(['charge_type' => 'percentage', 'charge_rate' => '1.85']);
        $quote = $this->service->quote($this->customer, $this->code, '1000.50');
        $this->assertSame('18.51', $quote['fee']);
        $this->assertSame('1019.01', $quote['total']);
        foreach (['5000.01', '10001.00'] as $amount) {
            try {
                $this->service->quote($this->customer, $this->code, $amount);
                $this->fail('Over-limit quote accepted.');
            } catch (ValidationException $e) {
                $this->assertNotEmpty($e->errors());
            }
        }
        MembershipCashoutSetting::find(1)->update(['maximum_amount' => '10000.00']);
        $this->expectException(ValidationException::class);
        $this->service->quote($this->customer, $this->code, '10000.00');
    }

    public function test_retry_does_not_create_two_requests_and_wrong_pin_never_holds_funds(): void
    {
        $quote = $this->service->quote($this->customer, $this->code, '1000.00');
        $key = (string) Str::uuid();
        try {
            $this->service->request($this->customer, $quote['quote'], '000000', $key);
            $this->fail('Wrong PIN accepted.');
        } catch (ValidationException $e) {
            $this->assertSame(0, MembershipCashout::held($this->account->id));
        }
        $first = $this->service->request($this->customer, $quote['quote'], '763921', $key);
        $this->travel(3)->minutes();
        $retry = $this->service->request($this->customer, $quote['quote'], '763921', $key);
        $this->assertSame($first->id, $retry->id);
        $this->assertSame(1, MembershipCashout::where('customer_id', $this->customer->id)->count());
    }

    public function test_pending_expiry_cancel_and_branch_rejection_release_holds(): void
    {
        $cashout = $this->requestCash();
        $this->service->change($cashout, 'cancel');
        $this->assertSame(0, MembershipCashout::held($this->account->id));
        $cashout = $this->requestCash();
        $this->travel(11)->minutes();
        $this->assertSame('Expired', $cashout->effectiveStatus());
        $this->assertSame(0, MembershipCashout::held($this->account->id));
        $cashout = $this->requestCash();
        $this->service->change($cashout, 'accept', $this->operator);
        $this->travel(11)->minutes();
        $this->assertSame(101000, MembershipCashout::held($this->account->id));
        $this->service->change($cashout, 'reject', $this->operator, 'Not enough physical cash.');
        $this->assertSame(0, MembershipCashout::held($this->account->id));
    }

    public function test_existing_withdrawals_and_direct_decrements_cannot_spend_held_balance(): void
    {
        $this->requestCash('5000.00');
        try {
            $this->account->fresh()->update(['status' => 'Closed']);
            $this->fail('A held account was closed.');
        } catch (ValidationException $e) {
            $this->assertSame('Active', $this->account->fresh()->status);
        }
        try {
            $this->account->fresh()->delete();
            $this->fail('A held account was deleted.');
        } catch (ValidationException $e) {
            $this->assertNotNull($this->account->fresh());
        }
        $advance = ShomriddhiTransaction::create(['customer_id' => $this->customer->id, 'type' => 'withdraw',
            'amount' => '6000.00', 'date' => now()->toDateString(), 'status' => 'Pending', 'created_by' => $this->operator->id]);
        $result = app(ShomriddhiAdvanceService::class)->approve($advance, $this->cash->id);
        $this->assertFalse($result['success']);
        $this->assertSame('Pending', $advance->fresh()->status);
        $this->expectException(ValidationException::class);
        $this->account->fresh()->decrement('principal_balance', 6000);
    }

    public function test_cross_branch_cashout_posts_balanced_origin_and_booth_entries(): void
    {
        $other = SisterConcern::whereKeyNot($this->branch->id)->where('status', 1)->firstOrFail();
        foreach ([4 => ['Receivable', 'Debit', 6], 5 => ['Payable', 'Credit', 13]] as $reserved => [$name, $nature, $group]) {
            if (! AccountHead::where('reserved_id', $reserved)->whereNull('sister_concern_id')->exists()) {
                $this->fixtureHead($name, $nature, $group, ['reserved_id' => $reserved]);
            }
        }
        $cash = $this->fixtureHead('Other cash', 'Debit', 7, ['payment_mode' => 2, 'sister_concern_id' => $other->id]);
        $offset = $this->fixtureHead('Funding offset', 'Credit', 23);
        $this->fund($other->id, $cash->id, $offset->id);
        $booth = MembershipCashoutBooth::updateOrCreate(['branch_id' => $other->id], ['cash_head_id' => $cash->id, 'qr_token' => Str::random(48), 'enabled' => true]);
        $this->code = 'tori:cashout:'.$booth->qr_token;
        $cashout = $this->requestCash();
        $this->withSession(['active_sister_concern_id' => $other->id]);
        $this->service->change($cashout, 'accept', $this->operator);
        $done = $this->service->change($cashout, 'complete', $this->operator);
        $this->assertNotNull($done->settlement_id);
        foreach ([$done->source_voucher_id, $done->voucher_id, $done->fee_voucher_id] as $id) {
            $this->assertSame(CashoutService::minor(Transaction::where('voucher_id', $id)->where('transaction_type', 1)->sum('amount')),
                CashoutService::minor(Transaction::where('voucher_id', $id)->where('transaction_type', 2)->sum('amount')));
        }
        $this->assertSame(899000, CashoutService::minor($this->savings->getCurrentBalance($this->branch->id)));
        $this->assertSame(900000, CashoutService::minor($cash->getCurrentBalance($other->id)));
        $this->assertSame(101000, CashoutService::minor(ShomriddhiTransaction::where('customer_id', $this->customer->id)->sum('amount')));
    }

    public function test_api_rejects_other_members_requests_and_employees(): void
    {
        $cashout = $this->requestCash();
        $other = Supplier::whereKeyNot($this->customer->id)->where('type', '!=', 'Supplier')->where('status', 1)->firstOrFail();
        Sanctum::actingAs($other);
        $this->getJson('/api/membership/v1/cashouts/'.$cashout->id)->assertForbidden();
        $this->postJson('/api/membership/v1/cashouts/'.$cashout->id.'/cancel')->assertForbidden();
        Sanctum::actingAs($this->operator);
        $this->getJson('/api/membership/v1/cashout')->assertForbidden();
    }

    public function test_api_creates_an_authenticated_hold_and_reports_available_balance(): void
    {
        Sanctum::actingAs($this->customer);
        $quote = $this->postJson('/api/membership/v1/cashout/quote', ['qr_code' => $this->code, 'amount' => '1000.00'])
            ->assertOk()->assertJsonPath('data.fee', '10.00')->json('data.quote');
        $this->postJson('/api/membership/v1/cashouts', ['quote' => $quote, 'pin' => '763921', 'idempotency_key' => (string) Str::uuid()])
            ->assertCreated()->assertJsonPath('data.status', 'Pending');
        $this->getJson('/api/membership/v1/cashout')->assertOk()->assertJsonPath('data.held_balance', '1010.00')
            ->assertJsonPath('data.available_balance', '8990.00');
    }

    public function test_payment_failure_keeps_the_request_and_balance_unchanged(): void
    {
        $cashout = $this->requestCash();
        $this->service->change($cashout, 'accept', $this->operator);
        $this->income->update(['status' => 0]);
        $count = Voucher::count();
        try {
            $this->service->change($cashout, 'complete', $this->operator);
            $this->fail('Inactive income account accepted.');
        } catch (ValidationException $e) {
            $this->assertSame($count, Voucher::count());
            $this->assertSame('Accepted', $cashout->fresh()->status);
            $this->assertSame(101000, MembershipCashout::held($this->account->id));
            $this->assertSame(1000000, CashoutService::minor($this->account->fresh()->principal_balance));
        }
    }

    public function test_changed_settings_require_a_fresh_quote_and_wrong_branch_cannot_accept(): void
    {
        $quote = $this->service->quote($this->customer, $this->code, '1000.00');
        MembershipCashoutSetting::find(1)->update(['charge_rate' => '20.00']);
        try {
            $this->service->request($this->customer, $quote['quote'], '763921', (string) Str::uuid());
            $this->fail('Stale price accepted.');
        } catch (ValidationException $e) {
            $this->assertSame(0, MembershipCashout::held($this->account->id));
        }
        $cashout = $this->requestCash();
        $this->withSession(['active_sister_concern_id' => -1]);
        try {
            $this->service->change($cashout, 'accept', $this->operator);
            $this->fail('Wrong branch accepted request.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_counter_qr_is_generated_locally_and_settings_support_percentage(): void
    {
        $this->operator->forceFill(['role' => 'Admin'])->save();
        $controller = app(CashoutController::class);
        $svg = $controller->qr();
        $this->assertStringContainsString('<svg', $svg->getContent());
        $controller->settings(Request::create('/', 'POST', [
            'enabled' => '1', 'maximum_amount' => '5000.00', 'charge_type' => 'percentage',
            'charge_rate' => '1.85', 'hold_minutes' => 10, 'income_head_id' => $this->income->id,
        ]));
        $this->assertSame('percentage', MembershipCashoutSetting::find(1)->charge_type);
        $this->assertSame('18.50', $this->service->quote($this->customer, $this->code, '1000.00')['fee']);
    }

    public function test_ordinary_withdrawal_and_deposit_still_post_matching_savings_and_cash(): void
    {
        $service = app(ShomriddhiAdvanceService::class);
        foreach ([['withdraw', '1000.00', 900000], ['deposit', '2500.00', 1150000]] as [$type, $amount, $balance]) {
            $advance = ShomriddhiTransaction::create(['customer_id' => $this->customer->id, 'type' => $type,
                'amount' => $amount, 'date' => now()->toDateString(), 'status' => 'Pending', 'created_by' => $this->operator->id]);
            $result = $service->approve($advance, $this->cash->id);
            $this->assertTrue($result['success'], $result['message']);
            $this->assertSame($balance, CashoutService::minor($this->account->fresh()->principal_balance));
            $this->assertSame($balance, CashoutService::minor($this->savings->getCurrentBalance()));
            $this->assertSame($balance, CashoutService::minor($this->cash->getCurrentBalance($this->branch->id)));
            $this->assertFalse($service->approve($advance, $this->cash->id)['success']);
        }
    }

    public function test_ordinary_full_withdrawal_and_reversal_remain_supported(): void
    {
        $advance = ShomriddhiTransaction::create(['customer_id' => $this->customer->id, 'type' => 'withdraw',
            'amount' => '10000.00', 'date' => now()->toDateString(), 'status' => 'Pending', 'created_by' => $this->operator->id]);
        $service = app(ShomriddhiAdvanceService::class);
        $result = $service->approve($advance, $this->cash->id);
        $this->assertTrue($result['success'], $result['message']);
        $this->assertSame(0, CashoutService::minor($this->account->fresh()->principal_balance));
        $this->assertSame(0, CashoutService::minor($this->cash->getCurrentBalance($this->branch->id)));
        $result = $service->deleteTransaction($advance->fresh());
        $this->assertTrue($result['success'], $result['message']);
        $this->assertSame(1000000, CashoutService::minor($this->account->fresh()->principal_balance));
        $this->assertSame(1000000, CashoutService::minor($this->savings->getCurrentBalance()));
        $this->assertSame(1000000, CashoutService::minor($this->cash->getCurrentBalance($this->branch->id)));
    }
}
