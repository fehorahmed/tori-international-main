<?php

namespace Tests\Integration;

use App\Http\Controllers\CardManagement\TransactionSettingController;
use App\Models\MembershipTransactionSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class MembershipTransactionSettingsTest extends TestCase
{
    use DatabaseTransactions;

    private function registrationIncomeHead(): int
    {
        return \App\Models\Account\AccountHead::with('accountGroup')->where('nature', 'Credit')->whereNull('sister_concern_id')->where('status', 1)->get()->first(fn ($head) => $head->accountGroup?->top_reserved_id == 4)->id;
    }

    public function test_membership_settings_can_be_saved_through_the_post_route(): void
    {
        $admin = User::firstOrFail();
        $admin->forceFill(['role' => 'Admin'])->save();

        $this->actingAs($admin)->post('/admin/card-management/transaction-settings/membership', [
            'income_head_id' => $this->registrationIncomeHead(), 'registration_fee' => '150.50',
            'account_hold_amount' => '1000.00',
        ])->assertRedirect(route('card-management.transaction-settings.index').'#membership');

        $this->assertSame('150.50', membership_transaction_setting('membership.registration_fee'));
        $this->assertSame('1000.00', membership_transaction_setting('membership.account_hold_amount'));
    }

    public function test_membership_amounts_save_as_decimal_strings_without_changing_transaction_settings(): void
    {
        $admin = User::firstOrFail();
        $admin->forceFill(['role' => 'Admin'])->save();
        $this->actingAs($admin);
        $cashout = (array) membership_transaction_settings('cashout');
        $transfer = (array) membership_transaction_settings('transfer');
        $controller = new TransactionSettingController;
        $controller->update(Request::create('/', 'POST', ['income_head_id' => $this->registrationIncomeHead(), 'registration_fee' => '150.5', 'account_hold_amount' => '1000', 'enabled' => 1]), 'membership');
        $this->assertSame('150.50', membership_transaction_setting('membership.registration_fee'));
        $this->assertSame('1000.00', membership_transaction_settings('membership')->account_hold_amount);
        $this->assertSame($cashout, (array) membership_transaction_settings('cashout'));
        $this->assertSame($transfer, (array) membership_transaction_settings('transfer'));
        $this->assertStringContainsString('Membership Registration Fee (Tk)', $controller->index()->with('errors', new ViewErrorBag)->render());
        try {
            $controller->update(Request::create('/', 'POST', ['registration_fee' => '-1', 'account_hold_amount' => '1000.001']), 'membership');
            $this->fail('Invalid amounts must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('registration_fee', $exception->errors());
            $this->assertArrayHasKey('account_hold_amount', $exception->errors());
            $this->assertSame('150.50', membership_transaction_setting('membership.registration_fee'));
        }
    }

    public function test_key_helper_returns_typed_values_and_group_reads_the_same_store_without_stale_cache(): void
    {
        MembershipTransactionSetting::writeGroup('cashout', ['enabled' => false, 'minimum_amount' => '1.00', 'maximum_amount' => '1234.50', 'hold_minutes' => 17, 'income_head_id' => null]);
        $this->assertFalse(membership_transaction_setting('cashout.enabled'));
        $this->assertSame('1234.50', membership_transaction_setting('cashout.maximum_amount'));
        $this->assertSame(17, membership_transaction_setting('cashout.hold_minutes'));
        $this->assertNull(membership_transaction_setting('cashout.income_head_id'));
        $this->assertSame('fallback', membership_transaction_setting('unknown.key', 'fallback'));
        $group = membership_transaction_settings('cashout');
        $this->assertSame('1234.50', $group->maximum_amount);
        $this->assertFalse($group->enabled);
        MembershipTransactionSetting::writeGroup('cashout', ['minimum_amount' => '1.00', 'maximum_amount' => '0.00', 'enabled' => true]);
        $this->assertSame('0.00', membership_transaction_setting('cashout.maximum_amount'));
        $this->assertTrue(membership_transaction_settings('cashout')->enabled);
    }

    public function test_unknown_keys_rollback_the_whole_group_write(): void
    {
        $before = membership_transaction_setting('cashout.maximum_amount');
        try {
            MembershipTransactionSetting::writeGroup('cashout', ['minimum_amount' => '1.00', 'maximum_amount' => '9000.00', 'unknown_field' => true]);
            $this->fail('Unknown keys must not be stored.');
        } catch (InvalidArgumentException) {
            $this->assertSame($before, membership_transaction_setting('cashout.maximum_amount'));
        }
    }

    public function test_saving_cashout_does_not_modify_transfer_or_accept_posting_actor_from_request(): void
    {
        $admin = User::firstOrFail();
        $admin->forceFill(['role' => 'Admin'])->save();
        $this->actingAs($admin);
        MembershipTransactionSetting::writeGroup('transfer', ['minimum_amount' => '1.00', 'maximum_amount' => '7777.00', 'posting_user_id' => $admin->id]);
        $controller = new TransactionSettingController;
        $controller->update(Request::create('/', 'POST', ['enabled' => 1, 'minimum_amount' => '1.00', 'maximum_amount' => '2000.00', 'charge_type' => 'fixed', 'charge_rate' => '0.00', 'hold_minutes' => 15, 'posting_user_id' => 999999]), 'cashout');
        $this->assertSame('2000.00', membership_transaction_setting('cashout.maximum_amount'));
        $this->assertSame('7777.00', membership_transaction_setting('transfer.maximum_amount'));
        $this->assertSame($admin->id, membership_transaction_setting('transfer.posting_user_id'));
        $this->assertFalse(membership_transaction_setting('cashin.enabled'));
        $this->assertSame('card_management.transaction_settings.index', $controller->index()->name());
        $this->assertStringContainsString('Not available yet', $controller->index()->with('errors', new ViewErrorBag)->render());
    }

    public function test_non_admin_cannot_change_global_settings(): void
    {
        $user = User::firstOrFail();
        $user->forceFill(['role' => 'Staff'])->save();
        $this->actingAs($user);
        $this->expectException(HttpException::class);
        (new TransactionSettingController)->update(Request::create('/', 'POST'), 'cashout');
    }

    public function test_invalid_percentage_is_rejected_before_saving(): void
    {
        $user = User::firstOrFail();
        $user->forceFill(['role' => 'Admin'])->save();
        $this->actingAs($user);
        $this->expectException(ValidationException::class);
        (new TransactionSettingController)->update(Request::create('/', 'POST', ['enabled' => 1, 'minimum_amount' => '1.00', 'maximum_amount' => '1000.00', 'charge_type' => 'percentage', 'charge_rate' => '101.00']), 'transfer');
    }

    public function test_minimum_cannot_exceed_maximum(): void
    {
        $admin = User::firstOrFail();
        $admin->forceFill(['role' => 'Admin'])->save();
        $this->actingAs($admin);
        $this->expectException(ValidationException::class);
        (new TransactionSettingController)->update(Request::create('/', 'POST', ['enabled' => 1, 'minimum_amount' => '100.01', 'maximum_amount' => '100.00', 'charge_type' => 'fixed', 'charge_rate' => '0.00']), 'cashout');
    }
}
