<?php
namespace Tests\Integration;

use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\BranchManagement\SisterConcern;
use App\Models\User;
use App\Services\Reports\BranchTransactionLedgerReport;
use App\Support\ReportBranch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class BranchTransactionLedgerReportTest extends TestCase
{
    use DatabaseTransactions;
    private User $user;
    private int $branch;
    private int $other;
    private AccountHead $cash;
    private AccountHead $income;
    private Voucher $voucher;
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::firstOrFail();
        $this->user->syncRoles([]); $this->user->syncPermissions([]);
        $this->actingAs($this->user);
        $this->branch = SisterConcern::firstOrFail()->id;
        $this->other = SisterConcern::where('id', '!=', $this->branch)->firstOrFail()->id;
        session(['active_sister_concern_id' => $this->branch]);
        $this->cash = AccountHead::create(['name' => 'Branch cash <test>', 'nature' => 'Debit']);
        $this->income = AccountHead::create(['name' => 'App charge income', 'nature' => 'Credit']);
        $this->voucher = Voucher::create(['sister_concern_id' => $this->branch, 'voucher_type' => 3, 'amount' => 100,
            'date' => '2088-04-15', 'voucher_no' => 'BRL-'.Str::random(12), 'voucher_no_group_sl' => 1, 'user_id' => $this->user->id]);
        view()->share('errors', new ViewErrorBag);
    }
    private function entry(AccountHead $head, int $type, string $amount, array $extra = []): Transaction
    {
        return Transaction::create($extra + ['account_head_id' => $head->id, 'sister_concern_id' => $this->branch,
            'transaction_type' => $type, 'amount' => $amount, 'date' => '2088-04-15', 'voucher_id' => $this->voucher->id,
            'voucher_no' => $this->voucher->voucher_no, 'voucher_no_group_sl' => 1, 'voucher_type' => 3, 'user_id' => $this->user->id, 'notes' => '<script>Branch ledger</script>']);
    }
    public function test_all_account_postings_charges_openings_and_retired_heads_are_included_once(): void
    {
        $this->entry($this->cash, 1, '100', ['date' => '2088-04-14']);
        $this->entry($this->income, 2, '100', ['date' => '2088-04-14']);
        $this->entry($this->cash, 1, '100');
        $this->entry($this->income, 2, '90');
        $this->entry($this->income, 2, '10');
        $this->entry($this->cash, 1, '5', ['is_opening_balance' => 1]);
        $this->entry($this->income, 2, '5', ['is_opening_balance' => 1]);
        $this->entry($this->cash, 1, '999', ['deleted_at' => now()]);
        $this->entry($this->cash, 1, '999', ['date' => '2088-04-16']);
        $this->entry($this->cash, 1, '50', ['sister_concern_id' => $this->other]);
        $this->entry($this->income, 2, '50', ['sister_concern_id' => $this->other]);
        $this->income->delete();
        $r = app(BranchTransactionLedgerReport::class)->generate($this->branch, '2088-04-15', '2088-04-15');
        $this->assertSame(5, $r['count']);
        $this->assertSame(10500, $r['debit']); $this->assertSame(10500, $r['credit']);
        $report = $r['statements'][0]['report'];
        $this->assertSame(0, $report['opening']); $this->assertSame(0, $report['balance']);
        $this->assertSame([10000, 1000, 0, 500, 0], array_column($report['rows'], 'balance'));
        $this->assertSame('App charge income', $report['rows'][1]['account']);
        $all = app(BranchTransactionLedgerReport::class)->generate(null, '2088-04-15', '2088-04-15');
        $this->assertSame(7, $all['count']);
        $this->assertSame(15500, $all['debit']); $this->assertSame(15500, $all['credit']);
        $this->assertCount(2, $all['statements']);
        $summary = app(BranchTransactionLedgerReport::class)->summary(null, '2088-04-15', '2088-04-15');
        $this->assertSame($all['debit'], $summary['debit']);
        $this->assertSame($all['credit'], $summary['credit']);
        $this->assertSame($all['count'], $summary['count']);
        foreach ($all['statements'] as $statement) {
            $row = collect($summary['rows'])->first(fn ($r) => $r['branch']->id === $statement['branch']->id);
            foreach (['opening', 'debit', 'credit', 'balance'] as $field) $this->assertSame($statement['report'][$field], $row[$field]);
        }

    }
    public function test_report_dates_permission_scope_links_and_validation(): void
    {
        $this->entry($this->cash, 1, '10.25');
        $this->entry($this->income, 2, '10.25');
        $this->entry($this->cash, 1, '88', ['sister_concern_id' => $this->other]);
        $this->withoutMiddleware();
        $url = route('branch_report.branch_ledger');
        $this->get($url)->assertOk()->assertSee('Default Today')->assertSee(now()->format('d-m-Y'))->assertDontSee('name="report_branch_id"', false);
        $f = ['search' => 1, 'start_date' => '15-04-2088', 'end_date' => '15-04-2088', 'report_branch_id' => 'all'];
        $response = $this->get($url.'?'.http_build_query($f))->assertOk()->assertSee('Branch Ledger Summary')->assertSee('Total Debit')->assertSee('Total Credit');
        $this->assertCount(1, $response->viewData('result')['rows']);
        $detailUrl = route('branch_report.branch_ledger.details', ['branchId' => $this->branch] + $response->viewData('filters'));
        $response->assertSee('href="'.e($detailUrl).'"', false);
        $detail = $this->get($detailUrl)->assertOk()->assertSee('App charge income')
            ->assertSee('&lt;script&gt;Branch ledger&lt;/script&gt;', false)->assertSee('href="'.route('voucher.details', ['voucher' => $this->voucher->id]).'"', false);
        $this->assertSame($response->viewData('result')['debit'], $detail->viewData('result')['debit']);
        $this->assertSame($response->viewData('result')['credit'], $detail->viewData('result')['credit']);
        $this->get(route('branch_report.branch_ledger.details', ['branchId' => $this->other] + $f))->assertForbidden();
        $this->assertSame(2, $response->viewData('result')['count']);
        $this->user->givePermissionTo(ReportBranch::PERMISSION);
        $this->get($url)->assertOk()->assertSee('name="report_branch_id"', false)->assertSee('All Branches');
        $this->assertSame(3, $this->get($url.'?'.http_build_query($f))->assertOk()->viewData('result')['count']);
        $other = $this->get($url.'?'.http_build_query(array_replace($f, ['report_branch_id' => $this->other])))->assertOk();
        $this->assertSame(1, $other->viewData('result')['count']);
        $otherDetail = $this->get(route('branch_report.branch_ledger.details', ['branchId' => $this->other] + $f))->assertOk();
        $this->assertSame($other->viewData('result')['debit'], $otherDetail->viewData('result')['debit']);
        $this->assertSame(1, $otherDetail->viewData('result')['count']);

        $this->assertSame($this->branch, $this->user->activeConcernId());
        $this->getJson($url.'?'.http_build_query(array_replace($f, ['end_date' => '14-04-2088'])))->assertUnprocessable();
        $this->getJson($url.'?search=1')->assertUnprocessable()->assertJsonValidationErrors(['start_date', 'end_date']);
        $this->getJson($url.'?report_branch_id=invalid')->assertUnprocessable();
    }
    public function test_opening_difference_is_carried_and_empty_branch_renders(): void
    {
        $this->entry($this->cash, 1, '12.50', ['date' => '2088-04-14']);
        $this->entry($this->income, 2, '2.25');
        $r = app(BranchTransactionLedgerReport::class)->generate($this->branch, '2088-04-15', '2088-04-15')['statements'][0]['report'];
        $this->assertSame(1250, $r['opening']); $this->assertSame(1025, $r['balance']);
        $empty = app(BranchTransactionLedgerReport::class)->generate($this->other, '2088-04-15', '2088-04-15');
        $this->assertSame(0, $empty['count']); $this->assertCount(1, $empty['statements']);
        $this->assertSame(0, $empty['statements'][0]['report']['balance']);
    }
    public function test_all_branch_permission_does_not_replace_existing_report_permissions(): void
    {
        $this->user->givePermissionTo(ReportBranch::PERMISSION);
        $this->get(route('branch_report.branch_ledger'))->assertForbidden();
    }
}
