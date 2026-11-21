<?php

namespace Tests\Integration;

use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\ReservedAccountHead;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\BranchManagement\SisterConcern;
use App\Models\MembershipTransactionSetting;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiDailyInterest;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\Supplier;
use App\Services\ShomriddhiAdvance\CapitalizationService;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MonthlyInterestCapitalizationTest extends TestCase
{
    use DatabaseTransactions;

    private ShomriddhiAccount $account;
    private AccountHead $savings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-11-30 23:25:00'));
        MembershipTransactionSetting::writeGroup('membership', ['registration_fee' => '0.00', 'account_hold_amount' => '0.00']);
        $member = Supplier::create(['name' => 'Monthly capitalization fixture', 'type' => 'Customer', 'status' => 1]);
        $branch = SisterConcern::where('status', 1)->firstOrFail();
        $this->account = ShomriddhiAccount::create(['customer_id' => $member->id, 'origin_sister_concern_id' => $branch->id,
            'principal_balance' => '10000.00', 'accrued_interest' => '10.1299', 'last_interest_date' => '2026-11-30', 'status' => 'Active']);
        $this->savings = AccountHead::create(['name' => 'Interest fixture savings', 'nature' => 'Credit', 'head_type' => 'savings',
            'supplier_id' => $member->id, 'status' => 1, 'account_group_id' => AccountGroup::where('reserved_id', 28)->firstOrFail()->id]);
        $expense = AccountHead::firstOrCreate(['reserved_id' => ReservedAccountHead::SHOMRIDDHI_INTEREST_EXPENSE],
            ['name' => 'Interest fixture expense', 'nature' => 'Debit', 'status' => 1,
                'account_group_id' => AccountGroup::where('reserved_id', ReservedAccountGroup::FINANCIAL_EXPENSES)->firstOrFail()->id]);
        $expense->update(['status' => 1, 'nature' => 'Debit', 'sister_concern_id' => null]);
        ShomriddhiDailyInterest::create(['customer_id' => $member->id, 'origin_sister_concern_id' => $branch->id,
            'date' => '2026-11-30', 'principal_balance' => 10000, 'interest_rate' => 6, 'earned_interest' => '10.1299', 'status' => 'Pending']);
    }

    private function assertUnchanged(): void
    {
        $this->assertSame('10000.00', $this->account->fresh()->principal_balance);
        $this->assertSame('10.1299', $this->account->fresh()->accrued_interest);
        $this->assertSame(0, ShomriddhiTransaction::where('customer_id', $this->account->customer_id)->count());
        $this->assertSame('Pending', ShomriddhiDailyInterest::where('customer_id', $this->account->customer_id)->firstOrFail()->status);
    }

    public function test_monthly_posts_balanced_ledger_once_and_preserves_fractional_interest(): void
    {
        $service = app(CapitalizationService::class);
        $service->capitalizeMonthly($this->account);
        $this->assertSame('10010.12', $this->account->fresh()->principal_balance);
        $this->assertSame('0.0099', $this->account->fresh()->accrued_interest);
        $entry = ShomriddhiTransaction::where('customer_id', $this->account->customer_id)->firstOrFail();
        $this->assertNotNull($entry->voucher_id);
        $this->assertEquals('10.12', Transaction::where('voucher_id', $entry->voucher_id)->where('transaction_type', 1)->sum('amount'));
        $this->assertEquals('10.12', Transaction::where('voucher_id', $entry->voucher_id)->where('transaction_type', 2)->sum('amount'));
        // A stale caller and newly accrued interest must not bypass the monthly guard.
        DB::table('shomriddhi_accounts')->where('id', $this->account->id)->update(['accrued_interest' => '2.0099']);
        $service->capitalizeMonthly($this->account);
        $this->assertSame(1, ShomriddhiTransaction::where('customer_id', $this->account->customer_id)->count());
        $this->assertSame('10010.12', $this->account->fresh()->principal_balance);
        // The distinct full-withdrawal flow may settle newly accrued interest.
        $service->capitalize($this->account);
        $this->assertSame('10012.12', $this->account->fresh()->principal_balance);
        $this->assertSame('0.0099', $this->account->fresh()->accrued_interest);
    }

    public function test_missing_savings_ledger_blocks_every_balance_and_history_change(): void
    {
        $this->savings->delete();
        try {
            app(CapitalizationService::class)->capitalizeMonthly($this->account);
            $this->fail('Missing ledger was allowed.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('savings ledger', $e->getMessage());
        }
        $this->assertUnchanged();
    }

    public function test_second_gl_entry_failure_rolls_back_the_entire_posting_and_can_retry(): void
    {
        $before = Voucher::count();
        $entries = 0;
        $failOnce = true;
        DB::listen(function (QueryExecuted $query) use (&$entries, &$failOnce) {
            if ($failOnce && str_starts_with(strtolower($query->sql), 'insert into `transactions`') && ++$entries === 2) {
                $failOnce = false;
                throw new \RuntimeException('Simulated credit posting failure');
            }
        });
        try {
            app(CapitalizationService::class)->capitalizeMonthly($this->account);
            $this->fail('Expected GL posting failure.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulated credit posting failure', $e->getMessage());
        }
        $this->assertUnchanged();
        $this->assertSame($before, Voucher::count());
        app(CapitalizationService::class)->capitalizeMonthly($this->account);
        $this->assertSame('10010.12', $this->account->fresh()->principal_balance);
    }

    public function test_invalid_expense_head_blocks_posting_and_command_reports_failure(): void
    {
        AccountHead::where('reserved_id', ReservedAccountHead::SHOMRIDDHI_INTEREST_EXPENSE)->update(['status' => 0]);
        try {
            app(CapitalizationService::class)->capitalizeMonthly($this->account);
            $this->fail('Inactive expense ledger was allowed.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('expense ledger', $e->getMessage());
        }
        $this->assertUnchanged();
        $mock = \Mockery::mock(CapitalizationService::class);
        $mock->shouldReceive('recoverMonthly')->andThrow(new \RuntimeException('Simulated monthly failure'));
        $this->app->instance(CapitalizationService::class, $mock);
        $this->artisan('shomriddhi:capitalize-monthly-interest')->assertExitCode(1);
        $this->assertUnchanged();
    }
    public function test_missed_month_end_recovers_later_without_capitalizing_current_month(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-12-03 23:25:00'));
        $this->account->update(['accrued_interest' => '13.1299', 'last_interest_date' => '2026-12-03']);
        ShomriddhiDailyInterest::create(['customer_id' => $this->account->customer_id,
            'origin_sister_concern_id' => $this->account->origin_sister_concern_id,
            'date' => '2026-12-03', 'principal_balance' => 10000, 'interest_rate' => 6, 'earned_interest' => '3.0000', 'status' => 'Pending']);
        $service = app(CapitalizationService::class);
        $service->recoverMonthly($this->account);
        $entry = ShomriddhiTransaction::where('customer_id', $this->account->customer_id)->firstOrFail();
        $this->assertSame('2026-11-30', $entry->date);
        $this->assertEquals('10.12', $entry->amount);
        $this->assertSame('3.0099', $this->account->fresh()->accrued_interest);
        $this->assertSame('Pending', ShomriddhiDailyInterest::where('customer_id', $this->account->customer_id)->where('date', '2026-12-03')->value('status'));
        $service->recoverMonthly($this->account);
        $this->assertSame(1, ShomriddhiTransaction::where('customer_id', $this->account->customer_id)->count());
    }

    public function test_recovery_handles_multiple_closed_months_in_order(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2027-01-05 23:25:00'));
        $this->account->update(['accrued_interest' => '30.1299', 'last_interest_date' => '2027-01-05']);
        ShomriddhiDailyInterest::create(['customer_id' => $this->account->customer_id,
            'origin_sister_concern_id' => $this->account->origin_sister_concern_id,
            'date' => '2026-12-31', 'principal_balance' => 10000, 'interest_rate' => 6, 'earned_interest' => '20.0000', 'status' => 'Pending']);
        app(CapitalizationService::class)->recoverMonthly($this->account);
        $entries = ShomriddhiTransaction::where('customer_id', $this->account->customer_id)->orderBy('date')->get();
        $this->assertSame(['2026-11-30', '2026-12-31'], $entries->pluck('date')->all());
        $this->assertEquals(['10.12', '20.00'], $entries->pluck('amount')->all());
        $this->assertSame('10030.12', $this->account->fresh()->principal_balance);
        $this->assertSame('0.0099', $this->account->fresh()->accrued_interest);
    }

    public function test_incomplete_daily_history_blocks_monthly_recovery(): void
    {
        $this->account->update(['last_interest_date' => '2026-11-29']);
        try {
            app(CapitalizationService::class)->recoverMonthly($this->account);
            $this->fail('Incomplete month was capitalized.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Daily interest is incomplete', $e->getMessage());
        }
        $this->assertUnchanged();
    }

}
