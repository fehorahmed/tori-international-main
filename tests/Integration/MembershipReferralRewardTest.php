<?php

namespace Tests\Integration;

use App\Http\Controllers\CardManagement\ReferralController;
use App\Http\Controllers\CardManagement\TransactionSettingController;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\BranchManagement\SisterConcern;
use App\Models\MembershipTransactionSetting as Setting;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Membership\ReferralRewardService as Rewards;
use App\Services\Membership\RegistrationFeeService;
use App\Services\Membership\RecentTransactionService;
use App\Services\ShomriddhiAdvance\ShomriddhiAdvanceService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MembershipReferralRewardTest extends TestCase
{
    use DatabaseTransactions;
    private User $admin;
    private SisterConcern $branch;
    private Supplier $member;
    private AccountHead $expense;
    private AccountHead $payable;
    private AccountHead $income;
    private AccountHead $cash;
    private ShomriddhiAccount $account;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        Setting::writeGroup('referral_milestones', ['enabled' => true, 'tiers' => Setting::defaults()['referral_milestones.tiers']]);
        $this->branch = SisterConcern::where('status', 1)->firstOrFail();
        $this->admin = User::firstOrFail();
        $this->admin->forceFill(['role' => 'Admin', 'status' => 1])->save();
        $this->actingAs($this->admin, 'web')->withSession(['active_sister_concern_id' => $this->branch->id]);
        Setting::writeGroup('membership', ['registration_fee' => '0.00', 'account_hold_amount' => '100.00']);
        $this->expense = $this->fixtureHead('Expense', 'Debit', 25);
        $this->payable = $this->fixtureHead('Agent payable', 'Credit', 16);
        $this->income = $this->fixtureHead('Registration income', 'Credit', 23);
        $this->cash = $this->fixtureHead('Cash', 'Debit', 7, ['payment_mode' => 2, 'sister_concern_id' => $this->branch->id]);
        $this->member = Supplier::create(['type' => 'Customer', 'name' => 'Referring test member', 'status' => 1, 'pin' => Hash::make('763921')]);
        $this->account = ShomriddhiAccount::create(['customer_id' => $this->member->id, 'origin_sister_concern_id' => $this->branch->id, 'status' => 'Active', 'principal_balance' => '0.00']);
        $this->fixtureHead('Member savings', 'Credit', 28, ['supplier_id' => $this->member->id, 'head_type' => 'savings']);
        Setting::writeGroup('referral', ['enabled' => true, 'member_points' => 2, 'point_value' => '30.00', 'agent_bonus' => '30.00',
            'expense_head_id' => $this->expense->id, 'payable_head_id' => $this->payable->id, 'posting_user_id' => $this->admin->id]);
    }

    private function fixtureHead(string $name, string $nature, int $group, array $extra = []): AccountHead
    {
        return AccountHead::create($extra + ['name' => 'Referral test '.$name, 'nature' => $nature, 'status' => 1,
            'account_group_id' => AccountGroup::where('reserved_id', $group)->firstOrFail()->id]);
    }

    private function referred(string $type = 'member', string $balance = '150.00'): ShomriddhiAccount
    {
        Setting::writeGroup('membership', ['registration_fee' => '50.00', 'income_head_id' => $this->income->id]);
        $c = Supplier::create(['type' => 'Customer', 'name' => 'Referred test customer', 'status' => 1]);
        $a = ShomriddhiAccount::create(['customer_id' => $c->id, 'origin_sister_concern_id' => $this->branch->id, 'principal_balance' => $balance, 'status' => 'Active']);
        $this->fixtureHead('Referred savings', 'Credit', 28, ['supplier_id' => $c->id, 'head_type' => 'savings']);
        DB::table('membership_referrals')->insert(['customer_id' => $c->id, 'referrer_type' => $type, 'referrer_id' => $type === 'member' ? $this->member->id : $this->admin->id,
            'referral_code' => 'ORIGINAL-CODE', 'created_at' => now(), 'updated_at' => now()]);
        return $a;
    }

    private function collect(ShomriddhiAccount $a): object
    {
        RegistrationFeeService::collect($a, $this->admin->id);
        return DB::table('membership_referral_rewards')->where('referrer_id', $this->member->id)->orWhere('referrer_id', $this->admin->id)->latest('id')->first();
    }

    private function assertBalanced(int $id): void
    {
        $this->assertSame(2, Transaction::where('voucher_id', $id)->count());
        $this->assertEquals(Transaction::where('voucher_id', $id)->where('transaction_type', 1)->sum('amount'), Transaction::where('voucher_id', $id)->where('transaction_type', 2)->sum('amount'));
        $this->assertTrue(Voucher::findOrFail($id)->is_locked());
    }

    public function test_member_only_earns_once_after_full_fee_and_hold_remains(): void
    {
        $a = $this->referred('member', '149.99');
        RegistrationFeeService::collect($a, $this->admin->id);
        $this->assertSame(0, Rewards::points($this->member->id));
        $a->refresh()->update(['principal_balance' => '150.00']);
        $reward = $this->collect($a);
        $this->assertSame(2, (int) $reward->points);
        $this->assertSame('100.00', $a->fresh()->principal_balance);
        $this->assertSame('0.00', $this->account->fresh()->principal_balance);
        $this->collect($a);
        $this->assertSame(2, Rewards::points($this->member->id));
        $this->assertSame(1, DB::table('membership_referral_rewards')->where('referral_id', $reward->referral_id)->count());
    }

    public function test_manual_redemption_uses_current_rate_posts_savings_and_is_retry_safe(): void
    {
        $this->collect($this->referred());
        Setting::writeGroup('referral', ['point_value' => '35.50']);
        $key = (string) Str::uuid();
        $row = Rewards::redeem($this->member, 1, '35.50', '763921', $key);
        $this->assertSame('35.50', $row->amount);
        $this->assertSame('35.50', $this->account->fresh()->principal_balance);
        $this->assertSame(1, Rewards::points($this->member->id));
        $this->assertBalanced($row->voucher_id);
        $retry = Rewards::redeem($this->member, 1, '35.50', '000000', $key);
        $this->assertSame($row->id, $retry->id);
        $this->assertSame('35.50', $this->account->fresh()->principal_balance);
        $recent = app(RecentTransactionService::class)->forCustomer($this->member);
        $this->assertSame('Referral points redeemed', $recent[0]['title_en']);
        $this->assertTrue($recent[0]['is_credit']);
        $log = ShomriddhiTransaction::where('voucher_id', $row->voucher_id)->firstOrFail();
        $this->assertFalse(app(ShomriddhiAdvanceService::class)->deleteTransaction($log)['success']);
    }

    public function test_overspending_points_wrong_pin_and_changed_rate_do_not_post(): void
    {
        $this->collect($this->referred());
        foreach ([[3, '30.00', '763921'], [1, '30.00', '000000'], [1, '20.00', '763921']] as [$points, $rate, $pin]) {
            try { Rewards::redeem($this->member, $points, $rate, $pin, (string) Str::uuid()); $this->fail('Must reject'); }
            catch (ValidationException) { $this->assertSame(2, Rewards::points($this->member->id)); }
        }
        $this->assertSame('0.00', $this->account->fresh()->principal_balance);
    }

    public function test_changed_payload_on_same_request_key_is_rejected(): void
    {
        $this->collect($this->referred());
        $key = (string) Str::uuid();
        Rewards::redeem($this->member, 1, '30.00', '763921', $key);
        $this->expectException(ValidationException::class);
        Rewards::redeem($this->member, 2, '30.00', '763921', $key);
    }

    public function test_agent_accrual_payment_and_retries_are_balanced(): void
    {
        $reward = $this->collect($this->referred('agent'));
        $this->assertSame('30.00', $reward->amount);
        $this->assertNull($reward->paid_at);
        $this->assertBalanced($reward->voucher_id);
        $this->assertSame(30.0, (float) $this->payable->getCurrentBalance($this->branch->id));
        $fund = Voucher::findOrFail($reward->voucher_id)->replicate();
        $fund->voucher_no = 'FUND-'.Str::random(12); $fund->save();
        Transaction::create(['sister_concern_id' => $this->branch->id, 'voucher_id' => $fund->id, 'voucher_type' => 1,
            'voucher_no_group_sl' => 1, 'voucher_no' => $fund->voucher_no, 'account_head_id' => $this->cash->id, 'transaction_type' => 1, 'amount' => '100.00', 'date' => now()->toDateString(), 'user_id' => $this->admin->id]);
        // Configuration changes cannot move an already accrued payable to a different ledger.
        Setting::writeGroup('referral', ['payable_head_id' => null]);
        Rewards::payAgent($reward->id, $this->cash->id, $this->admin);
        $paid = DB::table('membership_referral_rewards')->find($reward->id);
        $this->assertNotNull($paid->paid_at);
        $this->assertBalanced($paid->payment_voucher_id);
        $this->assertSame(0.0, (float) $this->payable->getCurrentBalance($this->branch->id));
        $this->assertSame(70.0, (float) $this->cash->getCurrentBalance($this->branch->id));
        Rewards::payAgent($reward->id, $this->cash->id, $this->admin);
        $this->assertSame($paid->payment_voucher_id, DB::table('membership_referral_rewards')->find($reward->id)->payment_voucher_id);
    }

    public function test_invalid_accrual_ledger_rolls_back_fee_and_reward(): void
    {
        $a = $this->referred('agent');
        Setting::writeGroup('referral', ['expense_head_id' => $this->income->id]);
        try { $this->collect($a); $this->fail('Must reject'); }
        catch (ValidationException) {
            $this->assertSame('150.00', $a->fresh()->principal_balance);
            $this->assertNull(DB::table('membership_registration_fees')->where('customer_id', $a->customer_id)->value('paid_at'));
            $this->assertSame(0, DB::table('membership_referral_rewards')->where('referrer_id', $this->admin->id)->count());
        }
    }

    public function test_payment_requires_funds_and_originating_branch(): void
    {
        $reward = $this->collect($this->referred('agent'));
        try { Rewards::payAgent($reward->id, $this->cash->id, $this->admin); $this->fail('Must reject unfunded payment'); }
        catch (ValidationException) { $this->assertNull(DB::table('membership_referral_rewards')->find($reward->id)->paid_at); }
        $other = SisterConcern::where('status', 1)->whereKeyNot($this->branch->id)->firstOrFail();
        session(['active_sister_concern_id' => $other->id]);
        try { Rewards::payAgent($reward->id, $this->cash->id, $this->admin); $this->fail('Must reject wrong branch'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(403, $e->getStatusCode()); }
    }

    public function test_api_is_member_scoped_and_rejects_staff(): void
    {
        $this->collect($this->referred());
        Sanctum::actingAs($this->member);
        $this->getJson('/api/membership/v1/referrals')->assertOk()->assertJsonPath('data.points', 2);
        $key = (string) Str::uuid();
        $this->postJson('/api/membership/v1/referrals/redeem', ['points' => 1, 'point_value' => '30.00', 'pin' => '763921', 'request_key' => $key])->assertCreated()->assertJsonPath('data.amount', '30.00');
        $other = Supplier::create(['type' => 'Customer', 'name' => 'Unrelated', 'status' => 1]);
        Sanctum::actingAs($other);
        $this->getJson('/api/membership/v1/referrals')->assertOk()->assertJsonPath('data.points', 0)->assertJsonCount(0, 'data.redemptions');
        Sanctum::actingAs($this->admin);
        $this->getJson('/api/membership/v1/referrals')->assertForbidden();
    }

    public function test_zero_and_disabled_rewards_never_backfill(): void
    {
        Setting::writeGroup('referral', ['enabled' => false]);
        $a = $this->referred();
        $this->collect($a);
        Setting::writeGroup('referral', ['enabled' => true]);
        $this->collect($a);
        $this->assertSame(0, Rewards::points($this->member->id));
        Setting::writeGroup('referral', ['member_points' => 0]);
        $this->collect($this->referred());
        $this->assertSame(0, Rewards::points($this->member->id));
    }

    public function test_admin_settings_and_report_render_and_permissions_apply(): void
    {
        $this->collect($this->referred());
        $settings = app(TransactionSettingController::class);
        $settings->update(Request::create('/', 'POST', ['enabled' => 1, 'member_points' => 3, 'point_value' => '30.5', 'agent_bonus' => '40', 'expense_head_id' => $this->expense->id, 'payable_head_id' => $this->payable->id, 'posting_user_id' => 999999]), 'referral');
        $this->assertSame(3, membership_transaction_setting('referral.member_points'));
        $this->assertSame('30.50', membership_transaction_setting('referral.point_value'));
        $this->assertSame($this->admin->id, membership_transaction_setting('referral.posting_user_id'));
        $this->assertStringContainsString('Member points per successful referral', $settings->index()->with('errors', new ViewErrorBag)->render());
        $view = app(ReferralController::class)->index(Request::create('/', 'GET', ['type' => 'member', 'referrer_id' => $this->member->id]));
        $this->assertSame(1, $view->getData()['summary']->first()->fee_paid + 0);
        $this->assertStringContainsString('Referred test customer', $view->with('errors', new ViewErrorBag)->render());
        $this->admin->update(['role' => 'Agent']);
        try { app(ReferralController::class)->index(Request::create('/')); $this->fail('Must reject non admin'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(403, $e->getStatusCode()); }
    }
    public function test_milestone_automatically_credits_cash_without_adding_or_spending_points(): void
    {
        Setting::writeGroup('referral', ['member_points' => 100]);
        $this->collect($this->referred());
        $this->assertSame(100, Rewards::points($this->member->id));
        $this->assertSame('750.00', $this->account->fresh()->principal_balance);
        $bonus = DB::table('membership_milestone_bonuses')->where('customer_id', $this->member->id)->first();
        $this->assertSame('750.00', $bonus->amount);
        $this->assertSame(100, (int) $bonus->qualifying_points);
        $this->assertBalanced($bonus->voucher_id);
        $this->assertSame(37, (int) Voucher::find($bonus->voucher_id)->payment_purpose);
        $this->assertSame('Referral milestone bonus', app(RecentTransactionService::class)->forCustomer($this->member)[0]['title_en']);
        $log = ShomriddhiTransaction::where('voucher_id', $bonus->voucher_id)->first();
        $this->assertFalse(app(ShomriddhiAdvanceService::class)->deleteTransaction($log)['success']);
    }

    public function test_redeeming_lowers_progress_and_crossing_again_never_repeats_bonus(): void
    {
        Setting::writeGroup('referral', ['member_points' => 100]);
        $this->collect($this->referred());
        Rewards::redeem($this->member, 40, '30.00', '763921', (string) Str::uuid());
        $this->assertSame(60, Rewards::summary($this->member->id)['points']);
        $this->collect($this->referred());
        $this->assertSame(160, Rewards::points($this->member->id));
        $this->assertSame('1950.00', $this->account->fresh()->principal_balance);
        $this->assertSame(1, DB::table('membership_milestone_bonuses')->where('customer_id', $this->member->id)->count());
        $this->collect($this->referred());
        $this->assertSame('4950.00', $this->account->fresh()->principal_balance);
        $this->assertSame(260, Rewards::points($this->member->id));
    }

    public function test_crossing_multiple_thresholds_pays_each_once_and_bonus_cash_does_not_count_as_points(): void
    {
        Setting::writeGroup('referral', ['member_points' => 300]);
        $this->collect($this->referred());
        $this->assertSame('12750.00', $this->account->fresh()->principal_balance);
        $this->assertSame(300, Rewards::points($this->member->id));
        \App\Services\Membership\MilestoneBonusService::collect($this->member->id);
        $this->assertSame('12750.00', $this->account->fresh()->principal_balance);
        $rows = DB::table('membership_milestone_bonuses')->where('customer_id', $this->member->id)->get();
        $this->assertCount(3, $rows);
        foreach ($rows as $row) $this->assertBalanced($row->voucher_id);
    }

    public function test_new_tier_checks_remaining_points_instead_of_lifetime_earnings(): void
    {
        Setting::writeGroup('referral_milestones', ['enabled' => false]);
        Setting::writeGroup('referral', ['member_points' => 100]);
        $this->collect($this->referred());
        Rewards::redeem($this->member, 40, '30.00', '763921', (string) Str::uuid());
        Setting::writeGroup('referral_milestones', ['enabled' => true]);
        \App\Services\Membership\MilestoneBonusService::collect($this->member->id);
        $this->assertSame('1200.00', $this->account->fresh()->principal_balance);
        $this->assertSame(0, DB::table('membership_milestone_bonuses')->where('customer_id', $this->member->id)->count());
    }

    public function test_pending_bonus_preserves_amount_and_pays_once_after_account_is_active(): void
    {
        $this->account->update(['status' => 'Closed']);
        Setting::writeGroup('referral', ['member_points' => 100]);
        $this->collect($this->referred());
        $row = DB::table('membership_milestone_bonuses')->where('customer_id', $this->member->id)->first();
        $this->assertNull($row->paid_at);
        $this->assertSame('750.00', $row->amount);
        Setting::writeGroup('referral', ['point_value' => '50.00']);
        $this->account->update(['status' => 'Active']);
        \App\Services\Membership\MilestoneBonusService::collect($this->member->id);
        $this->assertSame('750.00', $this->account->fresh()->principal_balance);
        $this->assertNotNull(DB::table('membership_milestone_bonuses')->find($row->id)->paid_at);
    }

    public function test_milestone_api_is_scoped_and_retry_safe(): void
    {
        Setting::writeGroup('referral', ['member_points' => 100]);
        $this->collect($this->referred());
        Sanctum::actingAs($this->member);
        $this->postJson('/api/membership/v1/referrals/milestones/collect')->assertOk()->assertJsonPath('data.milestones.bonuses.0.amount', '750.00');
        $this->postJson('/api/membership/v1/referrals/milestones/collect')->assertOk();
        $this->assertSame('750.00', $this->account->fresh()->principal_balance);
        Sanctum::actingAs($this->admin);
        $this->postJson('/api/membership/v1/referrals/milestones/collect')->assertForbidden();
    }

    public function test_dynamic_tier_settings_persist_ids_and_validate_thresholds(): void
    {
        $controller = app(TransactionSettingController::class);
        $old = membership_transaction_settings('referral_milestones')->tiers;
        $tiers = $old;
        $tiers[] = ['id' => null, 'threshold' => 400, 'percentage' => '125.5', 'enabled' => true];
        $controller->update(Request::create('/', 'POST', ['enabled' => 1, 'tiers' => $tiers]), 'referral_milestones');
        $saved = membership_transaction_settings('referral_milestones')->tiers;
        $this->assertCount(4, $saved);
        $this->assertSame('125.50', $saved[3]['percentage']);
        $this->assertTrue(Str::isUuid($saved[3]['id']));
        $controller->update(Request::create('/', 'POST', ['enabled' => 1, 'tiers' => [$saved[3]]]), 'referral_milestones');
        $retained = collect(membership_transaction_settings('referral_milestones')->tiers)->keyBy('id');
        $this->assertFalse($retained[$old[0]['id']]['enabled']);
        $this->assertStringContainsString('Referral Milestone Bonus', $controller->index()->with('errors', new ViewErrorBag)->render());
        $this->expectException(ValidationException::class);
        $controller->update(Request::create('/', 'POST', ['tiers' => [['id' => null, 'threshold' => 0, 'percentage' => -1, 'enabled' => 1]]]), 'referral_milestones');
    }

    public function test_milestone_rounds_money_once_and_uses_configured_threshold_and_percentage(): void
    {
        Setting::writeGroup('referral', ['member_points' => 3, 'point_value' => '0.05']);
        Setting::writeGroup('referral_milestones', ['enabled' => true, 'tiers' => [
            ['id' => (string) Str::uuid(), 'threshold' => 3, 'percentage' => '12.50', 'enabled' => true],
        ]]);
        $this->collect($this->referred());
        $this->assertSame('0.02', $this->account->fresh()->principal_balance);
        $this->assertSame(3, Rewards::points($this->member->id));
    }

    public function test_invalid_milestone_expense_keeps_bonus_pending_without_blocking_fee_collection(): void
    {
        Setting::writeGroup('referral', ['member_points' => 100, 'expense_head_id' => $this->income->id]);
        $a = $this->referred();
        $this->collect($a);
        $this->assertSame('100.00', $a->fresh()->principal_balance);
        $bonus = DB::table('membership_milestone_bonuses')->where('customer_id', $this->member->id)->first();
        $this->assertNull($bonus->paid_at);
        $this->assertSame('0.00', $this->account->fresh()->principal_balance);
        Setting::writeGroup('referral', ['expense_head_id' => $this->expense->id]);
        \App\Services\Membership\MilestoneBonusService::collect($this->member->id);
        $this->assertSame('750.00', $this->account->fresh()->principal_balance);
    }

}
