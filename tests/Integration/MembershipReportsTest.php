<?php

namespace Tests\Integration;

use App\Http\Controllers\ShomriddhiAdvance\MembershipReportController;
use App\Models\BranchManagement\SisterConcern;
use App\Models\CustomerKyc;
use App\Models\MembershipAgent;
use App\Models\MembershipAgentTransaction;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Reports\MembershipReports;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class MembershipReportsTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private int $branch;
    private int $other;
    private Supplier $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::firstOrFail();
        $this->admin->update(['role' => 'Admin']);
        $this->branch = SisterConcern::firstOrFail()->id;
        $this->other = SisterConcern::where('id', '!=', $this->branch)->firstOrFail()->id;
        $this->actingAs($this->admin);
        session(['active_sister_concern_id' => $this->branch]);
        $this->member = Supplier::create(['type' => 'Customer', 'status' => 1, 'name' => 'Report <Member>', 'member_no' => 'RPT-'.Str::random(12),
            'origin_sister_concern_id' => $this->branch, 'registration_source' => 'app', 'created_at' => '2026-02-15 23:59:59']);
    }

    private function report(): MembershipReports { return app(MembershipReports::class); }

    public function test_member_report_filters_joining_date_and_current_balances_without_duplicate_members(): void
    {
        ShomriddhiAccount::create(['customer_id' => $this->member->id, 'origin_sister_concern_id' => $this->branch, 'principal_balance' => '100.25', 'accrued_interest' => '2.50']);
        ShomriddhiAccount::create(['customer_id' => $this->member->id, 'origin_sister_concern_id' => $this->branch, 'principal_balance' => '200.50', 'accrued_interest' => '1.25']);
        ShomriddhiAccount::create(['customer_id' => $this->member->id, 'origin_sister_concern_id' => $this->other, 'principal_balance' => '999.00']);
        ShomriddhiAccount::create(['customer_id' => $this->member->id, 'origin_sister_concern_id' => $this->branch, 'status' => 'Closed', 'principal_balance' => '500.00']);
        CustomerKyc::create(['customer_id' => $this->member->id, 'submitted_name' => $this->member->name, 'nid_number' => '1234567890',
            'nid_front_path' => 'test', 'nid_back_path' => 'test', 'selfie_path' => 'test', 'status' => 'Verified']);
        $filters = ['customer_id' => $this->member->id, 'start_date' => '2026-02-15', 'end_date' => '2026-02-15', 'status' => '1', 'source' => 'app'];
        $data = $this->report()->members($this->branch, $filters);
        $this->assertCount(1, $data['rows']);
        $this->assertSame('300.75', $data['totals']['Current Savings (Tk)']);
        $this->assertSame('3.75', $data['totals']['Current Accrued Interest (Tk)']);
        $this->assertSame('Verified', $data['rows'][0][7]);
        $this->assertCount(0, $this->report()->members($this->other, $filters)['rows']);
        $this->assertCount(0, $this->report()->members($this->branch, array_replace($filters, ['source' => 'ecommerce']))['rows']);
        $this->assertCount(0, $this->report()->members($this->branch, array_replace($filters, ['status' => '0']))['rows']);
        $this->assertCount(0, $this->report()->members($this->branch, array_replace($filters, ['start_date' => '2026-02-16', 'end_date' => '2026-02-16']))['rows']);
    }

    public function test_agent_report_separates_principal_charges_commission_and_keeps_removed_agent_history(): void
    {
        $agent = MembershipAgent::create(['customer_id' => $this->member->id, 'agent_code' => 'RPT-'.$this->member->id,
            'status' => 'Removed', 'approved_by' => $this->admin->id, 'approved_at' => now()]);
        foreach ([['agent_cashin', '100.25', '0.00', '0.00', '2026-02-15 00:00:00'], ['agent_cashout', '200.50', '20.25', '12.50', '2026-02-15 23:59:59'], ['agent_cashout', '999.00', '99.00', '9.00', '2026-02-16 00:00:00']] as $i => [$type, $amount, $charge, $commission, $date]) {
            MembershipAgentTransaction::create(['reference' => 'RPT-'.Str::random(12), 'type' => $type, 'agent_id' => $agent->id, 'customer_id' => $this->member->id,
                'initiator_id' => $this->member->id, 'transfer_id' => 800000 + $agent->id * 10 + $i, 'idempotency_key' => (string) Str::uuid(),
                'quote_hash' => str_repeat('a', 64), 'amount' => $amount, 'customer_charge' => $charge, 'agent_commission' => $commission, 'settings_snapshot' => [], 'completed_at' => $date]);
        }
        $f = ['agent_id' => $agent->id, 'status' => 'Removed', 'start_date' => '2026-02-15', 'end_date' => '2026-02-15'];
        $data = $this->report()->agents($this->branch, $f);
        $this->assertSame(2, $data['totals']['Transactions']);
        $this->assertSame('100.25', $data['totals']['Cash-in (Tk)']);
        $this->assertSame('200.50', $data['totals']['Cash-out (Tk)']);
        $this->assertSame('20.25', $data['totals']['Customer Charges (Tk)']);
        $this->assertSame('12.50', $data['totals']['Commission (Tk)']);
        $this->assertCount(0, $this->report()->agents($this->other, $f)['rows']);
        $emptyPeriod = $this->report()->agents($this->branch, array_replace($f, ['start_date' => '2026-02-17', 'end_date' => '2026-02-17']));
        $this->assertCount(1, $emptyPeriod['rows']);
        $this->assertSame(0, $emptyPeriod['totals']['Transactions']);
    }

    private function reward(string $type, int $points, string $amount, ?string $paidAt, int $branch, string $date): void
    {
        $customer = Supplier::create(['name' => 'Report referred customer', 'type' => 'Customer', 'status' => 1, 'origin_sister_concern_id' => $branch]);
        $referrer = $type === 'member' ? $this->member->id : $this->admin->id;
        $referral = DB::table('membership_referrals')->insertGetId(['customer_id' => $customer->id, 'referrer_type' => $type, 'referrer_id' => $referrer, 'referral_code' => 'REPORT', 'created_at' => '2026-01-01 00:00:00', 'updated_at' => now()]);
        $fee = DB::table('membership_registration_fees')->where('customer_id', $customer->id)->value('id');
        DB::table('membership_referral_rewards')->insert(['referral_id' => $referral, 'registration_fee_id' => $fee, 'referrer_type' => $type,
            'referrer_id' => $referrer, 'branch_id' => $branch, 'points' => $points, 'amount' => $amount, 'paid_at' => $paidAt, 'created_at' => $date, 'updated_at' => now()]);
    }

    public function test_referral_report_uses_bonus_date_and_separates_points_cash_paid_pending_and_milestones(): void
    {
        $this->reward('member', 3, '0', null, $this->branch, '2026-02-15 23:59:59');
        $this->reward('agent', 0, '30.25', '2026-02-20 12:00:00', $this->branch, '2026-02-15 10:00:00');
        $this->reward('agent', 0, '10.50', null, $this->branch, '2026-02-15 11:00:00');
        $this->reward('member', 999, '0', null, $this->other, '2026-02-15 11:00:00');
        $this->reward('member', 999, '0', null, $this->branch, '2026-02-16 00:00:00');
        $account = ShomriddhiAccount::create(['customer_id' => $this->member->id, 'origin_sister_concern_id' => $this->branch]);
        DB::table('membership_milestone_bonuses')->insert(['customer_id' => $this->member->id, 'tier_id' => (string) Str::uuid(),
            'threshold' => 3, 'qualifying_points' => 3, 'percentage' => 10, 'point_value' => 100, 'amount' => '25.25', 'account_id' => $account->id,
            'paid_at' => '2026-02-15 12:00:00', 'created_at' => '2026-02-15 12:00:00', 'updated_at' => now()]);
        $f = ['start_date' => '2026-02-15', 'end_date' => '2026-02-15'];
        $data = $this->report()->referrals($this->branch, $f);
        $this->assertCount(4, $data['rows']);
        $this->assertSame(3, $data['totals']['Points Earned']);
        $this->assertSame('66.00', $data['totals']['Cash Bonus (Tk)']);
        $this->assertSame('55.50', $data['totals']['Paid Cash (Tk)']);
        $this->assertSame('10.50', $data['totals']['Pending Cash (Tk)']);
        $pending = $this->report()->referrals($this->branch, $f + ['payment_status' => 'pending']);
        $this->assertCount(1, $pending['rows']);
        $this->assertSame(0, $pending['totals']['Points Earned']);
        $employee = $this->report()->referrals($this->branch, $f + ['referrer_type' => 'agent']);
        $this->assertCount(2, $employee['rows']);
        $member = $this->report()->referrals($this->branch, $f + ['referrer_key' => 'member:'.$this->member->id]);
        $this->assertCount(2, $member['rows']);
        $this->assertSame('25.25', $member['totals']['Paid Cash (Tk)']);
        $this->assertCount(1, $this->report()->referrals($this->branch, $f + ['bonus_kind' => 'milestone'])['rows']);
        $this->assertCount(3, $this->report()->referrals($this->branch, $f + ['bonus_kind' => 'referral'])['rows']);
    }

    public function test_report_filters_print_pages_and_permissions(): void
    {
        $this->withoutMiddleware();
        view()->share('errors', new ViewErrorBag);
        foreach (['members', 'agents', 'referrals'] as $kind) {
            $url = route('shomriddhi-advance.reports.'.$kind);
            $this->withSession(['active_sister_concern_id' => $this->branch])->get($url)->assertOk()->assertSee('Generate Report');
            $this->withSession(['active_sister_concern_id' => $this->branch])->get($url.'?'.http_build_query(['search' => 1, 'start_date' => '2026-02-15', 'end_date' => '2026-02-15', 'customer_id' => $this->member->id]))
                ->assertOk()->assertSee('printArea')->assertSee('Print');
            $this->withSession(['active_sister_concern_id' => $this->branch])->getJson($url.'?start_date=2026-02-16&end_date=2026-02-15')->assertUnprocessable();
        }
        $this->withSession(['active_sister_concern_id' => $this->branch])->get(route('shomriddhi-advance.reports.members', ['search' => 1, 'customer_id' => $this->member->id]))
            ->assertOk()->assertSee('Report &lt;Member&gt;', false);
        $this->admin->update(['role' => 'Staff']);
        foreach (['agents', 'referrals'] as $kind) $this->get(route('shomriddhi-advance.reports.'.$kind))->assertForbidden();
    }
}

