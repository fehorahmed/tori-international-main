<?php
namespace Tests\Integration;

use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Reports\PartyLedgerReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class PartyLedgerReportTest extends TestCase
{
    use DatabaseTransactions;
    private User $user;
    private Supplier $customer;
    private Supplier $supplier;
    private AccountHead $head;
    private int $branch;
    private int $other;
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::firstOrFail();
        $this->branch = SisterConcern::firstOrFail()->id;
        $this->other = SisterConcern::where('id', '!=', $this->branch)->firstOrFail()->id;
        $this->actingAs($this->user);
        session(['active_sister_concern_id' => $this->branch]);
        $this->customer = Supplier::create(['status' => 1, 'type' => 'Customer', 'name' => '<Ledger Customer>', 'origin_sister_concern_id' => $this->branch]);
        $this->supplier = Supplier::create(['status' => 1, 'type' => 'Supplier', 'name' => 'Ledger Supplier', 'origin_sister_concern_id' => $this->branch]);
        $this->head = AccountHead::create(['name' => 'Trade ledger test', 'supplier_id' => $this->customer->id, 'head_type' => 'primary']);
    }
    private function entry(int $type, string $amount, array $extra = []): Transaction
    {
        return Transaction::create($extra + ['account_head_id' => $this->head->id, 'date' => '2088-04-15', 'sister_concern_id' => $this->branch,
            'transaction_type' => $type, 'amount' => $amount, 'voucher_id' => 0, 'voucher_no_group_sl' => 1, 'voucher_no' => 'LEDGER-TEST',
            'voucher_type' => 1, 'user_id' => $this->user->id, 'notes' => '<script>ledger</script>']);
    }
    public function test_opening_running_balance_date_boundaries_and_branch_scope(): void
    {
        $this->entry(1, '100.25', ['date' => '2088-04-14', 'is_opening_balance' => 1]);
        $this->entry(2, '10.10', ['date' => '2088-04-14']);
        $this->entry(1, '20.20');
        $this->entry(2, '150.55');
        $this->entry(1, '999', ['date' => '2088-04-16']);
        $this->entry(1, '999', ['deleted_at' => now()]);
        $this->entry(1, '999', ['sister_concern_id' => $this->other]);
        $savings = AccountHead::create(['name' => 'Separate savings', 'supplier_id' => $this->customer->id, 'head_type' => 'savings']);
        $this->entry(1, '999', ['account_head_id' => $savings->id]);
        $this->head->delete(); // Historical primary postings remain visible.
        $r = app(PartyLedgerReport::class)->generate($this->customer->id, $this->branch, '2088-04-15', '2088-04-15');
        $this->assertSame(9015, $r['opening']);
        $this->assertCount(2, $r['rows']);
        $this->assertSame(11035, $r['rows'][0]['balance']);
        $this->assertSame(-4020, $r['balance']);
        $this->assertSame(2020, $r['debit']);
        $this->assertSame(15055, $r['credit']);
    }
    public function test_supplier_credit_balance_and_empty_period(): void
    {
        $head = AccountHead::create(['name' => 'Supplier trade', 'supplier_id' => $this->supplier->id, 'head_type' => 'primary']);
        $this->entry(2, '100.50', ['account_head_id' => $head->id]);
        $this->entry(1, '25.25', ['account_head_id' => $head->id]);
        $service = app(PartyLedgerReport::class);
        $r = $service->generate($this->supplier->id, $this->branch, '2088-04-15', '2088-04-15');
        $this->assertSame(-7525, $r['balance']);
        $next = $service->generate($this->supplier->id, $this->branch, '2088-04-16', '2088-04-16');
        $this->assertSame(-7525, $next['opening']);
        $this->assertCount(0, $next['rows']);
        $this->assertSame(-7525, $next['balance']);
    }
    public function test_branch_ledger_statement_uses_account_head_opening_and_running_balance(): void
    {
        $this->entry(1, '100.25', ['date' => '2088-04-14']);
        $this->entry(1, '20.20');
        $this->entry(2, '150.55');
        $this->entry(1, '999', ['sister_concern_id' => $this->other]);
        $this->withoutMiddleware();
        view()->share('errors', new ViewErrorBag);
        $url = route('branch_report.ledger');
        $this->get($url)->assertOk()->assertSee('Account Head')->assertSee('Default Today');
        $filters = ['search' => 1, 'account_head_id' => $this->head->id, 'report_branch_id' => $this->branch,
            'start_date' => '15-04-2088', 'end_date' => '15-04-2088'];
        $response = $this->get($url.'?'.http_build_query($filters))->assertOk()->assertSee('Opening Balance')->assertSee('Closing Balance')
            ->assertSee('100.25 Dr')->assertSee('30.10 Cr')->assertSee('&lt;script&gt;ledger&lt;/script&gt;', false);
        $statements = $response->viewData('statements');
        $this->assertCount(1, $statements);
        $this->assertSame(10025, $statements[0]['report']['opening']);
        $this->assertSame(-3010, $statements[0]['report']['balance']);
        $this->assertCount(2, $statements[0]['report']['rows']);
        $this->getJson($url.'?'.http_build_query(array_replace($filters, ['end_date' => '14-04-2088'])))->assertUnprocessable();
        $this->getJson($url.'?'.http_build_query(array_replace($filters, ['account_head_id' => 99999999])))->assertUnprocessable();
        $this->getJson($url.'?search=1')->assertUnprocessable()->assertJsonValidationErrors(['start_date', 'end_date']);
        $this->assertFileExists(base_path('backups/branch-ledger/2026-11-28/FinancialReportController.php'));
        $this->assertFileExists(base_path('backups/branch-ledger/2026-11-28/ledger.blade.php'));
        $this->assertFileExists(base_path('backups/branch-ledger/2026-11-28/ledger_report.blade.php'));
    }

    public function test_all_branch_ledger_separates_balances_and_carries_them_across_pages(): void
    {
        $this->user->givePermissionTo(\App\Support\ReportBranch::PERMISSION);
        $this->head->update(['nature' => 'Debit']);
        for ($i = 0; $i < 20; $i++) $this->entry(1, '1.25');
        $this->entry(1, '2.50', ['sister_concern_id' => $this->other]);
        $this->withoutMiddleware();
        view()->share('errors', new ViewErrorBag);
        $response = $this->get(route('branch_report.ledger', ['search' => 1, 'account_head_id' => $this->head->id, 'report_branch_id' => 'all',
            'start_date' => '15-04-2088', 'end_date' => '15-04-2088']))->assertOk()->assertSee('Branch General Ledger')->assertSee('Nature: Debit')
            ->assertSee('Balance Brought Forward')->assertSee('Balance Carried Forward')->assertSee('Page 3 of 3')->assertSee('25.00 Dr')->assertSee('2.50 Dr');
        $statements = collect($response->viewData('statements'))->keyBy(fn ($s) => $s['branch']->id);
        $this->assertCount(2, $statements);
        $this->assertSame(2500, $statements[$this->branch]['report']['balance']);
        $this->assertSame(250, $statements[$this->other]['report']['balance']);
        $pages = collect($response->viewData('pages'))->where('branch.id', $this->branch)->values();
        $this->assertCount(2, $pages);
        $this->assertSame(2250, $pages[0]['closing']);
        $this->assertSame($pages[0]['closing'], $pages[1]['opening']);
        $this->assertSame(2500, $pages[1]['closing']);
        $this->assertFalse($pages[0]['last']);
        $this->assertTrue($pages[1]['last']);
        $this->assertCount(20, array_merge($pages[0]['rows'], $pages[1]['rows']));
    }

    public function test_filters_validation_statement_rendering_and_party_scope(): void
    {
        $this->withoutMiddleware();
        view()->share('errors', new ViewErrorBag);
        $this->entry(1, '12.25');
        foreach (['customer' => $this->customer, 'supplier' => $this->supplier] as $kind => $party) {
            $url = route('report.accounts_'.$kind.'_ledger');
            $this->get($url)->assertOk()->assertSee('Default Today')->assertSee('Generate Report');
            $this->getJson($url.'?search=1')->assertUnprocessable()->assertJsonValidationErrors([$kind.'_id', 'start_date', 'end_date']);
            $f = ['search' => 1, $kind.'_id' => $party->id, 'start_date' => '15-04-2088', 'end_date' => '15-04-2088'];
            $this->get($url.'?'.http_build_query($f))->assertOk()->assertSee('Statement of Account')->assertSee('Opening Balance')->assertSee('Closing Balance');
            $this->getJson($url.'?'.http_build_query(array_replace($f, ['end_date' => '14-04-2088'])))->assertUnprocessable();
            $wrong = $kind === 'customer' ? $this->supplier : $this->customer;
            $this->getJson($url.'?'.http_build_query(array_replace($f, [$kind.'_id' => $wrong->id])))->assertUnprocessable()->assertJsonValidationErrors([$kind.'_id']);
        }
        $url = route('report.accounts_customer_ledger');
        $f = ['search' => 1, 'customer_id' => $this->customer->id, 'start_date' => '15-04-2088', 'end_date' => '15-04-2088'];
        $this->get($url.'?'.http_build_query($f))->assertOk()->assertSee('&lt;script&gt;ledger&lt;/script&gt;', false)->assertSee('12.25 Dr');
        $foreign = Supplier::create(['status' => 1, 'name' => 'Foreign branch', 'type' => 'Customer', 'origin_sister_concern_id' => $this->other]);
        $this->getJson($url.'?'.http_build_query(array_replace($f, ['customer_id' => $foreign->id, 'branch_id' => $this->other])))->assertUnprocessable();
    }
}
