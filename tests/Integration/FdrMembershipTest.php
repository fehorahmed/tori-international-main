<?php

namespace Tests\Integration;

use App\Http\Controllers\ShomriddhiFdr\FdrAccountController;
use App\Http\Controllers\ShomriddhiFdr\FdrDashboardController;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\BranchManagement\SisterConcern;
use App\Models\CustomerKyc;
use App\Models\ShomriddhiFdr\FdrAccount;
use App\Models\ShomriddhiFdr\FdrScheme;
use App\Models\ShomriddhiFdr\FdrTransaction;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Membership\RegistrationSavingsService;
use App\Services\ShomriddhiFdr\FdrAccountService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class FdrMembershipTest extends TestCase
{
    use DatabaseTransactions;

    private $member;

    private $savings;

    private $scheme;

    private $branch;

    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->branch = SisterConcern::where('status', 1)->firstOrFail();
        Carbon::setTestNow('2026-01-31 12:00:00');
        $this->actingAs(User::firstOrFail());
        session(['active_sister_concern_id' => $this->branch->id]);
        $this->member = Supplier::create(['name' => 'FDR test fixture', 'type' => 'Customer', 'status' => 1, 'origin_sister_concern_id' => $this->branch->id, 'pin' => Hash::make('123456')]);
        CustomerKyc::create(['customer_id' => $this->member->id, 'submitted_name' => $this->member->name, 'nid_number' => '1234567890', 'nid_front_path' => 'test', 'nid_back_path' => 'test', 'selfie_path' => 'test', 'status' => 'Verified']);
        $this->savings = RegistrationSavingsService::ensure($this->member);
        $this->savings->update(['principal_balance' => 200000]);
        $this->scheme = FdrScheme::create(['name' => 'Fixture '.uniqid(), 'duration' => 12, 'interest_rate' => 12, 'penalty_rate' => 1, 'min_amount' => 1000, 'max_amount' => 150000, 'status' => 'Active', 'visible_on_app' => true]);
        $this->service = app(FdrAccountService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function open(array $extra = []): FdrAccount
    {
        return $this->service->openFromSavings($this->member, $extra + ['scheme_id' => $this->scheme->id, 'amount' => '100000.00', 'frequency' => 'Monthly', 'auto_renew' => false, 'pin' => '123456', 'terms_hash' => $this->service->termsHash($this->scheme), 'idempotency_key' => 'fixture-open-key-123456']);
    }

    private function balanced(): void
    {
        foreach (FdrTransaction::whereHas('account', fn ($q) => $q->where('customer_id', $this->member->id))->get() as $t) {
            $dr = Transaction::where('voucher_id', $t->voucher_id)->where('transaction_type', 1)->sum('amount');
            $cr = Transaction::where('voucher_id', $t->voucher_id)->where('transaction_type', 2)->sum('amount');
            $this->assertEquals($dr, $cr);
        }
    }

    public function test_savings_open_is_active_atomic_and_retry_safe(): void
    {
        $a = $this->open();
        $this->assertSame('Active', $a->status);
        $this->assertEquals(100000, $this->savings->fresh()->principal_balance);
        $this->assertEquals(112000, $a->payable_amount_at_maturity);
        $this->assertSame($a->id, $this->open()->id);
        $this->assertSame(1, FdrTransaction::where('fdr_account_id', $a->id)->count());
        $this->balanced();
    }

    public function test_failed_ledger_insert_rolls_back_account_and_savings(): void
    {
        $before = FdrAccount::count();
        Transaction::creating(function () {
            throw new \RuntimeException('fixture ledger failure');
        });
        try {
            $this->open();
            $this->fail('Expected failure');
        } catch (\RuntimeException $e) {
            $this->assertEquals('fixture ledger failure', $e->getMessage());
        } finally {
            Transaction::flushEventListeners();
        }
        $this->assertSame($before, FdrAccount::count());
        $this->assertEquals(200000, $this->savings->fresh()->principal_balance);
    }

    public function test_hidden_scheme_and_unapproved_kyc_are_rejected(): void
    {
        $this->scheme->update(['visible_on_app' => false]);
        try {
            $this->open();
            $this->fail('Expected rejection');
        } catch (ValidationException) {
        }
        $this->scheme->update(['visible_on_app' => true]);
        CustomerKyc::where('customer_id', $this->member->id)->update(['status' => 'Pending']);
        $this->expectException(ValidationException::class);
        $this->open();
    }

    public function test_insufficient_balance_or_wrong_pin_cannot_deduct_funds(): void
    {
        try {
            $this->open(['pin' => '000000']);
            $this->fail('Expected PIN rejection');
        } catch (ValidationException) {
        }
        $this->savings->update(['principal_balance' => 500]);
        $this->expectException(ValidationException::class);
        $this->open();
    }

    public function test_missed_months_are_recovered_once_and_end_of_month_does_not_drift(): void
    {
        $a = $this->open();
        Carbon::setTestNow('2026-04-30 12:00:00');
        $this->service->process($a->id);
        $this->assertEquals(103000, $this->savings->fresh()->principal_balance);
        $this->assertSame('2026-05-31', $a->fresh()->next_interest_date);
        $this->service->process($a->id);
        $this->assertSame(3, FdrTransaction::where('fdr_account_id', $a->id)->where('type', 'Interest Payout')->count());
        $this->balanced();
    }

    public function test_pending_encashment_does_not_pay_and_approval_reconciles_paid_interest(): void
    {
        $a = $this->open();
        Carbon::setTestNow('2026-02-28 12:00:00');
        $this->service->process($a->id);
        $r = $this->service->requestEncash($this->member, $a->id, '123456', 'request-fixture-123456');
        $this->assertEquals(101000, $this->savings->fresh()->principal_balance);
        $this->assertSame('Active', $a->fresh()->status);
        $this->assertEquals(98000, $r->preview['net_payable']);
        $this->service->reviewEncash($r->id, true);
        $this->assertEquals(199000, $this->savings->fresh()->principal_balance);
        $this->assertSame('Closed', $a->fresh()->status);
        $this->service->reviewEncash($r->id, true);
        $this->assertEquals(199000, $this->savings->fresh()->principal_balance);
        $this->balanced();
    }

    public function test_reject_keeps_fdr_active_and_does_not_return_money(): void
    {
        $a = $this->open();
        $r = $this->service->requestEncash($this->member, $a->id, '123456', 'request-fixture-123456');
        $this->service->reviewEncash($r->id, false, 'fixture');
        $this->assertSame('Active', $a->fresh()->status);
        $this->assertEquals(100000, $this->savings->fresh()->principal_balance);
    }

    public function test_at_maturity_pays_interest_and_can_return_principal_without_double_interest(): void
    {
        $a = $this->open(['frequency' => 'At Maturity']);
        Carbon::setTestNow('2027-01-31 12:00:00');
        $this->service->process($a->id);
        $this->assertSame('Matured', $a->fresh()->status);
        $this->assertEquals(112000, $this->savings->fresh()->principal_balance);
        $r = $this->service->requestEncash($this->member, $a->id, '123456', 'request-fixture-123456');
        $this->service->reviewEncash($r->id, true);
        $this->assertEquals(212000, $this->savings->fresh()->principal_balance);
        $this->balanced();
    }

    public function test_auto_renew_preserves_terms_and_pending_request_prevents_renewal(): void
    {
        $a = $this->open(['frequency' => 'At Maturity', 'auto_renew' => true]);
        Carbon::setTestNow('2027-01-31 12:00:00');
        $this->service->process($a->id);
        $this->assertSame('Active', $a->fresh()->status);
        $this->assertEquals(1, $a->fresh()->renewal_count);
        $this->service->requestEncash($this->member, $a->id, '123456', 'request-fixture-123456');
        Carbon::setTestNow('2028-01-31 12:00:00');
        $this->service->process($a->id);
        $this->assertSame('Matured', $a->fresh()->status);
        $this->assertEquals(1, $a->fresh()->renewal_count);
    }

    public function test_app_api_restricts_ownership_and_returns_visible_schemes(): void
    {
        Sanctum::actingAs($this->member);
        $this->withoutMiddleware(ThrottleRequests::class);
        $this->getJson('/api/membership/v1/fdr')->assertOk()->assertJsonPath('data.kyc_approved', true);
        $this->postJson('/api/membership/v1/fdr/quote', ['scheme_id' => $this->scheme->id, 'amount' => '100000'])->assertOk()->assertJsonPath('data.term_interest', '12000.00');
        $a = $this->open();
        $this->getJson('/api/membership/v1/fdr/'.$a->id)->assertOk();
        $other = Supplier::create(['name' => 'Other fixture', 'type' => 'Customer', 'status' => 1]);
        Sanctum::actingAs($other);
        $this->getJson('/api/membership/v1/fdr/'.$a->id)->assertNotFound();
    }

    public function test_changed_terms_and_conflicting_idempotency_key_are_rejected(): void
    {
        $old = $this->service->termsHash($this->scheme);
        $this->scheme->update(['interest_rate' => 13]);
        try {
            $this->open(['terms_hash' => $old]);
            $this->fail('Expected stale quote rejection');
        } catch (ValidationException) {
        }
        $this->open();
        $this->expectException(ValidationException::class);
        $this->open(['amount' => '100001.00']);
    }

    public function test_short_term_quarterly_payout_is_capped_at_maturity(): void
    {
        $this->scheme->update(['duration' => 2]);
        $a = $this->open(['frequency' => 'Quarterly']);
        $this->assertSame('2026-03-31', $a->next_interest_date);
        Carbon::setTestNow('2026-03-31 12:00:00');
        $this->service->process($a->id);
        $this->assertEquals(102000, $this->savings->fresh()->principal_balance);
        $this->assertSame('Matured', $a->fresh()->status);
        $this->balanced();
    }

    public function test_cross_branch_encashment_review_is_rejected(): void
    {
        $a = $this->open();
        $r = $this->service->requestEncash($this->member, $a->id, '123456', 'request-fixture-123456');
        session(['active_sister_concern_id' => $this->branch->id + 999]);
        try {
            $this->service->reviewEncash($r->id, true);
            $this->fail('Expected branch rejection');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
        $this->assertSame('Pending', $r->fresh()->status);
        $this->assertEquals(100000, $this->savings->fresh()->principal_balance);
    }

    public function test_cash_bank_and_savings_admin_approval_have_distinct_accounting(): void
    {
        $base = ['customer_id' => $this->member->id, 'scheme_id' => $this->scheme->id, 'origin_sister_concern_id' => $this->branch->id, 'principal_amount' => 1000,
            'interest_rate' => 12, 'duration_months' => 12, 'penalty_rate' => 1, 'opening_date' => '2026-01-31', 'maturity_date' => '2027-01-31',
            'payable_amount_at_maturity' => 1120, 'interest_payout_frequency' => 'Monthly', 'next_interest_date' => '2026-02-28', 'status' => 'Pending'];
        $cash = AccountHead::where('status', 1)->where('payment_mode', '>', 0)->where(function ($q) {
            $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', $this->branch->id);
        })->firstOrFail();
        $a = FdrAccount::create(['fdr_no' => 'fixture-cash-'.uniqid()] + $base);
        $this->service->approve($a, $cash->id);
        $this->assertEquals(200000, $this->savings->fresh()->principal_balance);
        $b = FdrAccount::create(['fdr_no' => 'fixture-savings-'.uniqid()] + $base);
        $this->service->approve($b, 0, 'savings');
        $this->assertEquals(199000, $this->savings->fresh()->principal_balance);
        $this->balanced();
    }

    public function test_approval_failure_rolls_back_encashment_and_request_status(): void
    {
        $a = $this->open();
        $r = $this->service->requestEncash($this->member, $a->id, '123456', 'request-fixture-123456');
        Transaction::creating(function () {
            throw new \RuntimeException('encash fixture failure');
        });
        try {
            $this->service->reviewEncash($r->id, true);
            $this->fail('Expected failure');
        } catch (\RuntimeException) {
        } finally {
            Transaction::flushEventListeners();
        }
        $this->assertSame('Pending', $r->fresh()->status);
        $this->assertSame('Active', $a->fresh()->status);
        $this->assertEquals(100000, $this->savings->fresh()->principal_balance);
    }

    public function test_admin_cannot_reassign_pending_fdr_to_another_member(): void
    {
        Gate::before(fn () => true);
        $account = $this->open();
        $account->update(['status' => 'Pending']);
        $other = Supplier::create(['name' => 'Other FDR member', 'type' => 'Customer', 'status' => 1, 'origin_sister_concern_id' => $this->branch->id]);
        $request = Request::create('/', 'PUT', [
            'customer_id' => $other->id, 'scheme_id' => $this->scheme->id,
            'principal_amount' => '100000.00', 'interest_payout_frequency' => 'Monthly',
            'opening_date' => '31-01-2026', 'auto_renew' => false,
        ]);
        try {
            app(FdrAccountController::class)->update($request, $account->id);
            $this->fail('Expected member reassignment to be rejected');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('customer_id', $e->errors());
        }
        $this->assertEquals($this->member->id, $account->fresh()->customer_id);
        $this->assertEquals(100000, $this->savings->fresh()->principal_balance);
    }

    public function test_dashboard_principal_is_scoped_to_active_branch_and_includes_matured(): void
    {
        $a = $this->open();
        $a->update(['status' => 'Matured']);
        $expected = FdrAccount::where('origin_sister_concern_id', $this->branch->id)->whereIn('status', ['Active', 'Matured'])->sum('principal_amount');
        $other = $a->replicate(['fdr_no', 'idempotency_key', 'request_hash']);
        $other->fdr_no = 'fixture-other-branch-'.uniqid();
        $other->origin_sister_concern_id = SisterConcern::where('id', '!=', $this->branch->id)->firstOrFail()->id;
        $other->save();
        $view = app(FdrDashboardController::class)->index();
        $this->assertEquals($expected, $view->getData()['data']['total_principal']);
    }

    public function test_legacy_nature_correction_requires_reconciled_liability_and_preserves_journals(): void
    {
        $a = $this->open();
        $head = AccountHead::where('supplier_id', $this->member->id)->where('head_type', 'fdr')->firstOrFail();
        $head->refresh()->update(['nature' => 'Debit']);
        $before = Transaction::where('account_head_id', $head->id)->get()->toArray();
        $migration = require database_path('migrations/2026_11_23_020000_correct_reconciled_fdr_liability_nature.php');
        $migration->up();
        $this->assertSame('Credit', $head->fresh()->nature);
        $this->assertSame($before, Transaction::where('account_head_id', $head->id)->get()->toArray());
        $head->refresh()->update(['nature' => 'Debit']);
        $a->update(['principal_amount' => '100001.00']);
        $migration->up();
        $this->assertSame('Debit', $head->fresh()->nature);
        $this->assertEquals(100000, $this->savings->fresh()->principal_balance);
    }

    public function test_app_open_requires_maturity_payout_and_disabled_auto_renew(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        Sanctum::actingAs($this->member);
        $payload = ['scheme_id' => $this->scheme->id, 'amount' => '1000.00',
            'frequency' => 'Monthly', 'auto_renew' => true, 'pin' => '123456',
            'terms_hash' => $this->service->termsHash($this->scheme),
            'idempotency_key' => 'fixture-app-maturity-only'];
        $this->postJson('/api/membership/v1/fdr/open', $payload)
            ->assertUnprocessable()->assertJsonValidationErrors(['frequency', 'auto_renew']);
        $this->assertEquals(200000, $this->savings->fresh()->principal_balance);
        $payload['frequency'] = 'At Maturity';
        $payload['auto_renew'] = false;
        $this->postJson('/api/membership/v1/fdr/open', $payload)->assertCreated()
            ->assertJsonPath('data.interest_payout_frequency', 'At Maturity');
        $account = FdrAccount::where('customer_id', $this->member->id)->firstOrFail();
        $this->assertFalse((bool) $account->auto_renew);
        $this->assertEquals(199000, $this->savings->fresh()->principal_balance);
    }

    public function test_encashment_datatable_filters_searches_and_preserves_safe_actions(): void
    {
        $this->withoutMiddleware();
        Gate::before(fn () => true);
        $a = $this->open();
        $r = $this->service->requestEncash($this->member, $a->id, '123456', 'fixture-datatable-encash');
        $this->member->update(['name' => '<script>alert(1)</script>']);
        $foreign = $r->replicate(['idempotency_key']);
        $foreign->idempotency_key = 'fixture-other-branch-request';
        $foreign->origin_sister_concern_id = SisterConcern::where('id', '!=', $this->branch->id)->firstOrFail()->id;
        $foreign->save();
        $params = ['draw' => 1, 'start' => 0, 'length' => 10, 'status' => 'Pending',
            'start_date' => now()->toDateString(), 'end_date' => now()->toDateString(),
            'search' => ['value' => $a->fdr_no], 'order' => [['column' => 0, 'dir' => 'desc']],
            'columns' => [
                ['data' => 'created_at', 'name' => 'created_at', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'member', 'name' => 'member', 'searchable' => 'true', 'orderable' => 'false'],
            ]];
        $url = '/admin/shomriddhi-fdr/encashment-requests/datatable?';
        $response = $this->getJson($url.http_build_query($params))->assertOk()->assertJsonPath('recordsFiltered', 1);
        $row = $response->json('data.0');
        $this->assertStringContainsString('&lt;script&gt;', $row['member']);
        $this->assertStringNotContainsString('<script>', $row['member']);
        $this->assertStringContainsString('Approve to Savings', $row['action']);
        $this->assertStringContainsString('Net:', $row['settlement']);
        $params['status'] = 'Approved';
        $this->getJson($url.http_build_query($params))->assertOk()->assertJsonPath('recordsFiltered', 0);
        $params['status'] = 'Pending';
        $params['start_date'] = now()->addDay()->toDateString();
        $params['end_date'] = now()->addDay()->toDateString();
        $this->getJson($url.http_build_query($params))->assertOk()->assertJsonPath('recordsFiltered', 0);
        $params['start_date'] = $params['end_date'] = now()->toDateString();
        Carbon::setTestNow('2026-01-30 12:00:00');
        $response = $this->getJson($url.http_build_query($params))->assertOk();
        $this->assertStringContainsString('disabled', $response->json('data.0.action'));
        $this->assertStringContainsString('Reject', $response->json('data.0.action'));
        $this->assertEquals(100000, $this->savings->fresh()->principal_balance);
    }
}
