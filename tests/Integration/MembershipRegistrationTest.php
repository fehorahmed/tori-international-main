<?php

namespace Tests\Integration;

use App\Models\BranchManagement\SisterConcern;
use App\Models\Supplier;
use App\Services\SmsManagement\SmsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MembershipRegistrationTest extends TestCase
{
    use DatabaseTransactions;
    private array $input;

    public function test_member_and_agent_selection_resolves_same_code_in_different_tables(): void
    {
        $code = 'RF-'.bin2hex(random_bytes(6));
        $member = Supplier::create(['name' => 'Referral member', 'type' => 'Customer', 'status' => 1, 'member_no' => $code]);
        $agent = \App\Models\User::firstOrFail();
        $agent->forceFill(['username' => $code, 'status' => 1])->save();
        foreach (['member' => $member->id, 'agent' => $agent->id] as $type => $id) {
            $this->input['mobile_no'] = '019'.random_int(10000000, 99999999);
            $this->input['referral_type'] = $type;
            $this->input['referral_code'] = ' '.$code.' ';
            $data = $this->verified();
            $response = $this->postJson('/api/membership/v1/auth/register/complete', $data)->assertCreated();
            $customer = $response->json('data.customer.id');
            $this->assertDatabaseHas('membership_referrals', ['customer_id' => $customer, 'referrer_type' => $type, 'referrer_id' => $id, 'referral_code' => $code]);
            $this->postJson('/api/membership/v1/auth/register/complete', $data)->assertStatus(409);
            $this->assertSame(1, DB::table('membership_referrals')->where('customer_id', $customer)->count());
        }
    }

    public function test_referral_type_invalid_code_inactive_and_duplicate_members_are_rejected(): void
    {
        $code = 'RF-'.bin2hex(random_bytes(6));
        $member = Supplier::create(['name' => 'Referral member', 'type' => 'Customer', 'status' => 1, 'member_no' => $code]);
        $url = '/api/membership/v1/auth/register/initiate';
        $this->postJson($url, $this->input + ['referral_code' => $code])->assertUnprocessable()->assertJsonValidationErrors('referral_type');
        $this->postJson($url, $this->input + ['referral_type' => 'agent', 'referral_code' => $code])->assertUnprocessable()->assertJsonValidationErrors('referral_code');
        $member->update(['status' => 0]);
        $this->postJson($url, $this->input + ['referral_type' => 'member', 'referral_code' => $code])->assertUnprocessable();
        $member->update(['status' => 1]);
        Supplier::create(['name' => 'Ambiguous referrer', 'type' => 'Customer', 'status' => 1, 'member_no' => $code]);
        $this->postJson($url, $this->input + ['referral_type' => 'member', 'referral_code' => $code])->assertUnprocessable()->assertJsonValidationErrors('referral_code');
        $this->assertDatabaseMissing('membership_registration_requests', ['mobile_no' => $this->input['mobile_no']]);
    }

    public function test_referrer_is_rechecked_before_registration_completes(): void
    {
        $member = Supplier::create(['name' => 'Referral member', 'type' => 'Customer', 'status' => 1, 'member_no' => 'RF-'.bin2hex(random_bytes(6))]);
        $this->input += ['referral_type' => 'member', 'referral_code' => $member->member_no];
        $data = $this->verified();
        $member->update(['status' => 0]);
        $this->postJson('/api/membership/v1/auth/register/complete', $data)->assertUnprocessable()->assertJsonValidationErrors('referral_code');
        $this->assertDatabaseMissing('suppliers', ['mobile_no' => $this->input['mobile_no']]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        $this->input = ['name' => 'Registration Rollback Test', 'mobile_no' => '019'.random_int(10000000, 99999999),
            'email' => null, 'branch_id' => SisterConcern::where('status', 1)->firstOrFail()->id,
            'password' => 'Website-password-42', 'password_confirmation' => 'Website-password-42',
            'terms_accepted' => true, 'device_id' => 'registration-test-device', 'device_name' => 'TORI test', 'platform' => 'android'];
    }

    public function test_savings_provisioning_never_reopens_closed_accounts_or_creates_duplicates(): void
    {
        $response = $this->postJson('/api/membership/v1/auth/register/complete', $this->verified())->assertCreated();
        $customer = Supplier::findOrFail($response->json('data.customer.id'));
        $account = $customer->shomriddhiAccount;
        $account->update(['status' => 'Closed']);
        try {
            \App\Services\Membership\RegistrationSavingsService::ensure($customer);
            $this->fail('A closed account must require manual branch review.');
        } catch (\Illuminate\Validation\ValidationException) {
            $this->assertSame('Closed', $account->fresh()->status);
            $this->assertSame(1, $customer->shomriddhiAccount()->count());
        }
    }

    private function registrationSession(): array
    {
        $token = $this->postJson('/api/membership/v1/auth/register/initiate', $this->input)->assertOk()->json('data.registration_token');
        DB::table('membership_registration_requests')->where('mobile_no', $this->input['mobile_no'])->update(['otp_hash' => Hash::make('123456')]);
        return ['registration_token' => $token, 'device_id' => $this->input['device_id']];
    }

    private function verified(): array
    {
        $session = $this->registrationSession();
        $verification = $this->postJson('/api/membership/v1/auth/register/verify-otp', $session + ['otp' => '123456'])
            ->assertOk()->json('data.verification_token');
        return $session + ['verification_token' => $verification, 'pin' => '067891', 'pin_confirmation' => '067891'];
    }

    public function test_registration_verifies_phone_before_creating_customer_and_supports_website_password_and_trusted_pin(): void
    {
        $data = $this->verified();
        $this->assertDatabaseMissing('suppliers', ['mobile_no' => $this->input['mobile_no']]);
        $draft = DB::table('membership_registration_requests')->where('mobile_no', $this->input['mobile_no'])->first();
        $this->assertTrue(Hash::check($this->input['password'], $draft->website_password));
        $response = $this->postJson('/api/membership/v1/auth/register/complete', $data)->assertCreated()
            ->assertJsonPath('success', true)->assertJsonMissingPath('data.customer.password')->assertJsonMissingPath('data.customer.pin');
        $customer = Supplier::findOrFail($response->json('data.customer.id'));
        $this->assertTrue(Hash::check($this->input['password'], $customer->password));
        $this->assertTrue(Hash::check('067891', $customer->pin));
        $this->assertNotEmpty($customer->member_no);
        $this->assertTrue($customer->shomriddhiAccount()->exists());
        $account = $customer->shomriddhiAccount;
        $this->assertSame('0.00', $account->principal_balance);
        $this->assertSame('Active', $account->status);
        $this->assertNotEmpty($account->account_number);
        $this->assertSame(1, \App\Models\Account\AccountHead::where('supplier_id', $customer->id)->where('head_type', 'savings')->where('nature', 'Credit')->where('status', 1)->count());
        $this->assertSame($account->id, \App\Services\Membership\RegistrationSavingsService::ensure($customer)->id);
        $this->assertSame(1, $customer->shomriddhiAccount()->count());
        $this->assertFalse($customer->cards()->exists());
        $this->assertDatabaseMissing('membership_referrals', ['customer_id' => $customer->id]);
        $this->assertDatabaseHas('membership_registration_requests', ['customer_id' => $customer->id, 'website_password' => '', 'verification_hash' => null]);
        $this->postJson('/api/membership/v1/auth/initiate', ['mobile_no' => $customer->mobile_no,
            'device_id' => $this->input['device_id'], 'device_token' => $response->json('data.device_token')])
            ->assertOk()->assertJsonPath('data.otp_required', false);
        $this->postJson('/api/login', ['login_id' => $customer->mobile_no, 'password' => $this->input['password']])->assertOk();
        $this->postJson('/api/membership/v1/auth/register/complete', $data)->assertConflict();
        $this->assertSame(1, Supplier::where('mobile_no', $customer->mobile_no)->count());
    }

    public function test_existing_normalized_mobile_is_rejected_without_changing_account(): void
    {
        $customer = Supplier::where('type', 'Customer')->firstOrFail();
        $customer->update(['mobile_no' => '+88'.$this->input['mobile_no']]);
        $this->postJson('/api/membership/v1/auth/register/initiate', $this->input)->assertUnprocessable()->assertJsonValidationErrors('mobile_no');
        $this->assertDatabaseMissing('membership_registration_requests', ['mobile_no' => $this->input['mobile_no']]);
    }

    public function test_password_confirmation_consent_and_active_branch_are_required(): void
    {
        $this->postJson('/api/membership/v1/auth/register/initiate', array_replace($this->input, ['password_confirmation' => 'wrong', 'terms_accepted' => false]))
            ->assertUnprocessable()->assertJsonValidationErrors(['password', 'terms_accepted']);
        SisterConcern::whereKey($this->input['branch_id'])->update(['status' => 0]);
        $this->postJson('/api/membership/v1/auth/register/initiate', $this->input)->assertUnprocessable();
    }

    public function test_wrong_device_and_unverified_completion_cannot_create_account(): void
    {
        $session = $this->registrationSession();
        $this->postJson('/api/membership/v1/auth/register/verify-otp', array_replace($session, ['device_id' => 'other-device', 'otp' => '123456']))->assertUnauthorized();
        $this->postJson('/api/membership/v1/auth/register/complete', $session + ['verification_token' => str_repeat('a', 64), 'pin' => '123456', 'pin_confirmation' => '123456'])->assertUnauthorized();
        $this->assertDatabaseMissing('suppliers', ['mobile_no' => $this->input['mobile_no']]);
    }

    public function test_wrong_otp_attempts_are_persisted_and_resend_cooldown_is_enforced(): void
    {
        $session = $this->registrationSession();
        $this->postJson('/api/membership/v1/auth/register/resend', $session)->assertStatus(429);
        for ($i = 0; $i < 5; $i++) $this->postJson('/api/membership/v1/auth/register/verify-otp', $session + ['otp' => '000000'])->assertUnprocessable();
        $this->postJson('/api/membership/v1/auth/register/verify-otp', $session + ['otp' => '123456'])->assertStatus(429);
        $this->assertDatabaseHas('membership_registration_requests', ['mobile_no' => $this->input['mobile_no'], 'otp_attempts' => 5]);
    }

    public function test_verified_registration_cannot_complete_after_branch_is_disabled_or_proof_expires(): void
    {
        $data = $this->verified();
        DB::table('membership_registration_requests')->where('mobile_no', $this->input['mobile_no'])->update(['verified_until' => now()->subMinute()]);
        $this->postJson('/api/membership/v1/auth/register/complete', $data)->assertUnauthorized();
        DB::table('membership_registration_requests')->where('mobile_no', $this->input['mobile_no'])->update(['verified_until' => now()->addMinutes(5)]);
        SisterConcern::whereKey($this->input['branch_id'])->update(['status' => 0]);
        $this->postJson('/api/membership/v1/auth/register/complete', $data)->assertUnprocessable();
        $this->assertDatabaseMissing('suppliers', ['mobile_no' => $this->input['mobile_no']]);
    }

    public function test_production_sms_failure_does_not_report_success_or_keep_registration_draft(): void
    {
        $this->app->instance('env', 'production');
        $this->mock(SmsService::class)->shouldReceive('sendSms')->once()->andReturn(false);
        $this->postJson('/api/membership/v1/auth/register/initiate', $this->input)->assertStatus(503);
        $this->assertDatabaseMissing('membership_registration_requests', ['mobile_no' => $this->input['mobile_no']]);
    }

    public function test_transactional_sms_uses_configured_gateway_and_redacts_otp_from_logs(): void
    {
        \App\Models\SmsManagement\SmsGateway::query()->update(['is_active' => false]);
        \App\Models\SmsManagement\SmsGateway::create(['provider_name' => 'SSL Wireless', 'api_url' => 'https://sms.example.test/send', 'api_key' => 'test-key', 'sender_id' => 'TORI', 'is_active' => true, 'status' => true]);
        Http::preventStrayRequests();
        Http::fake(['https://sms.example.test/send' => Http::response(['status' => 'SUCCESS'], 200)]);
        $this->assertTrue(app(SmsService::class)->sendSms($this->input['mobile_no'], 'OTP 673920'));
        Http::assertSent(fn ($request) => $request['sms'] === 'OTP 673920' && $request['msisdn'] === $this->input['mobile_no']);
        $log = \App\Models\SmsManagement\SmsLog::where('mobile_no', $this->input['mobile_no'])->latest('id')->firstOrFail();
        $this->assertSame('Sent', $log->status);
        $this->assertStringNotContainsString('673920', $log->message);
    }
}
