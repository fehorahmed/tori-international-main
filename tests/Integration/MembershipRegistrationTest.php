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

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        $this->input = ['name' => 'Registration Rollback Test', 'mobile_no' => '019'.random_int(10000000, 99999999),
            'email' => null, 'branch_id' => SisterConcern::where('status', 1)->firstOrFail()->id,
            'password' => 'Website-password-42', 'password_confirmation' => 'Website-password-42',
            'terms_accepted' => true, 'device_id' => 'registration-test-device', 'device_name' => 'TORI test', 'platform' => 'android'];
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
        $this->assertFalse($customer->shomriddhiAccount()->exists());
        $this->assertFalse($customer->cards()->exists());
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
