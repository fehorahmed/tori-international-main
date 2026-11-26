<?php

namespace Tests\Integration;

use App\Http\Controllers\Account\ReportController;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\BranchManagement\SisterConcern;
use App\Models\User;
use App\Services\Reports\DailyTransactionReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class DailyTransactionReportTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;
    private int $branch;
    private int $otherBranch;
    private array $heads;
    private string $day = '2088-04-15';

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::firstOrFail();
        $this->branch = SisterConcern::firstOrFail()->id;
        $this->otherBranch = SisterConcern::where('id', '!=', $this->branch)->firstOrFail()->id;
        $this->actingAs($this->user);
        session(['active_sister_concern_id' => $this->branch]);
        $income = AccountGroup::create(['name' => 'Report test income', 'reserved_id' => 4]);
        $incomeChild = AccountGroup::create(['name' => 'Report test charge', 'account_group_id' => $income->id]);
        $expense = AccountGroup::create(['name' => 'Report test expense', 'reserved_id' => 5]);
        $liability = AccountGroup::create(['name' => 'Report test liability', 'reserved_id' => 2]);
        $asset = AccountGroup::create(['name' => 'Report test asset', 'reserved_id' => 1]);
        $this->heads = [
            'income' => AccountHead::create(['name' => 'App charge <test>', 'account_group_id' => $incomeChild->id, 'nature' => 'Credit']),
            'expense' => AccountHead::create(['name' => 'Agent commission test', 'account_group_id' => $expense->id]),
            'cash' => AccountHead::create(['name' => 'Cash test', 'account_group_id' => $asset->id, 'payment_mode' => 2]),
            'bank' => AccountHead::create(['name' => 'Bank test', 'account_group_id' => $asset->id, 'payment_mode' => 1]),
            'savings' => AccountHead::create(['name' => 'Member savings test', 'account_group_id' => $liability->id]),
        ];
    }

    private function entry(string $head, int $type, string $amount, array $extra = []): Transaction
    {
        return Transaction::create($extra + [
            'account_head_id' => $this->heads[$head]->id, 'date' => $this->day,
            'sister_concern_id' => $this->branch, 'transaction_type' => $type, 'amount' => $amount,
            'voucher_id' => 0, 'voucher_no_group_sl' => 1, 'voucher_no' => 'DAILY-REPORT-TEST',
            'voucher_type' => 1, 'user_id' => $this->user->id, 'notes' => '<script>test</script>',
        ]);
    }

    public function test_non_cash_charges_expenses_reversals_and_principal_are_separate(): void
    {
        // Cash deposit is a liability movement, then a fee and commission
        // settle within savings without touching cash.
        $this->entry('cash', 1, '1000.25');
        $this->entry('savings', 2, '1000.25');
        $this->entry('savings', 1, '20.55');
        $this->entry('income', 2, '20.55');
        $this->entry('income', 1, '5.10'); // Fee refund.
        $this->entry('savings', 2, '5.10');
        $this->entry('expense', 1, '12.34');
        $this->entry('savings', 2, '12.34');
        $this->entry('expense', 2, '2.04'); // Commission reversal.
        $this->entry('savings', 1, '2.04');
        $data = app(DailyTransactionReport::class)->summary($this->day, $this->day, $this->branch);
        $this->assertEquals(['income' => 15.45, 'expense' => 10.30, 'cash_in' => 1000.25, 'cash_out' => 0, 'net_income' => 5.15], $data['totals']);
        $this->assertCount(1, $data['sections']['income']);
        $this->assertSame(2, $data['sections']['income'][0]['count']);
        $this->assertCount(1, $data['sections']['other']);
        // A retired head must still appear in historical totals.
        $this->heads['income']->delete();
        $this->assertEquals($data, app(DailyTransactionReport::class)->summary($this->day, $this->day, $this->branch));
    }

    public function test_deleted_opening_out_of_date_and_other_branch_entries_are_excluded(): void
    {
        $this->entry('income', 2, '1.11');
        $this->entry('income', 2, '100', ['is_opening_balance' => 1]);
        $this->entry('income', 2, '100', ['deleted_at' => now()]);
        $this->entry('income', 2, '100', ['date' => '2088-04-14']);
        $this->entry('income', 2, '100', ['date' => '2088-04-16']);
        $this->entry('income', 2, '2.22', ['sister_concern_id' => $this->otherBranch]);
        $service = app(DailyTransactionReport::class);
        $this->assertEquals(1.11, $service->summary($this->day, $this->day, $this->branch)['totals']['income']);
        $this->assertEquals(3.33, $service->summary($this->day, $this->day, null)['totals']['income']);
    }

    public function test_internal_cash_transfers_are_gross_cash_movements_not_income(): void
    {
        $this->entry('cash', 2, '50');
        $this->entry('bank', 1, '50');
        $data = app(DailyTransactionReport::class)->summary($this->day, $this->day, $this->branch);
        $this->assertEquals(0, $data['totals']['income']);
        $this->assertEquals(0, $data['totals']['expense']);
        $this->assertEquals(50, $data['totals']['cash_in']);
        $this->assertEquals(50, $data['totals']['cash_out']);
    }

    public function test_branch_scope_is_enforced_for_summary_and_details(): void
    {
        foreach (['dailyTransactions', 'dailyTransactionsDetails'] as $method) {
            try {
                (new ReportController)->$method(Request::create('/', 'GET', ['branch_id' => $this->otherBranch, 'account_head_id' => $this->heads['income']->id]));
                $this->fail('Cross-branch access must be rejected');
            } catch (HttpException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }
        }
    }

    public function test_invalid_dates_ranges_and_categories_are_rejected(): void
    {
        foreach ([['start_date' => '31-02-2088'], ['start_date' => '2088-04-16', 'end_date' => '2088-04-15'], ['category' => 'invalid']] as $params) {
            try {
                (new ReportController)->dailyTransactions(Request::create('/', 'GET', $params));
                $this->fail('Invalid filters must be rejected');
            } catch (ValidationException $e) {
                $this->assertNotEmpty($e->errors());
            }
        }
    }

    public function test_summary_details_and_category_filter_render_with_identical_totals(): void
    {
        $this->entry('income', 2, '50.25');
        $this->entry('income', 1, '10.10');
        $this->entry('expense', 1, '5.00');
        $controller = new ReportController;
        $params = ['start_date' => '15-04-2088', 'end_date' => '2088-04-15', 'category' => 'income', 'search' => 1];
        $summary = $controller->dailyTransactions(Request::create('/', 'GET', $params));
        $this->assertEquals(40.15, $summary->getData()['report']['totals']['income']);
        $html = $summary->render();
        $this->assertStringContainsString('App charge &lt;test&gt;', $html);
        $this->assertStringNotContainsString('Agent commission test', $html);
        $this->assertStringContainsString('account_head_id='.$this->heads['income']->id, $html);
        $detail = $controller->dailyTransactionsDetails(Request::create('/', 'GET', $params + ['account_head_id' => $this->heads['income']->id]));
        $this->assertEquals(50.25, $detail->getData()['totals']->credit);
        $this->assertEquals(10.10, $detail->getData()['totals']->debit);
        $this->assertSame(2, $detail->getData()['transactions']->total());
        $this->assertStringContainsString('&lt;script&gt;test&lt;/script&gt;', $detail->render());
        $filter = $controller->dailyTransactions(Request::create('/', 'GET'));
        $this->assertStringContainsString('Daily Transactions', $filter->with('errors', new ViewErrorBag)->render());
    }

    public function test_details_do_not_expose_a_head_with_only_other_branch_postings(): void
    {
        $this->entry('income', 2, '100', ['sister_concern_id' => $this->otherBranch]);
        try {
            (new ReportController)->dailyTransactionsDetails(Request::create('/', 'GET', ['start_date' => $this->day, 'end_date' => $this->day, 'account_head_id' => $this->heads['income']->id]));
            $this->fail('Out-of-scope entries must not be exposed');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }
    }
    public function test_empty_period_and_negative_income_remain_visible(): void
    {
        $service = app(DailyTransactionReport::class);
        $this->assertEquals(0, $service->summary($this->day, $this->day, $this->branch)['totals']['net_income']);
        $this->entry('income', 1, '7.25');
        $data = $service->summary($this->day, $this->day, $this->branch);
        $this->assertEquals(-7.25, $data['totals']['income']);
        $this->assertCount(1, $data['sections']['income']);
    }

    public function test_global_user_can_select_all_or_a_single_branch(): void
    {
        $this->user->forceFill(['sister_concern_id' => null])->save();
        session()->forget('active_sister_concern_id');
        $this->entry('income', 2, '1.25');
        $this->entry('income', 2, '2.50', ['sister_concern_id' => $this->otherBranch]);
        $controller = new ReportController;
        $params = ['search' => 1, 'start_date' => $this->day, 'end_date' => $this->day];
        $all = $controller->dailyTransactions(Request::create('/', 'GET', $params))->getData();
        $this->assertNull($all['branchId']);
        $this->assertEquals(3.75, $all['report']['totals']['income']);
        $branch = $controller->dailyTransactions(Request::create('/', 'GET', $params + ['branch_id' => $this->otherBranch]))->getData();
        $this->assertEquals(2.50, $branch['report']['totals']['income']);
    }

    public function test_report_routes_validate_and_paginate_without_losing_filters(): void
    {
        // Exercise controller HTTP wiring; access middleware itself is unchanged.
        $this->withoutMiddleware();
        $params = ['search' => 1, 'start_date' => $this->day, 'end_date' => $this->day, 'branch_id' => $this->branch, 'category' => 'income'];
        for ($i = 0; $i < 101; $i++) {
            $this->entry('income', 2, '0.01');
        }
        $this->get(route('report.daily_transactions', $params))->assertOk()->assertSee('1.01');
        $this->get(route('report.daily_transactions.details', $params + ['account_head_id' => $this->heads['income']->id]))
            ->assertOk()->assertSee('Showing page 1 of 2')->assertSee('start_date='.$this->day, false)->assertSee('account_head_id='.$this->heads['income']->id, false)
            ->assertViewHas('totals', fn ($totals) => (float) $totals->credit === 1.01);
        $this->getJson(route('report.daily_transactions', ['start_date' => 'bad']))->assertUnprocessable()->assertJsonValidationErrors('start_date');
    }

}
