<?php

namespace Tests\Integration;

use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\BranchManagement\SisterConcern;
use App\Models\CustomerKyc;
use App\Models\MembershipAgent;
use App\Models\MembershipAgentTransaction;
use App\Models\MembershipTransactionSetting as Settings;
use App\Models\MembershipTransfer;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Membership\AgentTransactionService;
use App\Services\Membership\RecentTransactionService;
use App\Services\Membership\RegistrationSavingsService;
use App\Services\Membership\TransactionWindowService;
use App\Services\Membership\TransferService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AgentTransactionTest extends TestCase
{
    use DatabaseTransactions;

    private $customer;

    private $agentMember;

    private $source;

    private $target;

    private $agent;

    private $service;

    private $branch;

    private $operator;

    private $expense;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-24 12:00:00');
        $this->branch = SisterConcern::where('status', 1)->firstOrFail();
        $this->operator = User::firstOrFail();
        $this->actingAs($this->operator)->withSession(['active_sister_concern_id' => $this->branch->id]);
        Settings::writeGroup('transaction_window', ['enabled' => false]);
        Settings::writeGroup('membership', ['account_hold_amount' => '0.00', 'registration_fee' => '0.00']);
        Settings::writeGroup('referral_milestones', ['enabled' => false]);
        $members = [];
        $accounts = [];
        foreach (['Customer', 'Agent member'] as $name) {
            $c = Supplier::create(['type' => 'Customer', 'name' => $name, 'status' => 1, 'member_no' => 'AT-'.Str::random(12), 'origin_sister_concern_id' => $this->branch->id, 'pin' => Hash::make('123456')]);
            CustomerKyc::create(['customer_id' => $c->id, 'submitted_name' => $c->name, 'nid_number' => '1234567890', 'nid_front_path' => 'test', 'nid_back_path' => 'test', 'selfie_path' => 'test', 'status' => 'Verified']);
            $a = RegistrationSavingsService::ensure($c);
            $a->update(['principal_balance' => '10000.00']);
            $members[] = $c;
            $accounts[] = $a;
        }
        [$this->customer,$this->agentMember] = $members;
        [$this->source,$this->target] = $accounts;
        $this->agent = MembershipAgent::create(['customer_id' => $this->agentMember->id, 'agent_code' => 'AG-'.$this->agentMember->id, 'status' => 'Active', 'approved_by' => $this->operator->id, 'approved_at' => now()]);
        $income = AccountHead::create(['name' => 'Fixture agent income', 'nature' => 'Credit', 'status' => 1, 'account_group_id' => AccountGroup::where('reserved_id', 23)->firstOrFail()->id]);
        $this->expense = AccountHead::create(['name' => 'Fixture agent expense', 'nature' => 'Debit', 'status' => 1, 'account_group_id' => AccountGroup::where('reserved_id', 26)->firstOrFail()->id]);
        foreach (['transfer', 'agent_cashin', 'agent_cashout'] as $group) {
            Settings::writeGroup($group, ['enabled' => true, 'minimum_amount' => '1.00', 'maximum_amount' => '5000.00', 'charge_type' => 'fixed', 'charge_rate' => $group === 'agent_cashout' ? '20.00' : '0.00', 'income_head_id' => $income->id, 'posting_user_id' => $this->operator->id]);
        }
        Settings::writeGroup('agent_cashout', ['commission_type' => 'fixed', 'commission_rate' => '12.00', 'expense_head_id' => $this->expense->id]);
        $this->service = app(AgentTransactionService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function quote(string $kind = 'agent_cashout'): array
    {
        return $this->service->quote($kind === 'agent_cashin' ? $this->agentMember : $this->customer, $kind, $kind === 'agent_cashin' ? $this->customer->member_no : $this->agent->agent_code, '1000.00');
    }

    private function complete(string $kind = 'agent_cashout', ?array $quote = null, ?string $key = null)
    {
        $q = $quote ?? $this->quote($kind);

        return $this->service->complete($kind === 'agent_cashin' ? $this->agentMember : $this->customer, $kind, $q['quote'], '123456', $key ?? (string) Str::uuid());
    }

    private function balanced(): void
    {
        foreach (MembershipTransfer::whereIn('sender_id', [$this->customer->id, $this->agentMember->id])->get() as $t) {
            foreach (array_filter([$t->voucher_id, $t->recipient_voucher_id, $t->fee_voucher_id]) as $id) {
                $this->assertEquals(Transaction::where('voucher_id', $id)->where('transaction_type', 1)->sum('amount'), Transaction::where('voucher_id', $id)->where('transaction_type', 2)->sum('amount'));
            }
        }
        foreach (MembershipAgentTransaction::where('agent_id', $this->agent->id)->whereNotNull('commission_voucher_id')->get() as $r) {
            $this->assertEquals(Transaction::where('voucher_id', $r->commission_voucher_id)->where('transaction_type', 1)->sum('amount'), Transaction::where('voucher_id', $r->commission_voucher_id)->where('transaction_type', 2)->sum('amount'));
        }
    }

    public function test_cashout_is_immediate_charge_and_commission_are_separate_and_retry_is_safe(): void
    {
        $q = $this->quote();
        $key = (string) Str::uuid();
        $r = $this->complete('agent_cashout', $q, $key);
        $this->assertSame('Completed', $r->status);
        $this->assertEquals(8980, $this->source->fresh()->principal_balance);
        $this->assertEquals(11012, $this->target->fresh()->principal_balance);
        $this->assertEquals(20, $r->customer_charge);
        $this->assertEquals(12, $r->agent_commission);
        Carbon::setTestNow('2026-11-24 21:00:00');
        $this->assertSame($r->id, $this->complete('agent_cashout', $q, $key)->id);
        $this->assertEquals(11012, $this->target->fresh()->principal_balance);
        $history = app(RecentTransactionService::class)->forCustomer($this->agentMember);
        $this->assertCount(1, array_filter($history, fn ($h) => $h['category_en'] === 'Agent commission'));
        $this->balanced();
    }

    public function test_member_cashin_debits_same_agent_savings_without_fee(): void
    {
        $r = $this->complete('agent_cashin');
        $this->assertEquals(9000, $this->target->fresh()->principal_balance);
        $this->assertEquals(11000, $this->source->fresh()->principal_balance);
        $this->assertEquals(0, $r->customer_charge);
        $this->assertEquals(0, $r->agent_commission);
        $this->assertNull($r->commission_voucher_id);
        $this->balanced();
    }

    public function test_invalid_commission_ledger_rolls_back_every_balance_and_transfer(): void
    {
        Settings::writeGroup('agent_cashout', ['expense_head_id' => null]);
        try {
            $this->complete();
            $this->fail('Expected invalid ledger');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('commission', $e->getMessage());
        }
        $this->assertEquals(10000, $this->source->fresh()->principal_balance);
        $this->assertEquals(10000, $this->target->fresh()->principal_balance);
        $this->assertSame(0, MembershipAgentTransaction::where('agent_id', $this->agent->id)->count());
        $this->assertSame(0, MembershipTransfer::where('sender_id', $this->customer->id)->count());
    }

    public function test_suspend_after_quote_blocks_cashout_and_agent_cannot_use_send_money(): void
    {
        $q = $this->quote();
        $this->agent->update(['status' => 'Suspended']);
        try {
            $this->complete('agent_cashout', $q);
            $this->fail('Expected suspended agent');
        } catch (ValidationException) {
        }
        try {
            (new TransferService)->quote($this->agentMember, $this->customer->member_no, '1000');
            $this->fail('Expected agent send money block');
        } catch (ValidationException) {
        }
        $this->assertEquals(10000, $this->source->fresh()->principal_balance);
    }

    public function test_daily_window_blocks_quotes_and_final_confirmation_but_not_cashout(): void
    {
        Settings::writeGroup('transaction_window', ['enabled' => true, 'start_time' => '20:00', 'end_time' => '22:00']);
        Carbon::setTestNow('2026-11-24 19:59:30');
        $q = $this->quote('agent_cashin');
        $send = (new TransferService)->quote($this->customer, $this->agentMember->member_no, '1000');
        Carbon::setTestNow('2026-11-24 20:00:00');
        foreach ([fn () => $this->complete('agent_cashin', $q), fn () => (new TransferService)->send($this->customer, $send['quote'], '123456', (string) Str::uuid()), fn () => $this->quote('agent_cashin')] as $call) {
            try {
                $call();
                $this->fail('Expected closing window');
            } catch (ValidationException) {
            }
        }
        $this->assertEquals(10000, $this->source->fresh()->principal_balance);
        $this->assertEquals(10000, $this->target->fresh()->principal_balance);
        $this->complete();
        $this->assertEquals(8980, $this->source->fresh()->principal_balance);
    }

    public function test_window_boundaries_overnight_and_disabled(): void
    {
        foreach ([['20:00', '22:00', '19:59:59', false], ['20:00', '22:00', '20:00:00', true], ['20:00', '22:00', '22:00:00', false], ['23:00', '05:00', '23:00:00', true], ['23:00', '05:00', '00:00:00', true], ['23:00', '05:00', '04:59:59', true], ['23:00', '05:00', '05:00:00', false]] as [$start,$end,$time,$blocked]) {
            Settings::writeGroup('transaction_window', ['enabled' => true, 'start_time' => $start, 'end_time' => $end]);
            Carbon::setTestNow('2026-11-24 '.$time);
            $this->assertSame($blocked, TransactionWindowService::status()['blocked']);
        }
        Settings::writeGroup('transaction_window', ['enabled' => false]);
        Carbon::setTestNow('2026-11-24 01:00:00');
        $this->assertFalse(TransactionWindowService::status()['blocked']);
    }

    public function test_api_channel_and_owner_are_enforced_and_dashboard_exposes_role(): void
    {
        Sanctum::actingAs($this->customer);
        $this->postJson('/api/membership/v1/agent/agent_cashin/quote', ['recipient' => $this->agentMember->member_no, 'amount' => '1000'])->assertUnprocessable();
        $this->getJson('/api/membership/v1/agent/agent_cashout')->assertOk()->assertJsonPath('data.enabled', true);
        $q = $this->quote();
        $this->postJson('/api/membership/v1/agent/agent_cashout', ['quote' => $q['quote'], 'pin' => '123456', 'idempotency_key' => (string) Str::uuid()])->assertCreated()->assertJsonPath('data.status', 'Completed');
        Sanctum::actingAs($this->agentMember);
        $this->postJson('/api/membership/v1/agent/agent_cashin/quote', ['recipient_id' => $this->customer->id, 'amount' => '1000.00'])->assertOk()->assertJsonPath('data.recipient_id', $this->customer->id);
        $this->getJson('/api/membership/v1/dashboard')->assertOk()->assertJsonPath('data.agent.code', $this->agent->agent_code);
        $this->postJson('/api/membership/v1/agent/agent_cashin', ['quote' => $q['quote'], 'pin' => '123456', 'idempotency_key' => (string) Str::uuid()])->assertUnprocessable();
    }

    public function test_cashout_cross_branch_settlement_is_balanced_and_commission_is_in_agent_branch(): void
    {
        $other = SisterConcern::where('status', 1)->whereKeyNot($this->branch->id)->firstOrFail();
        $this->target->update(['origin_sister_concern_id' => $other->id]);
        $r = $this->complete();
        $this->assertNotNull($r->transfer->settlement_id);
        $this->assertEquals($other->id, Voucher::findOrFail($r->commission_voucher_id)->sister_concern_id);
        $this->balanced();
    }

    public function test_wrong_pin_and_changed_commission_settings_cannot_move_funds(): void
    {
        $q = $this->quote();
        try {
            $this->service->complete($this->customer, 'agent_cashout', $q['quote'], '000000', (string) Str::uuid());
            $this->fail('Expected wrong PIN');
        } catch (ValidationException) {
        }
        Settings::writeGroup('agent_cashout', ['commission_rate' => '13.00']);
        try {
            $this->complete('agent_cashout', $q);
            $this->fail('Expected stale pricing');
        } catch (ValidationException) {
        }
        $this->assertEquals(10000, $this->source->fresh()->principal_balance);
        $this->assertEquals(10000, $this->target->fresh()->principal_balance);
    }

    public function test_settings_save_window_and_independent_commission_and_restrict_non_admin(): void
    {
        $this->withoutMiddleware();
        $this->operator->update(['role' => 'Admin']);
        $this->actingAs($this->operator);
        $this->post('/admin/card-management/transaction-settings/transaction_window', ['enabled' => '1', 'start_time' => '23:00', 'end_time' => '05:00'])->assertRedirect();
        $this->assertTrue(membership_transaction_settings('transaction_window')->enabled);
        $this->assertSame('23:00', membership_transaction_settings('transaction_window')->start_time);
        $this->post('/admin/card-management/transaction-settings/transaction_window', ['start_time' => '23:00', 'end_time' => '05:00'])->assertRedirect();
        $this->assertFalse(membership_transaction_settings('transaction_window')->enabled);
        $this->postJson('/admin/card-management/transaction-settings/transaction_window', ['enabled' => '1', 'start_time' => '20:00', 'end_time' => '20:00'])->assertUnprocessable();
        $s = membership_transaction_settings('agent_cashout');
        $this->post('/admin/card-management/transaction-settings/agent_cashout', [
            'enabled' => 1, 'minimum_amount' => '1.00', 'maximum_amount' => '5000.00', 'charge_type' => 'fixed', 'charge_rate' => '20.00',
            'income_head_id' => $s->income_head_id, 'commission_type' => 'percentage', 'commission_rate' => '1.20', 'expense_head_id' => $this->expense->id,
            'posting_user_id' => 999999,
        ])->assertRedirect();
        $saved = membership_transaction_settings('agent_cashout');
        $this->assertSame('1.20', $saved->commission_rate);
        $this->assertSame('20.00', $saved->charge_rate);
        $this->assertSame($this->operator->id, $saved->posting_user_id);
        $this->operator->update(['role' => 'Employee']);
        $this->postJson('/admin/card-management/transaction-settings/transaction_window', ['start_time' => '20:00', 'end_time' => '22:00'])->assertForbidden();
    }

    public function test_agents_can_be_promoted_suspended_and_qr_is_branch_scoped(): void
    {
        $this->withoutMiddleware();
        $this->operator->update(['role' => 'Admin']);
        $this->actingAs($this->operator);
        $this->post('/admin/card-management/agents', ['member' => $this->customer->member_no])->assertRedirect();
        $a = MembershipAgent::where('customer_id', $this->customer->id)->firstOrFail();
        $this->assertSame('Active', $a->status);
        $this->post('/admin/card-management/agents/'.$a->id.'/status', ['status' => 'Suspended'])->assertRedirect();
        $this->assertSame('Suspended', $a->fresh()->status);
        $this->get('/admin/card-management/agents/'.$a->id.'/qr')->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
        $other = SisterConcern::whereKeyNot($this->branch->id)->firstOrFail();
        $this->withSession(['active_sister_concern_id' => $other->id])->get('/admin/card-management/agents/'.$a->id.'/qr')->assertForbidden();
    }
}
