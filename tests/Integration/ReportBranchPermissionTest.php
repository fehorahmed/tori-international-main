<?php
namespace Tests\Integration;

use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Reports\PartyLedgerReport;
use App\Support\ReportBranch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReportBranchPermissionTest extends TestCase
{
    use DatabaseTransactions;
    private User $user;
    private int $branch;
    private int $other;
    private Supplier $member;
    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate(ReportBranch::PERMISSION, 'web');
        $this->user = User::firstOrFail();
        $this->user->syncRoles([]);
        $this->user->syncPermissions([]);
        $this->user->update(['role' => 'Admin']);
        $this->actingAs($this->user);
        $this->branch = SisterConcern::firstOrFail()->id;
        $this->other = SisterConcern::where('id', '!=', $this->branch)->firstOrFail()->id;
        session(['active_sister_concern_id' => $this->branch]);
        $this->member = Supplier::create(['type' => 'Customer', 'status' => 1, 'name' => 'Scope member', 'origin_sister_concern_id' => $this->other]);
        view()->share('errors', new ViewErrorBag);
    }
    private function allow(): void { $this->user->givePermissionTo(ReportBranch::PERMISSION); }
    public function test_without_permission_forged_branch_is_ignored_and_selector_hidden(): void
    {
        $this->withoutMiddleware();
        $url = route('report.accounts_customer_ledger');
        $this->get($url.'?report_branch_id=all')->assertOk()->assertDontSee('name="report_branch_id"', false)->assertDontSee('Scope member');
        $f = ['search' => 1, 'report_branch_id' => $this->other, 'customer_id' => $this->member->id, 'start_date' => '15-04-2088', 'end_date' => '15-04-2088'];
        $this->getJson($url.'?'.http_build_query($f))->assertUnprocessable()->assertJsonValidationErrors('customer_id');
        $this->assertSame($this->branch, ReportBranch::id());
        $this->assertSame($this->branch, $this->user->activeConcernId());
    }
    public function test_permission_shows_all_and_specific_branch_dropdown_and_validates_ids(): void
    {
        $this->allow(); $this->withoutMiddleware();
        $url = route('report.accounts_customer_ledger');
        $this->get($url.'?report_branch_id=all')->assertOk()->assertSee('name="report_branch_id"', false)->assertSee('All Branches')->assertSee('Scope member');
        $this->assertNull(ReportBranch::id());
        $this->get($url.'?report_branch_id='.$this->other)->assertOk()->assertSee('Scope member');
        $this->assertSame($this->other, ReportBranch::id());
        $this->getJson($url.'?report_branch_id=invalid')->assertUnprocessable()->assertJsonValidationErrors('report_branch_id');
        $this->getJson($url.'?report_branch_id[]=1')->assertUnprocessable()->assertJsonValidationErrors('report_branch_id');
        $this->getJson($url.'?report_branch_id=99999999')->assertUnprocessable();
        $this->assertSame($this->branch, $this->user->activeConcernId());
    }
    public function test_consolidated_ledger_includes_each_branch_once_and_links_preserve_scope(): void
    {
        $head = AccountHead::create(['supplier_id' => $this->member->id, 'head_type' => 'primary', 'name' => 'Scope primary']);
        foreach ([$this->branch => '10.25', $this->other => '20.50'] as $branch => $amount) {
            Transaction::create(['account_head_id' => $head->id, 'date' => '2088-04-15', 'sister_concern_id' => $branch, 'transaction_type' => 1,
                'amount' => $amount, 'voucher_id' => 0, 'voucher_no_group_sl' => 1, 'voucher_no' => 'SCOPE', 'voucher_type' => 1, 'user_id' => $this->user->id]);
        }
        $service = app(PartyLedgerReport::class);
        $this->assertSame(3075, $service->generate($this->member->id, null, '2088-04-15', '2088-04-15')['balance']);
        $this->assertSame(2050, $service->generate($this->member->id, $this->other, '2088-04-15', '2088-04-15')['balance']);
        $this->allow(); $this->withoutMiddleware();
        $f = ['search' => 1, 'customer_id' => $this->member->id, 'report_branch_id' => 'all', 'start_date' => '15-04-2088', 'end_date' => '15-04-2088'];
        $this->get(route('report.accounts_customer_ledger', $f))->assertOk()->assertSee('30.75 Dr')->assertSee('report_branch_id=all')->assertSee('All Branches');
        $this->get(route('shomriddhi-advance.reports.members', ['report_branch_id' => $this->other, 'customer_id' => $this->member->id, 'search' => 1]))->assertOk()->assertSee('Scope member');
        $this->get(route('shomriddhi-advance.reports.members', ['report_branch_id' => $this->branch, 'customer_id' => $this->member->id, 'search' => 1]))->assertOk()->assertDontSee('Scope member');
    }
    public function test_general_ledger_and_other_module_filters_use_the_same_scope(): void
    {
        $this->allow(); $this->withoutMiddleware();
        $head = AccountHead::create(['name' => 'Scope ledger head', 'status' => 1]);
        foreach ([$this->branch => '11.25', $this->other => '22.50'] as $branch => $amount) {
            Transaction::create(['account_head_id' => $head->id, 'date' => '2088-04-15', 'sister_concern_id' => $branch, 'transaction_type' => 1,
                'amount' => $amount, 'voucher_id' => 0, 'voucher_no_group_sl' => 1, 'voucher_no' => 'SCOPE', 'voucher_type' => 1, 'user_id' => $this->user->id]);
        }
        foreach ([$this->branch => 11.25, $this->other => 22.50, 'all' => 33.75] as $branch => $expected) {
            $response = $this->get(route('report.ledger', ['search' => 1, 'account_head_id' => $head->id, 'report_branch_id' => $branch,
                'start_date' => '15-04-2088', 'end_date' => '15-04-2088']))->assertOk();
            $this->assertEquals($expected, $response->viewData('accountHeads')->first()->transactions->sum('amount'));
        }
        foreach (['shomriddhi-fdr.reports.maturity', 'shomriddhi-dps.reports.transaction', 'shomriddhi-advance.reports.ledger', 'branch_report.ledger'] as $route) {
            $this->get(route($route, ['report_branch_id' => 'all']))->assertOk()->assertSee('name="report_branch_id"', false)->assertSee('All Branches');
        }
    }

    public function test_report_permission_does_not_grant_access_to_report_routes(): void
    {
        $this->allow();
        $this->get(route('report.accounts_customer_ledger'))->assertForbidden();
    }
}
