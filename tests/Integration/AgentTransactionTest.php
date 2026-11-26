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
    public function test_agent_datatable_search_sort_pagination_and_branch_scope(): void
    {
        $this->withoutMiddleware();
        $this->operator->update(['role' => 'Admin']);
        $mobile = '019'.str_pad((string) $this->agentMember->id, 8, '0', STR_PAD_LEFT);
        $this->agentMember->update(['name' => 'Zebra <Agent>', 'mobile_no' => $mobile]);
        $this->customer->update(['name' => 'Alpha Agent', 'mobile_no' => '01850637286']);
        MembershipAgent::create(['customer_id' => $this->customer->id, 'agent_code' => 'AG-'.$this->customer->id, 'status' => 'Suspended', 'approved_by' => $this->operator->id, 'approved_at' => now()]);
        $other = SisterConcern::whereKeyNot($this->branch->id)->firstOrFail();
        $foreign = Supplier::create(['type' => 'Customer', 'name' => 'Outside Agent', 'status' => 1, 'origin_sister_concern_id' => $other->id]);
        MembershipAgent::create(['customer_id' => $foreign->id, 'agent_code' => 'AG-'.$foreign->id, 'status' => 'Active', 'approved_by' => $this->operator->id, 'approved_at' => now()]);
        $columns = collect([
            ['agent_code', 'agent_code'], ['member_name', 'customer.name'], ['member_no', 'customer.member_no'],
            ['mobile_no', 'customer.mobile_no'], ['status', 'status'], ['action', 'action'],
        ])->map(fn ($c) => ['data' => $c[0], 'name' => $c[1], 'searchable' => 'true', 'orderable' => 'true'])->all();
        $params = ['draw' => 1, 'start' => 0, 'length' => 1, 'columns' => $columns,
            'order' => [['column' => 1, 'dir' => 'asc']], 'search' => ['value' => '']];
        $request = fn ($p) => $this->withSession(['active_sister_concern_id' => $this->branch->id])->getJson(route('card-management.agents.index', $p), ['X-Requested-With' => 'XMLHttpRequest']);
        view()->share('errors', new \Illuminate\Support\ViewErrorBag);
        $this->get(route('card-management.agents.index'))->assertOk()->assertSee('agents-table');
        foreach ([$mobile, $this->agentMember->member_no, $this->agent->agent_code, 'Zebra'] as $term) {
            $response = $request(array_replace($params, ['search' => ['value' => $term]]));
            $this->assertSame(1, $response->json('recordsFiltered'), 'Search failed for fixture term');
            $response->assertOk()
                ->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.id', $this->agent->id)
                ->assertJsonPath('data.0.member_name', 'Zebra &lt;Agent&gt;')
                ->assertJsonPath('data.0.mobile_no', $mobile);
        }
        $request(array_replace($params, ['search' => ['value' => 'Outside Agent']]))->assertOk()->assertJsonPath('recordsFiltered', 0);
        $page = $request(array_replace($params, ['search' => ['value' => 'Agent']]))->assertOk();
        $this->assertCount(1, $page->json('data'));
        $this->assertSame('Alpha Agent', $page->json('data.0.member_name'));
        $request(array_replace($params, ['search' => ['value' => str_repeat('x', 101)]]))->assertUnprocessable();
        $this->operator->update(['role' => 'Staff']);
        $request($params)->assertForbidden();
    }

    public function test_removed_agent_returns_to_member_without_changing_balances_or_history_and_can_be_restored(): void
    {
        $record = $this->complete();
        $stale = $this->quote();
        $balances = [$this->source->fresh()->principal_balance, $this->target->fresh()->principal_balance];
        $transactionCount = Transaction::count();
        $this->withoutMiddleware();
        $this->operator->update(['role' => 'Admin']);
        $url = '/admin/card-management/agents/'.$this->agent->id.'/status';
        $this->withSession(['active_sister_concern_id' => $this->branch->id])->post($url, ['status' => 'Removed'])->assertRedirect();
        $this->assertSame('Removed', $this->agent->fresh()->status);
        $this->assertNull(MembershipAgent::data($this->agentMember->id));
        $this->assertSame($balances, [$this->source->fresh()->principal_balance, $this->target->fresh()->principal_balance]);
        $this->assertSame($transactionCount, Transaction::count());
        $this->assertSame('Completed', $record->fresh()->status);
        $this->assertEquals(12, $record->fresh()->agent_commission);
        $this->assertSame($this->agent->id, $record->fresh()->agent->id);
        try {
            $this->complete('agent_cashout', $stale);
            $this->fail('A reviewed agent cashout must be blocked after removal');
        } catch (ValidationException) {
        }
        foreach (['agent_cashin', 'agent_cashout'] as $kind) {
            try {
                $this->quote($kind);
                $this->fail('Removed agent services must be blocked');
            } catch (ValidationException) {
            }
        }
        $normal = (new TransferService)->quote($this->agentMember, $this->customer->member_no, '10.00');
        $this->assertArrayHasKey('quote', $normal);
        $this->assertSame($balances, [$this->source->fresh()->principal_balance, $this->target->fresh()->principal_balance]);
        $this->withSession(['active_sister_concern_id' => $this->branch->id])->postJson($url, ['status' => 'Suspended'])->assertUnprocessable();
        $this->withSession(['active_sister_concern_id' => $this->branch->id])->get('/admin/card-management/agents/'.$this->agent->id.'/qr')->assertNotFound();
        // Make Agent on the original member reuses the same record and history.
        $this->withSession(['active_sister_concern_id' => $this->branch->id])->post('/admin/card-management/agents', ['member' => $this->agentMember->member_no])->assertRedirect();
        $this->assertSame('Active', $this->agent->fresh()->status);
        $this->assertSame($this->agent->id, MembershipAgent::data($this->agentMember->id)['id']);
        $this->assertSame($balances, [$this->source->fresh()->principal_balance, $this->target->fresh()->principal_balance]);
        $this->assertArrayHasKey('quote', $this->quote());
    }

    public function test_removal_requires_admin_and_own_branch(): void
    {
        $this->withoutMiddleware();
        $this->operator->update(['role' => 'Admin']);
        $other = SisterConcern::whereKeyNot($this->branch->id)->firstOrFail();
        $url = '/admin/card-management/agents/'.$this->agent->id.'/status';
        $this->withSession(['active_sister_concern_id' => $other->id])->post($url, ['status' => 'Removed'])->assertForbidden();
        $this->operator->update(['role' => 'Staff']);
        $this->withSession(['active_sister_concern_id' => $this->branch->id])->post($url, ['status' => 'Removed'])->assertForbidden();
        $this->assertSame('Active', $this->agent->fresh()->status);
    }

    public function test_agent_transaction_datatable_filters_searches_and_preserves_branch_scope(): void
    {
        $cashout = $this->complete();
        $cashin = $this->complete('agent_cashin');
        $cashout->update(['completed_at' => '2026-11-23 23:59:59']);
        $cashin->update(['completed_at' => '2026-11-24 00:00:00']);
        $this->agentMember->update(['name' => 'UniqueAgent <Test>']);
        $mobile = '019'.str_pad((string) $this->customer->id, 8, '0', STR_PAD_LEFT);
        $this->customer->update(['mobile_no' => $mobile]);
        $this->withoutMiddleware();
        $this->operator->update(['role' => 'Admin']);
        view()->share('errors', new \Illuminate\Support\ViewErrorBag);
        $this->get(route('card-management.agents.transactions'))->assertOk()->assertSee('agent-transactions-table');
        $params = ['draw' => 1, 'start' => 0, 'length' => 1, 'columns' => [
            ['data' => 'completed_at', 'name' => 'completed_at', 'orderable' => 'true', 'searchable' => 'true'],
        ], 'order' => [['column' => 0, 'dir' => 'desc']], 'search' => ['value' => '']];
        $request = fn ($extra) => $this->withSession(['active_sister_concern_id' => $this->branch->id])
            ->getJson(route('card-management.agents.transactions', array_replace($params, $extra)), ['X-Requested-With' => 'XMLHttpRequest']);
        $request(['search' => ['value' => $this->agent->agent_code]])->assertOk()->assertJsonPath('recordsFiltered', 2)
            ->assertJsonPath('data.0.id', $cashin->id)->assertJsonPath('data.0.type_label', 'Member Cash-in');
        $request(['type' => 'agent_cashout', 'search' => ['value' => $mobile]])->assertOk()->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.id', $cashout->id)->assertJsonPath('data.0.customer_charge', '20.00')
            ->assertJsonPath('data.0.agent_commission', '12.00');
        $request(['start_date' => '2026-11-24', 'end_date' => '2026-11-24', 'search' => ['value' => 'UniqueAgent']])
            ->assertOk()->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.id', $cashin->id)
            ->assertJsonPath('data.0.agent_label', 'UniqueAgent &lt;Test&gt; / '.$this->agent->agent_code);
        $request(['search' => ['value' => $cashout->reference]])->assertOk()->assertJsonPath('recordsFiltered', 1);
        $request(['search' => ['value' => $this->customer->member_no]])->assertOk()->assertJsonPath('recordsFiltered', 2);
        $request(['start_date' => '2026-11-25', 'end_date' => '2026-11-24'])->assertUnprocessable();
        $request(['type' => 'bad'])->assertUnprocessable();
        // Records remain visible when the agent returns to regular membership.
        $this->agent->update(['status' => 'Removed']);
        $request(['search' => ['value' => $cashout->reference]])->assertOk()->assertJsonPath('recordsFiltered', 1);
        $other = SisterConcern::whereKeyNot($this->branch->id)->firstOrFail();
        $this->withSession(['active_sister_concern_id' => $other->id])->getJson(route('card-management.agents.transactions', array_replace($params, ['search' => ['value' => $cashout->reference]])), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJsonPath('recordsFiltered', 0);
        $this->operator->update(['role' => 'Staff']);
        $request([])->assertForbidden();
    }

    public function test_make_agent_wrong_branch_returns_field_error_and_keeps_input(): void
    {
        $this->withoutMiddleware();
        $this->operator->update(['role' => 'Admin']);
        $other = SisterConcern::whereKeyNot($this->branch->id)->firstOrFail();
        $url = route('card-management.agents.index');
        $this->withSession(['active_sister_concern_id' => $other->id])->from($url)
            ->post($url, ['member' => $this->customer->member_no])
            ->assertRedirect($url)->assertSessionHasErrors('member')
            ->assertSessionHasInput('member', $this->customer->member_no);
        $this->assertFalse(MembershipAgent::where('customer_id', $this->customer->id)->exists());
        $this->withSession(['active_sister_concern_id' => $other->id])->postJson($url, ['member' => $this->customer->member_no])
            ->assertUnprocessable()->assertJsonValidationErrors('member');
        $this->withSession(['active_sister_concern_id' => $this->branch->id])->post($url, ['member' => $this->customer->member_no])->assertRedirect();
        $this->assertSame('Active', MembershipAgent::where('customer_id', $this->customer->id)->value('status'));
    }

}
