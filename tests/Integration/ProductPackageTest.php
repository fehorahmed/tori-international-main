<?php

namespace Tests\Integration;

use App\Http\Controllers\Ecommerce\PackageOrderController;
use App\Http\Controllers\Ecommerce\ProductPackageController;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\Inventory\Inventory;
use App\Models\Sales\SaleOrderItem;
use App\Models\Settings\Category;
use App\Models\Settings\Product;
use App\Models\Settings\Unit;
use App\Models\Settings\Warehouse;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Ecommerce\CartService;
use App\Services\Packages\PackageOrderService;
use App\Services\Packages\ProductPackageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductPackageTest extends TestCase
{
    use DatabaseTransactions;

    private Product $a;

    private Product $b;

    private Product $package;

    private Warehouse $warehouse;

    private User $operator;

    private Supplier $customer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        \App\Models\MembershipTransactionSetting::writeGroup('membership', ['registration_fee' => '0.00', 'account_hold_amount' => '100.00']);
        \App\Models\MembershipTransactionSetting::writeGroup('membership_discount', ['enabled' => true, 'minimum_balance' => '1000.00']);
        $this->warehouse = Warehouse::where('status', 1)->whereHas('sisterConcern', fn ($q) => $q->where('status', 1))->firstOrFail();
        $this->operator = User::firstOrFail();
        $this->operator->givePermissionTo(['ecommerce_product', 'ecommerce_product_create', 'ecommerce_product_edit', 'ecommerce_order', 'ecommerce_order_confirm', 'ecommerce_order_confirm']);
        $this->actingAs($this->operator)->withSession(['active_sister_concern_id' => $this->warehouse->sister_concern_id]);
        $this->customer = Supplier::create(['name' => 'Package rollback customer', 'type' => 'Customer', 'status' => 1]);
        $this->a = $this->product('Component A', '100.00');
        $this->b = $this->product('Component B', '50.00');
        $this->package = $this->product('Test package', '225.01', ['catalog_type' => 'package', 'package_warehouse_id' => $this->warehouse->id]);
        app(ProductPackageService::class)->replaceItems($this->package, [['product_id' => $this->a->id, 'quantity' => 2], ['product_id' => $this->b->id, 'quantity' => 1]]);
        foreach ([[$this->a, 10], [$this->b, 3]] as [$p,$qty]) {
            Inventory::create(['warehouse_id' => $this->warehouse->id, 'sister_concern_id' => $this->warehouse->sister_concern_id, 'product_id' => $p->id, 'quantity' => $qty, 'unit_price' => '10.00', 'product_code' => $p->code, 'batch_no' => 'PKG-'.Str::random(8)]);
        }
    }

    public function test_package_order_list_redirects_to_existing_orders_and_details_remain_accessible(): void
    {
        $this->get(route('package-orders.index'))->assertRedirect(route('ecom_order'));
        $checkout = app(PackageOrderService::class)->checkout($this->customer, $this->data());
        $this->get(route('package-orders.show', $checkout))
            ->assertOk()->assertSee($checkout->saleOrder->order_no)
            ->assertSee('Back to Orders')->assertSee(route('ecom_order'), false)
            ->assertDontSee('Product &amp; Package Orders', false);
    }

    public function test_member_discount_preview_exposes_dynamic_requirement_without_changing_guest_price(): void
    {
        $this->a->update(['old_selling_price' => '100.00', 'selling_price' => '90.00',
            'discount_percent' => 10, 'member_discount_percent' => 20]);
        \App\Models\MembershipTransactionSetting::writeGroup('membership_discount', ['enabled' => true, 'minimum_balance' => '1234.56']);
        $guest = \App\Services\Membership\DiscountService::present($this->a->fresh()->toArray(), false);
        $this->assertSame('90.00', $guest['selling_price']);
        $this->assertFalse($guest['membership_discount_eligible']);
        $this->assertEquals(20, $guest['member_discount_percent']);
        $this->assertSame('1234.56', $guest['membership_discount_minimum_balance']);
        $member = \App\Services\Membership\DiscountService::present($this->a->fresh()->toArray(), true);
        $this->assertSame('80.00', $member['selling_price']);
        $this->assertTrue($member['membership_discount_eligible']);
        \App\Models\MembershipTransactionSetting::writeGroup('membership_discount', ['enabled' => false]);
        $disabled = \App\Services\Membership\DiscountService::present($this->a->fresh()->toArray(), false);
        $this->assertFalse($disabled['membership_discount_enabled']);
        $this->assertFalse($disabled['member_discount_visible']);
        $this->assertSame('90.00', $disabled['selling_price']);
    }

    public function test_member_discount_label_only_appears_when_strictly_better_than_general_and_special(): void
    {
        $this->a->update(['old_selling_price' => '100.00', 'selling_price' => '90.00',
            'discount_percent' => 10, 'member_discount_percent' => 20]);
        $present = fn ($eligible) => \App\Services\Membership\DiscountService::present($this->a->fresh()->toArray(), $eligible);
        $guest = $present(false);
        $member = $present(true);
        $this->assertTrue($guest['member_discount_visible']);
        $this->assertSame(10.0, (float) $member['standard_discount_percent']);
        $this->assertSame('member', $member['discount_source']);
        $this->assertSame('80.00', $member['selling_price']);
        $this->offer($this->a, 'fixed', '20.00');
        $this->assertFalse($present(false)['member_discount_visible']);
        $this->assertFalse($present(true)['member_discount_visible']);
        $this->a->update(['member_discount_percent' => 25]);
        $member = $present(true);
        $this->assertTrue($member['member_discount_visible']);
        $this->assertEquals(20, $member['standard_discount_percent']);
        $this->assertSame('75.00', $member['selling_price']);
        $this->a->update(['member_discount_percent' => 5]);
        $this->assertFalse($present(false)['member_discount_visible']);
        $this->assertSame('special_offer', $present(true)['discount_source']);
    }

    public function test_special_offer_menu_availability_requires_active_published_offer_product(): void
    {
        \Illuminate\Support\Facades\DB::table('offer_campaigns')->update(['is_active' => 0]);
        $this->getJson('/api/special-offer-availability')->assertOk()->assertJsonPath('available', false);
        $campaign = $this->offer($this->a);
        $this->getJson('/api/special-offer-availability')->assertOk()->assertJsonPath('available', true);
        $this->a->update(['is_show' => 0]);
        $this->getJson('/api/special-offer-availability')->assertOk()->assertJsonPath('available', false);
        $this->a->update(['is_show' => 1]);
        \Illuminate\Support\Facades\DB::table('offer_campaigns')->where('id', $campaign)->update(['ends_at' => now()->subMinute()]);
        $this->getJson('/api/special-offer-availability')->assertOk()->assertJsonPath('available', false);
    }

    private function pointFunding(int $points = 100): void
    {
        $expense = \App\Models\Account\AccountHead::create(['name' => 'Points expense test', 'nature' => 'Debit',
            'status' => 1, 'account_group_id' => \App\Models\Account\AccountGroup::where('reserved_id', 25)->firstOrFail()->id]);
        \App\Models\MembershipTransactionSetting::writeGroup('referral', ['enabled' => true, 'point_value' => '30.00',
            'expense_head_id' => $expense->id, 'posting_user_id' => $this->operator->id]);
        \Illuminate\Support\Facades\DB::table('membership_referral_rewards')->insert([
            'referral_id' => 10000000 + $this->customer->id, 'registration_fee_id' => 10000000 + $this->customer->id,
            'referrer_type' => 'member', 'referrer_id' => $this->customer->id, 'branch_id' => $this->warehouse->sister_concern_id,
            'points' => $points, 'amount' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_points_order_uses_mrp_includes_delivery_posts_balanced_ledger_and_refunds_exactly_once(): void
    {
        $this->pointFunding();
        $account = $this->fundedSavings();
        $this->package->update(['old_selling_price' => '300.00', 'selling_price' => '225.01', 'discount_percent' => 25, 'member_discount_percent' => 30]);
        $this->offer($this->package, 'percentage', '40.00');
        $data = $this->data();
        $data['payment_method'] = 'points'; $data['point_value'] = '30.00'; $data['expected_total'] = '350.00';
        $service = app(PackageOrderService::class);
        $checkout = $service->checkout($this->customer, $data);
        $order = $checkout->saleOrder->fresh();
        $this->assertEquals(350, $order->total);
        $this->assertEquals(0, $order->discount);
        $this->assertEquals(350, $order->paid);
        $this->assertEquals(0, $order->due);
        $this->assertSame('0.00', $checkout->lines[0]['discount_total']);
        $this->assertSame('none', $checkout->lines[0]['discount_source']);
        $entry = \Illuminate\Support\Facades\DB::table('membership_point_transactions')->where('checkout_id', $checkout->id)->first();
        $this->assertSame('11.666667', $entry->points);
        $this->assertSame(88333333, \App\Services\Membership\ReferralRewardService::pointUnits($this->customer->id));
        $this->assertSame($checkout->id, $service->checkout($this->customer, $data)->id);
        $this->assertEquals(1000, $account->fresh()->principal_balance);
        $service->confirm($checkout, $this->operator);
        $service->cancel($checkout, $this->operator);
        $service->cancel($checkout->fresh(), $this->operator);
        $this->assertSame(100, \App\Services\Membership\ReferralRewardService::points($this->customer->id));
        $this->assertEquals(1000, $account->fresh()->principal_balance);
        $this->assertSame(2, \Illuminate\Support\Facades\DB::table('membership_point_transactions')->where('checkout_id', $checkout->id)->count());
        $this->assertEquals(0, $order->fresh()->paid);
        foreach (Voucher::where('sale_order_id', $order->id)->get() as $voucher) {
            $this->assertEquals(Transaction::where('voucher_id', $voucher->id)->where('transaction_type', 1)->sum('amount'),
                Transaction::where('voucher_id', $voucher->id)->where('transaction_type', 2)->sum('amount'));
        }
        $this->assertEquals(10, Inventory::where('warehouse_id', $this->warehouse->id)->where('product_id', $this->a->id)->sum('quantity'));
    }

    public function test_points_order_insufficient_points_rolls_back_order_and_money_ledger(): void
    {
        $this->pointFunding(1);
        $data = $this->data(); $data['payment_method'] = 'points'; $data['point_value'] = '30.00';
        $count = \App\Models\Sales\SaleOrder::count();
        try { app(PackageOrderService::class)->checkout($this->customer, $data); $this->fail('Must reject insufficient points.'); }
        catch (ValidationException $e) { $this->assertStringContainsString('Insufficient membership points', $e->getMessage()); }
        $this->assertSame($count, \App\Models\Sales\SaleOrder::count());
        $this->assertSame(1, \App\Services\Membership\ReferralRewardService::points($this->customer->id));
        $this->assertSame(0, \Illuminate\Support\Facades\DB::table('membership_point_transactions')->where('customer_id', $this->customer->id)->count());
    }

    public function test_points_order_rejects_stale_rate_coupon_and_discounted_total(): void
    {
        $this->pointFunding();
        foreach ([['point_value' => '29.00'], ['coupon_code' => 'FREE'], ['expected_total' => '1.00']] as $invalid) {
            $data = array_replace($this->data(), ['payment_method' => 'points', 'point_value' => '30.00'], $invalid);
            try { app(PackageOrderService::class)->checkout($this->customer, $data); $this->fail('Invalid point checkout must fail.'); }
            catch (ValidationException $e) { $this->assertNotEmpty($e->errors()); }
        }
        $this->assertSame(100, \App\Services\Membership\ReferralRewardService::points($this->customer->id));
    }

    public function test_points_order_api_returns_snapshot_and_cancel_uses_original_rate_after_settings_change(): void
    {
        $this->pointFunding();
        $this->actingAs($this->customer, 'customer');
        $data = array_replace($this->data(), ['payment_method' => 'points', 'point_value' => '30.00', 'expected_total' => '275.01']);
        $response = $this->postJson('/api/package-checkout', $data)->assertCreated()->assertJsonPath('order.payment_method', 'points');
        $this->assertSame('9.167000', $response->json('order.point_payment.points'));
        $checkout = \App\Models\Ecommerce\PackageOrderCheckout::where('request_key', $data['request_key'])->firstOrFail();
        \App\Models\MembershipTransactionSetting::writeGroup('referral', ['enabled' => false, 'point_value' => '99.00']);
        app(PackageOrderService::class)->cancel($checkout, $this->operator);
        $this->assertSame(100, \App\Services\Membership\ReferralRewardService::points($this->customer->id));
        $this->assertSame('30.00', \Illuminate\Support\Facades\DB::table('membership_point_transactions')->where('checkout_id', $checkout->id)->where('type', 'Refund')->value('point_value'));
    }

    public function test_point_purchase_and_manual_redemption_share_remaining_balance(): void
    {
        $this->pointFunding(10);
        $this->fundedSavings();
        $this->customer->update(['pin' => \Illuminate\Support\Facades\Hash::make('763921')]);
        $data = array_replace($this->data(), ['payment_method' => 'points', 'point_value' => '30.00']);
        $checkout = app(PackageOrderService::class)->checkout($this->customer, $data);
        $this->assertSame(833000, \App\Services\Membership\ReferralRewardService::pointUnits($this->customer->id));
        try {
            \App\Services\Membership\ReferralRewardService::redeem($this->customer, 1, '30.00', '763921', (string) Str::uuid());
            $this->fail('Points spent at checkout cannot also be redeemed.');
        } catch (ValidationException $e) { $this->assertStringContainsString('Insufficient referral points', $e->getMessage()); }
        app(PackageOrderService::class)->cancel($checkout, $this->operator);
        $this->assertSame(10, \App\Services\Membership\ReferralRewardService::points($this->customer->id));
    }

    public function test_ordinary_product_points_checkout_and_disabled_points_setting(): void
    {
        $this->pointFunding();
        $this->a->update(['old_selling_price' => '100.00', 'selling_price' => '80.00', 'discount_percent' => 20]);
        $data = array_replace($this->data(), ['payment_method' => 'points', 'point_value' => '30.00', 'expected_total' => '150.00',
            'items' => [['product_id' => $this->a->id, 'quantity' => 1]]]);
        $this->actingAs($this->customer, 'customer');
        $this->postJson('/api/order', $data)->assertCreated()->assertJsonPath('order.point_payment.points', '5.000000');
        \App\Models\MembershipTransactionSetting::writeGroup('referral', ['enabled' => false]);
        $data['request_key'] = (string) Str::uuid();
        $this->postJson('/api/order', $data)->assertUnprocessable();
        $this->assertSame(95, \App\Services\Membership\ReferralRewardService::points($this->customer->id));
    }

    public function test_unified_point_history_includes_credits_debits_refunds_and_is_member_scoped(): void
    {
        $this->pointFunding();
        $this->fundedSavings();
        $this->customer->update(['pin' => \Illuminate\Support\Facades\Hash::make('763921')]);
        $data = array_replace($this->data(), ['payment_method' => 'points', 'point_value' => '30.00']);
        $checkout = app(PackageOrderService::class)->checkout($this->customer, $data);
        app(PackageOrderService::class)->cancel($checkout, $this->operator);
        \App\Services\Membership\ReferralRewardService::redeem($this->customer, 1, '30.00', '763921', (string) Str::uuid());
        $this->actingAs($this->customer, 'customer');
        $summary = \App\Services\Membership\ReferralRewardService::summary($this->customer->id);
        $history = json_decode(json_encode($summary['history']), true);
        $this->assertSame(4, $history['total']);
        $this->assertEqualsCanonicalizing(['referral', 'purchase', 'refund', 'redemption'], array_column($history['data'], 'type'));
        $byType = collect($history['data'])->keyBy('type');
        $this->assertSame('credit', $byType['referral']['direction']);
        $this->assertSame('debit', $byType['purchase']['direction']);
        $this->assertSame('credit', $byType['refund']['direction']);
        $this->assertSame('debit', $byType['redemption']['direction']);
        $this->assertSame($checkout->saleOrder->order_no, $byType['purchase']['order_no']);
        $this->assertSame('9.167000', $byType['purchase']['points']);
        $this->assertStringContainsString('T', $byType['purchase']['created_at']);
        $other = Supplier::create(['name' => 'History isolation', 'type' => 'Customer', 'status' => 1]);
        $this->assertSame(0, \App\Services\Membership\ReferralRewardService::history($other->id)['total']);
        $this->assertSame(99, $summary['points']);
    }

    public function test_point_history_pagination_and_authenticated_endpoint(): void
    {
        $this->pointFunding();
        for ($i = 1; $i <= 22; $i++) {
            \Illuminate\Support\Facades\DB::table('membership_referral_rewards')->insert([
                'referral_id' => 20000000 + $this->customer->id * 100 + $i,
                'registration_fee_id' => 20000000 + $this->customer->id * 100 + $i,
                'referrer_type' => 'member', 'referrer_id' => $this->customer->id,
                'branch_id' => $this->warehouse->sister_concern_id, 'points' => 1, 'amount' => 0,
                'created_at' => now()->subMinutes($i), 'updated_at' => now(),
            ]);
        }
        $first = \App\Services\Membership\ReferralRewardService::history($this->customer->id);
        $second = \App\Services\Membership\ReferralRewardService::history($this->customer->id, 2);
        $this->assertSame(23, $first['total']);
        $this->assertCount(20, $first['data']);
        $this->assertCount(3, $second['data']);
        $this->assertEmpty(array_intersect(array_column($first['data'], 'id'), array_column($second['data'], 'id')));
        $this->actingAs($this->customer, 'sanctum');
        $this->getJson('/api/membership/v1/referrals/history?page=2')->assertOk()->assertJsonPath('data.current_page', 2)->assertJsonCount(3, 'data.data');
        $this->getJson('/api/membership/v1/referrals/history?page=0')->assertUnprocessable();
    }

    public function test_point_history_period_filters_use_bangladesh_calendar_boundaries(): void
    {
        \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2026-11-17 12:00:00', 'Asia/Dhaka'));
        try {
            $this->pointFunding();
            $local = \Carbon\Carbon::now('Asia/Dhaka');
            $dates = [$local->copy()->startOfDay(), $local->copy()->startOfDay()->subSecond(),
                $local->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->subSecond(), $local->copy()->startOfMonth()->subSecond()];
            \Illuminate\Support\Facades\DB::table('membership_referral_rewards')->where('referrer_type', 'member')
                ->where('referrer_id', $this->customer->id)->update(['created_at' => $dates[0]->setTimezone(config('app.timezone'))->toDateTimeString()]);
            foreach (array_slice($dates, 1) as $i => $date) {
                \Illuminate\Support\Facades\DB::table('membership_referral_rewards')->insert([
                    'referral_id' => 30000000 + $this->customer->id * 100 + $i,
                    'registration_fee_id' => 30000000 + $this->customer->id * 100 + $i,
                    'referrer_type' => 'member', 'referrer_id' => $this->customer->id,
                    'branch_id' => $this->warehouse->sister_concern_id, 'points' => 1, 'amount' => 0,
                    'created_at' => $date->setTimezone(config('app.timezone'))->toDateTimeString(), 'updated_at' => now(),
                ]);
            }
            foreach (['today' => 1, 'weekly' => 2, 'monthly' => 3, 'all' => 4] as $period => $count) {
                $this->assertSame($count, \App\Services\Membership\ReferralRewardService::history($this->customer->id, 1, $period)['total']);
            }
            $this->actingAs($this->customer, 'sanctum');
            $this->getJson('/api/membership/v1/referrals/history?period=today')->assertOk()->assertJsonCount(1, 'data.data');
            $this->getJson('/api/membership/v1/referrals/history?period=invalid')->assertUnprocessable();
        } finally { \Carbon\Carbon::setTestNow(); }
    }

    private function product(string $name, string $price, array $extra = []): Product
    {
        return Product::create($extra + ['name' => $name.' '.Str::random(8), 'code' => 'PKGTEST-'.Str::random(10), 'slug' => 'pkgtest-'.Str::random(15), 'category_id' => Category::firstOrFail()->id, 'unit_id' => Unit::firstOrFail()->id, 'status' => 1, 'is_show' => 1, 'selling_price' => $price, 'old_selling_price' => $price, 'user_id' => $this->operator->id, 'catalog_type' => 'single']);
    }

    private function data(): array
    {
        return ['request_key' => (string) Str::uuid(), 'billing' => ['name' => 'Package customer', 'phone' => '01712345678', 'address' => 'Rollback test address'], 'payment_method' => 'cod', 'shipping_charge' => '50.00', 'items' => [['product_id' => $this->package->id, 'quantity' => 1, 'price' => '0.01']]];
    }

    private function fundedSavings(?int $branch = null): \App\Models\ShomriddhiAdvance\ShomriddhiAccount
    {
        $branch ??= $this->warehouse->sister_concern_id;
        $this->customer->update(['origin_sister_concern_id' => $branch]);
        \App\Models\Account\AccountHead::create(['name' => 'Checkout savings test', 'nature' => 'Credit',
            'supplier_id' => $this->customer->id, 'head_type' => 'savings', 'status' => 1,
            'account_group_id' => \App\Models\Account\AccountGroup::where('reserved_id', 28)->firstOrFail()->id]);
        return \App\Models\ShomriddhiAdvance\ShomriddhiAccount::create(['customer_id' => $this->customer->id,
            'origin_sister_concern_id' => $branch, 'principal_balance' => '1000.00', 'status' => 'Active']);
    }

    public function test_account_checkout_debits_immediately_and_pending_cancellation_refunds_once(): void
    {
        $account = $this->fundedSavings();
        $data = array_replace($this->data(), ['payment_method' => 'membership']);
        $service = app(PackageOrderService::class);
        $checkout = $service->checkout($this->customer, $data);
        $this->assertSame('724.99', $account->fresh()->principal_balance);
        $this->assertEquals(275.01, $checkout->saleOrder->paid);
        $this->assertEquals(0, $checkout->saleOrder->due);
        $this->assertSame($checkout->id, $service->checkout($this->customer, $data)->id);
        $this->assertSame('724.99', $account->fresh()->principal_balance);
        $service->cancel($checkout, $this->operator);
        $service->cancel($checkout, $this->operator);
        $this->assertSame('1000.00', $account->fresh()->principal_balance);
        $this->assertSame('Cancelled', $checkout->fresh()->status);
        $this->assertSame('Refunded', \Illuminate\Support\Facades\DB::table('membership_order_payments')->where('checkout_id', $checkout->id)->value('status'));
        $this->assertSame(2, Voucher::where('sale_order_id', $checkout->sale_order_id)->count());
        $this->assertSame(10, (int) Inventory::where('product_id', $this->a->id)->sum('quantity'));
        $history = app(\App\Services\Membership\RecentTransactionService::class)->forCustomer($this->customer);
        $this->assertCount(2, array_filter($history, fn ($row) => $row['category_en'] === 'Ecommerce'));
    }

    public function test_paid_confirmed_order_cancellation_restores_stock_and_reverses_all_journals(): void
    {
        $account = $this->fundedSavings();
        $service = app(PackageOrderService::class);
        $checkout = $service->checkout($this->customer, array_replace($this->data(), ['payment_method' => 'membership']));
        $service->confirm($checkout, $this->operator);
        $this->assertSame('724.99', $account->fresh()->principal_balance);
        $this->assertSame(8, (int) Inventory::where('product_id', $this->a->id)->sum('quantity'));
        $service->cancel($checkout, $this->operator);
        $service->cancel($checkout, $this->operator);
        $this->assertSame(10, (int) Inventory::where('product_id', $this->a->id)->sum('quantity'));
        $this->assertSame('1000.00', $account->fresh()->principal_balance);
        $entries = Transaction::where('sale_order_id', $checkout->sale_order_id)->get();
        foreach ($entries->groupBy('account_head_id') as $rows) {
            $this->assertEquals($rows->where('transaction_type', 1)->sum('amount'), $rows->where('transaction_type', 2)->sum('amount'));
        }
        foreach ($entries->groupBy('voucher_id') as $rows) {
            $this->assertEquals($rows->where('transaction_type', 1)->sum('amount'), $rows->where('transaction_type', 2)->sum('amount'));
        }
    }

    public function test_account_checkout_cannot_spend_minimum_or_cashout_reservations(): void
    {
        $account = $this->fundedSavings();
        \App\Models\MembershipTransactionSetting::writeGroup('membership', ['account_hold_amount' => '725.00']);
        $before = \App\Models\Sales\SaleOrder::count();
        try {
            app(PackageOrderService::class)->checkout($this->customer, array_replace($this->data(), ['payment_method' => 'membership']));
            $this->fail('Minimum balance must not be spent.');
        } catch (ValidationException) {
            $this->assertSame('1000.00', $account->fresh()->principal_balance);
            $this->assertSame($before, \App\Models\Sales\SaleOrder::count());
        }
        \App\Models\MembershipTransactionSetting::writeGroup('membership', ['account_hold_amount' => '724.99']);
        $checkout = app(PackageOrderService::class)->checkout($this->customer, array_replace($this->data(), ['payment_method' => 'membership']));
        $this->assertSame('724.99', $account->fresh()->principal_balance);
        $this->assertSame('membership', $checkout->payment_method);
    }

    public function test_cross_branch_account_payment_and_refund_are_balanced_per_branch(): void
    {
        $origin = \App\Models\BranchManagement\SisterConcern::where('status', 1)->whereKeyNot($this->warehouse->sister_concern_id)->firstOrFail();
        $account = $this->fundedSavings($origin->id);
        $service = app(PackageOrderService::class);
        $checkout = $service->checkout($this->customer, array_replace($this->data(), ['payment_method' => 'membership']));
        $this->assertSame('724.99', $account->fresh()->principal_balance);
        $service->cancel($checkout, $this->operator);
        $this->assertSame('1000.00', $account->fresh()->principal_balance);
        $entries = Transaction::where('sale_order_id', $checkout->sale_order_id)->get();
        $this->assertCount(4, $entries->groupBy('voucher_id'));
        foreach ($entries->groupBy(fn ($e) => $e->sister_concern_id.'-'.$e->account_head_id) as $rows) {
            $this->assertEquals($rows->where('transaction_type', 1)->sum('amount'), $rows->where('transaction_type', 2)->sum('amount'));
        }
    }

    public function test_existing_cashout_holds_cannot_be_spent_on_checkout(): void
    {
        $account = $this->fundedSavings();
        \App\Models\MembershipCashout::create(['reference' => 'CO-'.Str::ulid(), 'customer_id' => $this->customer->id,
            'account_id' => $account->id, 'branch_id' => $this->warehouse->sister_concern_id,
            'cash_head_id' => \App\Models\Account\AccountHead::firstOrFail()->id, 'idempotency_key' => (string) Str::uuid(),
            'quote_hash' => str_repeat('a', 64), 'amount' => '700.00', 'fee' => '0.00', 'total' => '700.00',
            'charge_type' => 'fixed', 'charge_rate' => '0.00', 'status' => 'Accepted', 'expires_at' => now()->addMinutes(10)]);
        $this->expectException(ValidationException::class);
        app(PackageOrderService::class)->checkout($this->customer, array_replace($this->data(), ['payment_method' => 'membership']));
    }

    public function test_missing_payment_ledger_rolls_back_order_without_debit(): void
    {
        $account = $this->fundedSavings();
        \App\Models\Account\AccountHead::where('supplier_id', $this->customer->id)->where('head_type', 'savings')->update(['status' => 0]);
        $before = \App\Models\Sales\SaleOrder::count();
        try {
            app(PackageOrderService::class)->checkout($this->customer, array_replace($this->data(), ['payment_method' => 'membership']));
            $this->fail('Invalid accounting must not produce an order.');
        } catch (ValidationException) {
            $this->assertSame('1000.00', $account->fresh()->principal_balance);
            $this->assertSame($before, \App\Models\Sales\SaleOrder::count());
        }
    }

    public function test_dispatched_order_cannot_be_cancelled_or_refunded(): void
    {
        $account = $this->fundedSavings();
        $service = app(PackageOrderService::class);
        $checkout = $service->checkout($this->customer, array_replace($this->data(), ['payment_method' => 'membership']));
        $service->confirm($checkout, $this->operator);
        $checkout->saleOrder->update(['status' => 2]);
        try {
            $service->cancel($checkout, $this->operator);
            $this->fail('Dispatched orders require a return workflow.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
            $this->assertSame('724.99', $account->fresh()->principal_balance);
            $this->assertSame('Confirmed', $checkout->fresh()->status);
        }
    }

    public function test_authenticated_api_can_pay_for_ordinary_products_from_savings(): void
    {
        $account = $this->fundedSavings();
        $data = array_replace($this->data(), ['payment_method' => 'membership', 'expected_total' => '150.00', 'items' => [['product_id' => $this->a->id, 'quantity' => 1]]]);
        $this->actingAs($this->customer, 'customer')->postJson('/api/order', $data)->assertCreated()->assertJsonPath('order.payment_method', 'membership')->assertJsonPath('order.due', 0);
        $this->assertSame('850.00', $account->fresh()->principal_balance);
    }

    public function test_checkout_rejects_changed_total_before_debit(): void
    {
        $account = $this->fundedSavings();
        $data = array_replace($this->data(), ['payment_method' => 'membership', 'expected_total' => '1.00']);
        $this->actingAs($this->customer, 'customer')->postJson('/api/package-checkout', $data)->assertUnprocessable();
        $this->assertSame('1000.00', $account->fresh()->principal_balance);
        $this->assertSame(0, \Illuminate\Support\Facades\DB::table('membership_order_payments')->where('customer_id', $this->customer->id)->count());
    }

    public function test_admin_order_datatable_includes_paid_by_for_cod_and_membership_orders(): void
    {
        $this->fundedSavings();
        $service = app(PackageOrderService::class);
        $cod = $service->checkout($this->customer, $this->data());
        $paid = $service->checkout($this->customer, array_replace($this->data(), ['payment_method' => 'membership']));
        $response = app(\App\Http\Controllers\Ecommerce\OrderController::class)->datatable(Request::create('/', 'GET'));
        $rows = collect($response->getData(true)['data'])->keyBy('id');
        $this->assertSame('Cash on Delivery', $rows[$cod->sale_order_id]['paid_by']);
        $this->assertSame('Membership Account', $rows[$paid->sale_order_id]['paid_by']);
    }

    public function test_discount_uses_pre_payment_balance_and_snapshot_survives_lower_balance_and_settings_changes(): void
    {
        $account = $this->fundedSavings();
        $this->a->update(['old_selling_price' => '100.00', 'selling_price' => '100.00', 'discount' => '0.00', 'discount_percent' => 0, 'member_discount_percent' => 20]);
        $data = array_replace($this->data(), ['payment_method' => 'membership', 'expected_total' => '130.00', 'items' => [['product_id' => $this->a->id, 'quantity' => 1]]]);
        $service = app(PackageOrderService::class);
        $checkout = $service->checkout($this->customer, $data);
        $this->assertEquals(100, $checkout->saleOrder->sub_total);
        $this->assertEquals(20, $checkout->saleOrder->discount);
        $this->assertEquals(130, $checkout->saleOrder->paid);
        $this->assertSame('870.00', $account->fresh()->principal_balance);
        $this->assertSame('80.00', $checkout->lines[0]['line_total']);
        $this->assertTrue($checkout->lines[0]['membership_discount_eligible']);
        $this->assertSame($checkout->id, $service->checkout($this->customer, $data)->id);
        \App\Models\MembershipTransactionSetting::writeGroup('membership_discount', ['minimum_balance' => '5000.00', 'enabled' => false]);
        $this->a->update(['selling_price' => '90.00']);
        $service->confirm($checkout, $this->operator);
        $this->assertSame('80.00', $checkout->fresh()->lines[0]['line_total']);
        $this->assertEquals(80, SaleOrderItem::where('sale_order_id', $checkout->sale_order_id)->sum('selling_price'));
        $service->cancel($checkout, $this->operator);
        $this->assertSame('1000.00', $account->fresh()->principal_balance);
    }

    public function test_cart_reprices_existing_product_and_package_discounts_at_dynamic_balance_boundary(): void
    {
        $account = $this->fundedSavings();
        $this->a->update(['old_selling_price' => '100.00', 'selling_price' => '80.00', 'discount' => '20.00', 'discount_percent' => 20, 'member_discount_percent' => 30]);
        $this->package->update(['old_selling_price' => '225.01', 'selling_price' => '200.00', 'discount' => '25.01', 'discount_percent' => 11.12, 'member_discount_percent' => 20]);
        $this->actingAs($this->customer, 'customer');
        $cart = app(CartService::class);
        $cart->add($this->a->id, 1, 99999);
        $cart->add($this->package->id, 1, 99999);
        $this->assertEquals(325.01, $cart->subtotal());
        $this->assertEquals(75.00, $cart->productDiscount());
        $this->assertEquals(250.01, $cart->total());
        $account->update(['principal_balance' => '999.99']);
        $this->assertEquals(45.01, $cart->productDiscount());
        $this->assertEquals(280, $cart->total());
        \App\Models\MembershipTransactionSetting::writeGroup('membership_discount', ['minimum_balance' => '999.99']);
        $this->assertEquals(75.00, $cart->productDiscount());
        \App\Models\MembershipTransactionSetting::writeGroup('membership_discount', ['enabled' => false]);
        $this->assertEquals(45.01, $cart->productDiscount());
    }

    public function test_catalog_prices_are_regular_for_guests_and_discounted_for_eligible_members(): void
    {
        $this->fundedSavings();
        $this->a->update(['old_selling_price' => '100.00', 'selling_price' => '80.00', 'discount' => '20.00', 'discount_percent' => 20, 'member_discount_percent' => 30]);
        $this->getJson('/api/get-product-details-data/'.$this->a->slug)->assertOk()->assertJsonPath('product.selling_price', '80.00')->assertJsonPath('product.discount_percent', 20);
        $this->actingAs($this->customer, 'customer')->getJson('/api/get-product-details-data/'.$this->a->slug)->assertOk()->assertJsonPath('product.selling_price', '70.00');
    }

    public function test_balance_drop_after_cart_review_requires_a_new_total_without_charging(): void
    {
        $account = $this->fundedSavings();
        $this->a->update(['old_selling_price' => '100.00', 'selling_price' => '80.00', 'discount' => '20.00', 'discount_percent' => 20, 'member_discount_percent' => 30]);
        $account->update(['principal_balance' => '999.99']);
        $data = array_replace($this->data(), ['payment_method' => 'membership', 'expected_total' => '120.00', 'items' => [['product_id' => $this->a->id, 'quantity' => 1]]]);
        $this->actingAs($this->customer, 'customer')->postJson('/api/order', $data)->assertUnprocessable();
        $this->assertSame('999.99', $account->fresh()->principal_balance);
        $this->assertSame(0, \Illuminate\Support\Facades\DB::table('membership_order_payments')->where('customer_id', $this->customer->id)->count());
    }

    public function test_cod_without_an_eligible_account_gets_regular_price_even_with_forged_discount(): void
    {
        $this->a->update(['old_selling_price' => '100.00', 'selling_price' => '80.00', 'discount' => '20.00', 'discount_percent' => 20, 'member_discount_percent' => 30]);
        $data = array_replace($this->data(), ['items' => [['product_id' => $this->a->id, 'quantity' => 1, 'discount' => 99999]]]);
        $checkout = app(PackageOrderService::class)->checkout($this->customer, $data);
        $this->assertEquals(130, $checkout->saleOrder->total);
        $this->assertEquals(20, $checkout->saleOrder->discount);
        $this->actingAs($this->customer, 'customer')->postJson('/api/buy-now', $data)->assertCreated()->assertJsonPath('order.total', 130);
    }

    public function test_package_admin_preserves_existing_discount_and_can_update_it(): void
    {
        $data = ['name' => 'Discount package', 'category_id' => $this->package->category_id, 'unit_id' => $this->package->unit_id,
            'package_warehouse_id' => $this->warehouse->id, 'selling_price' => '300.00', 'discount' => '10.00', 'member_discount_percent' => '15.00',
            'status' => 1, 'is_show' => 1, 'items' => [['product_id' => $this->a->id, 'quantity' => 2], ['product_id' => $this->b->id, 'quantity' => 1]]];
        $controller = app(\App\Http\Controllers\Ecommerce\ProductPackageController::class);
        $controller->update(Request::create('/', 'PUT', $data), $this->package, app(ProductPackageService::class));
        $this->assertEquals(270, $this->package->fresh()->selling_price);
        $this->assertEquals(300, $this->package->fresh()->old_selling_price);
        $this->assertEquals(15, $this->package->fresh()->member_discount_percent);
        unset($data['discount'], $data['member_discount_percent']);
        $controller->update(Request::create('/', 'PUT', $data), $this->package->fresh(), app(ProductPackageService::class));
        $this->assertEquals(270, $this->package->fresh()->selling_price);
        $this->assertEquals(10, $this->package->fresh()->discount_percent);
        $this->assertEquals(15, $this->package->fresh()->member_discount_percent);
    }

    public function test_single_product_admin_saves_general_and_member_rates_independently(): void
    {
        $request = Request::create('/', 'POST', ['name' => $this->a->name, 'slug' => $this->a->slug,
            'category' => $this->a->category_id, 'unit' => $this->a->unit_id, 'alert_quantity' => 1, 'selling_price' => '100.00',
            'discount' => 20, 'member_discount_percent' => '30.00', 'sort' => 1, 'status' => 1, 'is_show' => 1,
            'is_featured' => 0, 'is_trending' => 0, 'is_best_selling' => 0, 'is_new_arrival' => 0]);
        app(\App\Http\Controllers\Ecommerce\EcomProductController::class)->editPost($this->a, $request);
        $this->assertEquals(80, $this->a->fresh()->selling_price);
        $this->assertEquals(20, $this->a->fresh()->discount_percent);
        $this->assertEquals(30, $this->a->fresh()->member_discount_percent);
        foreach ([10, 20] as $invalidRate) {
            $request->merge(['member_discount_percent' => $invalidRate]);
            try {
                app(\App\Http\Controllers\Ecommerce\EcomProductController::class)->editPost($this->a->fresh(), $request);
                $this->fail('Member discount must be greater than general discount or zero.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('member_discount_percent', $e->errors());
                $this->assertEquals(30, $this->a->fresh()->member_discount_percent);
            }
        }
        $request->merge(['member_discount_percent' => 0]);
        app(\App\Http\Controllers\Ecommerce\EcomProductController::class)->editPost($this->a->fresh(), $request);
        $this->assertEquals(0, $this->a->fresh()->member_discount_percent);
        $request->merge(['member_discount_percent' => 101]);
        $this->expectException(ValidationException::class);
        app(\App\Http\Controllers\Ecommerce\EcomProductController::class)->editPost($this->a->fresh(), $request);
    }

    public function test_member_discount_replaces_general_on_original_price_and_never_stacks(): void
    {
        $product = ['old_selling_price' => '100.00', 'selling_price' => '80.00', 'discount_percent' => 20, 'member_discount_percent' => 30];
        $price = \App\Services\Membership\DiscountService::price($product, true);
        $this->assertSame(7000, $price['net']);
        $this->assertSame(0, $price['general_discount']);
        $this->assertSame(3000, $price['member_discount']);
        $this->assertSame(8000, \App\Services\Membership\DiscountService::price($product, false)['net']);
        foreach ([0, 10, 20] as $rate) {
            $product['member_discount_percent'] = $rate;
            $fallback = \App\Services\Membership\DiscountService::price($product, true);
            $this->assertSame(8000, $fallback['net']);
            $this->assertSame(0, $fallback['member_discount']);
        }
    }

    public function test_eligible_package_discount_posts_net_components_and_refunds_exact_paid_total(): void
    {
        $account = $this->fundedSavings();
        $this->package->update(['old_selling_price' => '225.01', 'selling_price' => '200.00', 'discount' => '25.01', 'discount_percent' => 11.12, 'member_discount_percent' => 20]);
        $service = app(PackageOrderService::class);
        $checkout = $service->checkout($this->customer, array_replace($this->data(), ['payment_method' => 'membership', 'expected_total' => '230.01']));
        $this->assertEquals(45.00, $checkout->saleOrder->discount);
        $this->assertSame('769.99', $account->fresh()->principal_balance);
        $service->confirm($checkout, $this->operator);
        $this->assertEquals(180.01, SaleOrderItem::where('sale_order_id', $checkout->sale_order_id)->get()->sum(fn ($i) => $i->selling_price * $i->quantity));
        $service->cancel($checkout, $this->operator);
        $this->assertSame('1000.00', $account->fresh()->principal_balance);
    }

    public function test_product_details_returns_cart_id_and_images_for_singles_and_packages(): void
    {
        foreach ([$this->a, $this->package] as $product) {
            $this->getJson('/api/get-product-details-data/'.$product->slug)
                ->assertOk()
                ->assertJsonPath('product.id', $product->id)
                ->assertJsonPath('product.images', []);
        }
        $this->getJson('/api/get-product-details-data/missing-product-slug')->assertNotFound();
    }

    public function test_product_and_package_catalogs_are_separate(): void
    {
        foreach (['single', 'package'] as $type) {
            $items = $this->getJson('/api/get-product-data?perPage=200&catalog_type='.$type)
                ->assertOk()->json('products');
            $this->assertNotEmpty($items);
            foreach ($items as $item) {
                $this->assertSame($type, $item['catalog_type']);
            }
        }
        $items = $this->getJson('/api/get-product-data?perPage=200')->assertOk()->json('products');
        $this->assertNotContains($this->package->id, array_column($items, 'id'));
    }

    public function test_admin_product_list_uses_primary_image_without_gallery_queries(): void
    {
        $response = $this->getJson(route('ecom_product.datatable', ['draw' => 1, 'start' => 0, 'length' => 10]));
        $response->assertOk();
        $this->assertArrayNotHasKey('error', $response->json());
        $this->assertNotEmpty($response->json('data'));
        $this->assertStringContainsString('image_preview', $response->json('data.0.image'));
    }

    public function test_cod_product_order_saves_billing_server_totals_and_can_be_confirmed_once(): void
    {
        $data = $this->data();
        $data['items'] = [['product_id' => $this->a->id, 'quantity' => 2, 'price' => '0.01', 'discount' => 99999]];
        $this->actingAs($this->customer, 'customer');
        $response = $this->postJson('/api/order', $data)->assertCreated()->assertJsonPath('success', true);
        $number = $response->json('order_no');
        $this->assertStringStartsWith('ESO-', $number);
        $checkout = \App\Models\Ecommerce\PackageOrderCheckout::where('request_key', $data['request_key'])->firstOrFail();
        $this->assertEquals('250.00', $checkout->saleOrder->total);
        $this->assertEquals(0, $checkout->saleOrder->paid);
        $this->assertEquals('250.00', $checkout->saleOrder->due);
        $this->assertSame($this->operator->id, $checkout->saleOrder->user_id);
        $this->assertSame($data['billing'], $checkout->billing);
        $this->assertEquals(10, Inventory::where('product_id', $this->a->id)->sum('quantity'));
        $this->postJson('/api/order', $data)->assertCreated()->assertJsonPath('order_no', $number);
        $this->assertSame(1, \App\Models\Ecommerce\PackageOrderCheckout::where('request_key', $data['request_key'])->count());
        $this->getJson('/api/order/'.$number)->assertOk()
            ->assertJsonPath('billing_name', $data['billing']['name'])
            ->assertJsonPath('is_package_order', false)
            ->assertJsonPath('is_snapshot_order', true);
        $this->get('/api/order/'.$number.'/'.$data['billing']['phone'].'/receipt')->assertOk();
        $service = app(PackageOrderService::class);
        $service->confirm($checkout, $this->operator);
        $service->confirm($checkout, $this->operator);
        $this->assertEquals(8, Inventory::where('product_id', $this->a->id)->sum('quantity'));
        $this->assertEquals(2, SaleOrderItem::where('sale_order_id', $checkout->sale_order_id)->sum('quantity'));
        $entries = Transaction::where('sale_order_id', $checkout->sale_order_id)->get();
        $this->assertEquals($entries->where('transaction_type', 1)->sum('amount'), $entries->where('transaction_type', 2)->sum('amount'));
    }

    public function test_cod_product_order_rejects_insufficient_stock_without_saving_an_order(): void
    {
        $data = $this->data();
        $data['items'] = [['product_id' => $this->a->id, 'quantity' => 11]];
        $this->actingAs($this->customer, 'customer')->postJson('/api/order', $data)->assertUnprocessable();
        $this->assertDatabaseMissing('package_order_checkouts', ['request_key' => $data['request_key']]);
    }

    public function test_cod_orders_work_when_product_creator_is_missing(): void
    {
        $this->operator->update(['status' => 1, 'sister_concern_id' => $this->warehouse->sister_concern_id]);
        foreach ([$this->a, $this->package] as $product) {
            $product->update(['user_id' => 0]);
            $data = $this->data();
            $data['items'] = [['product_id' => $product->id, 'quantity' => 1]];
            $response = $this->actingAs($this->customer, 'customer')->postJson('/api/order', $data)
                ->assertCreated()->assertJsonPath('success', true);
            $order = \App\Models\Sales\SaleOrder::where('order_no', $response->json('order_no'))->firstOrFail();
            $actor = User::findOrFail($order->user_id);
            $this->assertEquals(1, $actor->status);
            $this->assertEquals($this->warehouse->sister_concern_id, $actor->sister_concern_id);
            $this->assertSame($this->customer->id, $order->customer_id);
            $this->assertEquals(0, $order->status);
            $this->assertFalse(Voucher::where('sale_order_id', $order->id)->exists());
        }
    }

    public function test_package_availability_is_limited_by_components_and_has_no_inventory(): void
    {
        $this->assertSame(3, app(ProductPackageService::class)->availability($this->package));
        $this->assertCount(2, $this->package->package_info['components']);
        $this->expectException(ValidationException::class);
        Inventory::create(['warehouse_id' => $this->warehouse->id, 'sister_concern_id' => $this->warehouse->sister_concern_id, 'product_id' => $this->package->id, 'quantity' => 1, 'unit_price' => 0]);
    }

    public function test_nested_package_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        app(ProductPackageService::class)->replaceItems($this->package, [['product_id' => $this->package->id, 'quantity' => 1], ['product_id' => $this->a->id, 'quantity' => 1]]);
    }

    public function test_checkout_uses_server_price_and_retry_is_idempotent(): void
    {
        $service = app(PackageOrderService::class);
        $data = $this->data();
        $order = $service->checkout($this->customer, $data);
        $this->assertEquals(275.01, $order->saleOrder->total);
        $this->assertSame('225.01', $order->lines[0]['unit_price']);
        $this->assertSame($order->id, $service->checkout($this->customer, $data)->id);
        $this->assertSame(10, (int) Inventory::where('product_id', $this->a->id)->sum('quantity'));
    }

    public function test_confirmation_deducts_components_and_posts_balanced_accounting_once(): void
    {
        $service = app(PackageOrderService::class);
        $checkout = $service->checkout($this->customer, $this->data());
        $service->confirm($checkout, $this->operator);
        $this->assertSame(8, (int) Inventory::where('product_id', $this->a->id)->sum('quantity'));
        $this->assertSame(2, (int) Inventory::where('product_id', $this->b->id)->sum('quantity'));
        $this->assertSame('Confirmed', $checkout->fresh()->status);
        $items = SaleOrderItem::where('sale_order_id', $checkout->sale_order_id)->get();
        $this->assertSame(22501, (int) round($items->sum(fn ($i) => $i->selling_price * $i->quantity) * 100));
        $voucher = Voucher::where('sale_order_id', $checkout->sale_order_id)->firstOrFail();
        $this->assertSame(Transaction::where('voucher_id', $voucher->id)->where('transaction_type', 1)->sum('amount'), Transaction::where('voucher_id', $voucher->id)->where('transaction_type', 2)->sum('amount'));
        $service->confirm($checkout, $this->operator);
        $this->assertSame(1, Voucher::where('sale_order_id', $checkout->sale_order_id)->count());
    }

    public function test_package_edit_does_not_change_existing_order_snapshot(): void
    {
        $service = app(PackageOrderService::class);
        $checkout = $service->checkout($this->customer, $this->data());
        $this->package->update(['selling_price' => '999.00']);
        app(ProductPackageService::class)->replaceItems($this->package, [['product_id' => $this->a->id, 'quantity' => 1], ['product_id' => $this->b->id, 'quantity' => 2]]);
        $service->confirm($checkout, $this->operator);
        $this->assertSame('225.01', $checkout->fresh()->lines[0]['unit_price']);
        $this->assertSame(8, (int) Inventory::where('product_id', $this->a->id)->sum('quantity'));
    }

    public function test_stock_changed_after_checkout_rolls_back_without_posting(): void
    {
        $service = app(PackageOrderService::class);
        $checkout = $service->checkout($this->customer, $this->data());
        Inventory::where('product_id', $this->b->id)->update(['quantity' => 0]);
        try {
            $service->confirm($checkout, $this->operator);
            $this->fail('Expected shortage');
        } catch (ValidationException) {
            $this->assertSame(10, (int) Inventory::where('product_id', $this->a->id)->sum('quantity'));
            $this->assertSame(0, Voucher::where('sale_order_id', $checkout->sale_order_id)->count());
            $this->assertSame('Pending', $checkout->fresh()->status);
        }
    }

    public function test_api_creates_package_order_and_returns_customer_snapshot(): void
    {
        $this->actingAs($this->customer, 'customer');
        $data = $this->data();
        $response = $this->postJson('/api/package-checkout', $data)->assertCreated()->assertJsonPath('order.total', 275.01);
        $this->getJson('/api/order/'.$response->json('order_no'))->assertOk()->assertJsonPath('products.0.unit_price', '225.01');
    }

    public function test_cart_package_quantity_and_price_are_server_controlled(): void
    {
        $this->actingAs($this->customer, 'customer');
        $service = app(CartService::class);
        $service->add($this->package->id, 1, 9999);
        $item = $service->all()->first();
        $this->assertSame(1, (int) $item->quantity);
        $this->assertEquals(0, $item->discount);
        $this->assertEquals(225.01, $item->price);
    }

    public function test_mixed_cart_checks_shared_component_stock_as_one_demand(): void
    {
        $data = $this->data();
        $data['items'][] = ['product_id' => $this->a->id, 'quantity' => 9];
        $this->expectException(ValidationException::class);
        app(PackageOrderService::class)->checkout($this->customer, $data);
    }

    public function test_other_customer_cannot_read_order_or_receipt(): void
    {
        $checkout = app(PackageOrderService::class)->checkout($this->customer, $this->data());
        $other = Supplier::create(['name' => 'Unrelated package customer', 'type' => 'Customer', 'status' => 1]);
        $this->actingAs($other, 'customer');
        $this->getJson('/api/order/'.$checkout->saleOrder->order_no)->assertForbidden();
        $this->get('/api/order/'.$checkout->saleOrder->order_no.'/01712345678/receipt')->assertForbidden();
        $this->get('/api/order/'.$checkout->saleOrder->order_no.'/receipt')->assertForbidden();
    }

    public function test_customer_can_open_receipt_without_mobile_in_url_and_old_undefined_link(): void
    {
        $checkout = app(PackageOrderService::class)->checkout($this->customer, $this->data());
        $number = $checkout->saleOrder->order_no;
        $this->actingAs($this->customer, 'customer');
        $this->get('/api/order/'.$number.'/receipt')->assertOk()->assertSee($number)->assertSee('01712345678');
        $this->get('/api/order/'.$number.'/undefined/receipt')->assertOk()->assertSee($number);
    }

    public function test_package_create_controller_saves_components_and_published_product(): void
    {
        $request = Request::create('/', 'POST', ['name' => 'New bundle '.Str::random(8), 'category_id' => $this->a->category_id, 'unit_id' => $this->a->unit_id, 'package_warehouse_id' => $this->warehouse->id, 'selling_price' => '150.00', 'status' => 1, 'is_show' => 1, 'items' => [['product_id' => $this->a->id, 'quantity' => 1], ['product_id' => $this->b->id, 'quantity' => 1]]]);
        (new ProductPackageController)->store($request, app(ProductPackageService::class));
        $package = Product::where('name', $request->name)->firstOrFail();
        $this->assertTrue($package->isPackage());
        $this->assertCount(2, $package->packageItems);
    }

    public function test_package_admin_forms_and_order_details_render(): void
    {
        $products = new ProductPackageController;
        $this->assertStringContainsString('Products inside this package', $products->create()->with('errors', new ViewErrorBag)->render());
        $checkout = app(PackageOrderService::class)->checkout($this->customer, $this->data());
        $orders = new PackageOrderController;
        $this->assertStringContainsString($checkout->saleOrder->order_no, $orders->show($checkout, app(PackageOrderService::class))->with('errors', new ViewErrorBag)->render());
    }
    private function offer(Product $product, string $type = 'percentage', string $value = '20.00', array $changes = []): int
    {
        $id = \Illuminate\Support\Facades\DB::table('offer_campaigns')->insertGetId($changes + [
            'name' => 'Test offer', 'slug' => 'offer-'.Str::random(12), 'starts_at' => now()->subMinute(),
            'ends_at' => now()->addHour(), 'is_active' => true, 'created_by' => $this->operator->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        \Illuminate\Support\Facades\DB::table('offer_campaign_products')->insert([
            'campaign_id' => $id, 'product_id' => $product->id, 'discount_type' => $type,
            'discount_value' => $value, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        return $id;
    }

    public function test_special_offer_selects_best_campaign_general_or_member_price_without_stacking(): void
    {
        $this->a->update(['old_selling_price' => '100.00', 'selling_price' => '90.00', 'discount_percent' => '10', 'member_discount_percent' => '25']);
        $this->offer($this->a);
        $this->assertSame(8000, \App\Services\Membership\DiscountService::price($this->a, false)['net']);
        $this->assertSame(7500, \App\Services\Membership\DiscountService::price($this->a, true)['net']);
        $best = $this->offer($this->a, 'fixed', '30.00');
        $price = \App\Services\Membership\DiscountService::price($this->a, true);
        $this->assertSame(7000, $price['net']);
        $this->assertSame(0, $price['member_discount']);
        $this->assertSame(0, $price['general_discount']);
        $this->assertSame($best, $price['offer']['campaign_id']);
    }

    public function test_special_offer_time_boundaries_inactive_rows_and_expiry_reprice_cart(): void
    {
        $this->travelTo(now()->startOfSecond());
        $id = $this->offer($this->package, 'percentage', '20.00', ['starts_at' => now()->addMinute(), 'ends_at' => now()->addMinutes(2)]);
        $service = app(CartService::class);
        $service->add($this->package->id, 1);
        $this->assertSame('225.01', $service->all()->first()->price);
        $this->travel(1)->minutes();
        $this->assertSame('180.01', $service->all()->first()->price);
        $this->travel(1)->minutes();
        $this->assertSame('225.01', $service->all()->first()->price);
        $this->offer($this->package, 'fixed', '50.00', ['is_active' => false]);
        $this->assertNull(\App\Services\Membership\DiscountService::price($this->package, false)['offer']);
    }

    public function test_special_offers_api_filters_campaigns_and_includes_both_catalog_types(): void
    {
        $id = $this->offer($this->a);
        $other = $this->offer($this->package);
        $slug = \Illuminate\Support\Facades\DB::table('offer_campaigns')->where('id', $id)->value('slug');
        $items = $this->getJson('/api/special-offers')->assertOk()->json('products.data');
        $this->assertContains($this->a->id, array_column($items,'id'));
        $this->assertContains($this->package->id, array_column($items,'id'));
        $items = $this->getJson('/api/special-offers?campaign='.$slug)->assertOk()->json('products.data');
        $this->assertSame([$this->a->id], array_column($items,'id'));
        $this->assertArrayNotHasKey('purchase_price', $items[0]);
        $this->getJson('/api/get-product-details-data/'.$this->a->slug)->assertOk()->assertJsonPath('product.selling_price','80.00');
        \Illuminate\Support\Facades\DB::table('offer_campaign_products')->where('campaign_id',$id)->update(['is_active'=>false]);
        $items = $this->getJson('/api/special-offers?campaign='.$slug)->assertOk()->json('products.data');
        $this->assertSame([], $items);
    }

    public function test_special_offer_catalog_price_filters_and_sort_use_offer_net_price(): void
    {
        $this->offer($this->a, 'fixed', '75.00');
        $items = $this->getJson('/api/get-product-data?minPrice=24&maxPrice=26&sortBy=price_low_high&perPage=200')->assertOk()->json('products');
        $this->assertContains($this->a->id, array_column($items,'id'));
        foreach ($items as $item) $this->assertTrue((float)$item['selling_price'] >= 24 && (float)$item['selling_price'] <= 26);
    }

    public function test_special_offer_checkout_snapshot_is_immutable_and_expired_total_is_rejected_for_cod(): void
    {
        $id = $this->offer($this->package, 'fixed', '25.01');
        $data = $this->data();
        $data['expected_total'] = '250.00';
        $service = app(PackageOrderService::class);
        $order = $service->checkout($this->customer, $data);
        $this->assertSame('special_offer', $order->lines[0]['discount_source']);
        $this->assertSame($id, $order->lines[0]['special_offer']['campaign_id']);
        $this->assertSame('25.01', $order->lines[0]['offer_discount_total']);
        \Illuminate\Support\Facades\DB::table('offer_campaigns')->where('id',$id)->update(['ends_at'=>now()->subSecond()]);
        $this->assertSame('200.00', $order->fresh()->lines[0]['line_total']);
        $this->assertSame($order->id, $service->checkout($this->customer, $data)->id);
        $data['request_key'] = (string) Str::uuid();
        $this->expectException(ValidationException::class);
        $service->checkout($this->customer, $data);
    }

    public function test_special_offer_admin_validates_discounts_and_retains_removed_entry_ids(): void
    {
        $controller = app(\App\Http\Controllers\Ecommerce\OfferCampaignController::class);
        $data = ['name'=>'Offer admin test','slug'=>'offer-admin-'.Str::lower(Str::random(10)),
            'starts_at'=>now('Asia/Dhaka')->format('Y-m-d H:i:s'),'ends_at'=>now('Asia/Dhaka')->addHour()->format('Y-m-d H:i:s'),
            'is_active'=>1,'items'=>[['product_id'=>$this->a->id,'discount_type'=>'percentage','discount_value'=>'20','is_active'=>1],
                ['product_id'=>$this->package->id,'discount_type'=>'fixed','discount_value'=>'25','is_active'=>1]]];
        $controller->save(Request::create('/', 'POST', $data));
        $id = \Illuminate\Support\Facades\DB::table('offer_campaigns')->where('slug',$data['slug'])->value('id');
        $this->assertNotNull($id);
        $this->assertStringContainsString('Offer admin test', $controller->edit($id)->with('errors',new ViewErrorBag)->render());
        $this->assertStringContainsString(route('offer-campaigns.datatable'), $controller->index()->with('errors',new ViewErrorBag)->render());
        $entry = \Illuminate\Support\Facades\DB::table('offer_campaign_products')->where('campaign_id',$id)->where('product_id',$this->package->id)->first();
        array_pop($data['items']);
        $controller->save(Request::create('/', 'POST', $data),$id);
        $this->assertSame(0, (int) \Illuminate\Support\Facades\DB::table('offer_campaign_products')->where('id',$entry->id)->value('is_active'));
        $data['items'][0]['discount_value']='100';
        $this->expectException(ValidationException::class);
        $controller->save(Request::create('/', 'POST', $data),$id);
    }

    public function test_special_offer_membership_payment_and_refund_use_actual_discounted_total(): void
    {
        $account = $this->fundedSavings();
        $id = $this->offer($this->package, 'fixed', '25.01');
        $data = array_replace($this->data(), ['payment_method'=>'membership','expected_total'=>'250.00']);
        $service = app(PackageOrderService::class);
        $checkout = $service->checkout($this->customer, $data);
        $this->assertSame('750.00', $account->fresh()->principal_balance);
        $this->assertEquals(250, $checkout->saleOrder->paid);
        \Illuminate\Support\Facades\DB::table('offer_campaigns')->where('id',$id)->update(['is_active'=>false]);
        $service->cancel($checkout, $this->operator);
        $service->cancel($checkout->fresh(), $this->operator);
        $this->assertSame('1000.00', $account->fresh()->principal_balance);
    }

    public function test_special_offer_admin_pages_keep_ecommerce_sidebar(): void
    {
        $id = $this->offer($this->a);
        foreach ([route('offer-campaigns.index'), route('offer-campaigns.create'), route('offer-campaigns.edit', $id)] as $url) {
            $this->get($url)->assertOk()->assertSee('Ecommerce Management');
        }
    }

    private function tableResponse(string $route, array $columns, string $search = '', int $start = 0, int $length = 10)
    {
        $params = ['draw'=>3,'start'=>$start,'length'=>$length,'search'=>['value'=>$search],
            'columns'=>array_map(fn($c)=>['data'=>$c[0],'name'=>$c[1],'searchable'=>$c[2]?'true':'false','orderable'=>$c[2]?'true':'false','search'=>['value'=>'']],$columns),
            'order'=>[['column'=>0,'dir'=>'asc']]];
        return $this->getJson(route($route).'?'.http_build_query($params), ['X-Requested-With'=>'XMLHttpRequest'])->assertOk()->assertJsonPath('draw',3);
    }

    public function test_new_ecommerce_datatables_return_searchable_paginated_rows_without_unknown_columns(): void
    {
        $campaign = $this->offer($this->a);
        \Illuminate\Support\Facades\DB::table('offer_campaigns')->where('id',$campaign)->update(['name'=>'Unique campaign '.Str::random(12)]);
        $name = \Illuminate\Support\Facades\DB::table('offer_campaigns')->where('id',$campaign)->value('name');
        $columns = [['campaign','name',true],['starts_at','starts_at',true],['ends_at','ends_at',true],['campaign_status','campaign_status',false],['action','action',false]];
        $r = $this->tableResponse('offer-campaigns.datatable',$columns,$name)->assertJsonPath('recordsFiltered',1);
        $this->assertStringContainsString($name,$r->json('data.0.campaign'));
        foreach(['campaign','starts_at','ends_at','campaign_status','action','DT_RowIndex'] as $key) $this->assertArrayHasKey($key,$r->json('data.0'));
        $this->tableResponse('offer-campaigns.datatable',$columns,$name,1,1)->assertJsonCount(0,'data');

        $columns = [['package','name',true],['code','code',true],['components','components',false],['selling_price','selling_price',true],['source','source',false],['available','available',false],['package_status','package_status',false],['action','action',false]];
        $r = $this->tableResponse('product-packages.datatable',$columns,$this->package->code)->assertJsonPath('recordsFiltered',1);
        foreach(['package','code','components','selling_price','source','available','package_status','action','DT_RowIndex'] as $key) $this->assertArrayHasKey($key,$r->json('data.0'));
        $this->assertStringContainsString($this->package->name,$r->json('data.0.package'));

        $checkout=app(PackageOrderService::class)->checkout($this->customer,$this->data());
        $columns=[['order_no','orders.order_no',true],['customer_name','customers.name',true],['total','orders.total',true],['status','checkouts.status',true],['action','action',false]];
        $r=$this->tableResponse('package-orders.datatable',$columns,$checkout->saleOrder->order_no)->assertJsonPath('recordsFiltered',1);
        foreach(['order_no','customer_name','total','status','action','DT_RowIndex'] as $key) $this->assertArrayHasKey($key,$r->json('data.0'));
        $this->assertSame($checkout->saleOrder->order_no,$r->json('data.0.order_no'));
        $other=\App\Models\BranchManagement\SisterConcern::whereKeyNot($this->warehouse->sister_concern_id)->firstOrFail();
        session(['active_sister_concern_id'=>$other->id]);
        $this->tableResponse('package-orders.datatable',$columns,$checkout->saleOrder->order_no)->assertJsonPath('recordsFiltered',0);
    }

    public function test_campaign_banner_upload_retains_replaces_and_removes_file_and_shows_mrp(): void
    {
        $controller=app(\App\Http\Controllers\Ecommerce\OfferCampaignController::class);
        $data=['name'=>'Banner test','slug'=>'banner-test-'.Str::lower(Str::random(12)),
            'starts_at'=>now('Asia/Dhaka')->subMinute()->format('Y-m-d H:i:s'),'ends_at'=>now('Asia/Dhaka')->addHour()->format('Y-m-d H:i:s'),
            'is_active'=>1,'items'=>[['product_id'=>$this->a->id,'discount_type'=>'percentage','discount_value'=>'20','is_active'=>1]]];
        $paths=[];
        try {
            $request=Request::create('/', 'POST', $data, [], ['banner_image'=>\Illuminate\Http\UploadedFile::fake()->image('campaign.png',1600,500)]);
            $controller->save($request);
            $row=\Illuminate\Support\Facades\DB::table('offer_campaigns')->where('slug',$data['slug'])->first();
            $paths[]=$row->banner_image;
            $this->assertFileExists(public_path($row->banner_image));
            $this->assertStringEndsWith('.webp',$row->banner_image);
            $view=$controller->edit($row->id)->with('errors',new ViewErrorBag)->render();
            $this->assertStringContainsString('MRP (Tk)',$view);
            $this->assertStringContainsString('multipart/form-data',$view);
            $this->assertStringContainsString(asset($row->banner_image),$view);
            $banners=$this->getJson('/api/special-offer-banners')->assertOk()->json('banners');
            $this->assertContains($data['slug'],array_column($banners,'slug'));
            $controller->save(Request::create('/','POST',$data),$row->id);
            $this->assertSame($row->banner_image,\Illuminate\Support\Facades\DB::table('offer_campaigns')->where('id',$row->id)->value('banner_image'));
            $request=Request::create('/', 'POST', $data, [], ['banner_image'=>\Illuminate\Http\UploadedFile::fake()->image('new.png',1200,400)]);
            $controller->save($request,$row->id);
            $new=\Illuminate\Support\Facades\DB::table('offer_campaigns')->where('id',$row->id)->value('banner_image');
            $paths[]=$new;
            $this->assertNotSame($row->banner_image,$new);
            $this->assertFileDoesNotExist(public_path($row->banner_image));
            $this->assertFileExists(public_path($new));
            $controller->save(Request::create('/','POST',$data+['remove_banner'=>1]),$row->id);
            $this->assertNull(\Illuminate\Support\Facades\DB::table('offer_campaigns')->where('id',$row->id)->value('banner_image'));
            $this->assertFileDoesNotExist(public_path($new));
        } finally { foreach($paths as $path) \Illuminate\Support\Facades\File::delete(public_path($path)); }
    }

    public function test_home_campaign_banners_exclude_scheduled_expired_disabled_and_hidden_products(): void
    {
        $id=$this->offer($this->a);
        \Illuminate\Support\Facades\DB::table('offer_campaigns')->where('id',$id)->update(['banner_image'=>'uploads/offer-campaigns/test.webp']);
        $slug=\Illuminate\Support\Facades\DB::table('offer_campaigns')->where('id',$id)->value('slug');
        $this->assertContains($slug,array_column($this->getJson('/api/special-offer-banners')->assertOk()->json('banners'),'slug'));
        foreach([['is_active'=>0],['starts_at'=>now()->addHour()],['ends_at'=>now()],['banner_image'=>null]] as $change) {
            \Illuminate\Support\Facades\DB::table('offer_campaigns')->where('id',$id)->update($change);
            $this->assertNotContains($slug,array_column($this->getJson('/api/special-offer-banners')->assertOk()->json('banners'),'slug'));
            \Illuminate\Support\Facades\DB::table('offer_campaigns')->where('id',$id)->update(['is_active'=>1,'starts_at'=>now()->subMinute(),'ends_at'=>now()->addHour(),'banner_image'=>'uploads/offer-campaigns/test.webp']);
        }
        $this->a->update(['is_show'=>0]);
        $this->assertNotContains($slug,array_column($this->getJson('/api/special-offer-banners')->assertOk()->json('banners'),'slug'));
    }

    public function test_banner_upload_rejects_non_image_and_cleans_up_when_campaign_validation_fails(): void
    {
        $controller=app(\App\Http\Controllers\Ecommerce\OfferCampaignController::class);
        $data=['name'=>'Invalid banner test','slug'=>'banner-invalid-'.Str::lower(Str::random(12)),
            'starts_at'=>now('Asia/Dhaka')->format('Y-m-d H:i:s'),'ends_at'=>now('Asia/Dhaka')->addHour()->format('Y-m-d H:i:s'),
            'is_active'=>1,'items'=>[['product_id'=>$this->a->id,'discount_type'=>'percentage','discount_value'=>'100','is_active'=>1]]];
        $directory=public_path('uploads/offer-campaigns');
        $before=glob($directory.'/*.webp') ?: [];
        foreach([\Illuminate\Http\UploadedFile::fake()->create('not-image.txt',1,'text/plain'),\Illuminate\Http\UploadedFile::fake()->image('valid.png',100,50)] as $file) {
            try { $controller->save(Request::create('/','POST',$data,[],['banner_image'=>$file])); $this->fail('Invalid campaign must fail.'); }
            catch(ValidationException) { $this->assertDatabaseMissing('offer_campaigns',['slug'=>$data['slug']]); }
        }
        $after=glob($directory.'/*.webp') ?: [];
        sort($before);sort($after);
        $this->assertSame($before,$after);
    }

}
