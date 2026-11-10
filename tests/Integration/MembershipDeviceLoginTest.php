<?php

namespace Tests\Integration;

use App\Models\CustomerDevice;
use App\Models\CustomerOtpLog;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MembershipDeviceLoginTest extends TestCase
{
    use DatabaseTransactions;

    private Supplier $customer;

    private string $mobile = '01900000311';

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        $this->customer = Supplier::where('type', '!=', 'Supplier')->where('status', 1)->firstOrFail();
        $this->customer->update(['mobile_no' => $this->mobile, 'pin' => Hash::make('763921')]);
        CustomerDevice::where('customer_id', $this->customer->id)->delete();
    }

    private function otp(string $device, bool $reset = false): string
    {
        $this->postJson('/api/membership/v1/auth/initiate', ['mobile_no' => $this->mobile, 'device_id' => $device, 'reset_pin' => $reset])
            ->assertOk()->assertJsonPath('data.otp_required', true);
        // Avoid depending on a random OTP or sending a real SMS in testing.
        Cache::put('customer_otp_'.$this->mobile.':'.hash('sha256', $device), [
            'otp' => Hash::make('123456'), 'customer_id' => $this->customer->id,
            'device_id' => $device, 'reset_pin' => $reset, 'attempts' => 0,
        ], 180);

        return $this->postJson('/api/membership/v1/auth/verify-otp', [
            'mobile_no' => $this->mobile, 'device_id' => $device, 'otp' => '123456',
        ])->assertOk()->json('data.verification_token');
    }

    public function test_new_device_needs_otp_but_verified_device_uses_phone_and_pin(): void
    {
        $this->postJson('/api/membership/v1/auth/login-pin', [
            'mobile_no' => $this->mobile, 'device_id' => 'phone-a', 'pin' => '763921',
        ])->assertUnauthorized()->assertJsonPath('otp_required', true);
        $verification = $this->otp('phone-a');
        $this->postJson('/api/membership/v1/auth/login-pin', [
            'mobile_no' => $this->mobile, 'device_id' => 'phone-a', 'pin' => '000000',
            'verification_token' => $verification,
        ])->assertUnauthorized();
        $this->assertSame(0, CustomerDevice::where('customer_id', $this->customer->id)->count());
        $login = $this->postJson('/api/membership/v1/auth/login-pin', [
            'mobile_no' => $this->mobile, 'device_id' => 'phone-a', 'pin' => '763921',
            'verification_token' => $verification,
        ])->assertOk();
        $secret = $login->json('data.device_token');
        $this->assertNotEmpty($secret);
        $this->assertSame(hash('sha256', $secret), CustomerDevice::where('customer_id', $this->customer->id)->firstOrFail()->trusted_token_hash);
        $body = ['mobile_no' => $this->mobile, 'device_id' => 'phone-a', 'device_token' => $secret];
        $logs = CustomerOtpLog::count();
        $this->postJson('/api/membership/v1/auth/initiate', $body)->assertOk()->assertJsonPath('data.otp_required', false);
        $this->assertSame($logs, CustomerOtpLog::count());
        $this->postJson('/api/membership/v1/auth/login-pin', $body + ['pin' => '000000'])->assertUnauthorized();
        $this->postJson('/api/membership/v1/auth/login-pin', $body + ['pin' => '763921'])->assertOk()->assertJsonPath('success', true);
        $body['device_id'] = 'phone-b';
        $this->postJson('/api/membership/v1/auth/initiate', $body)->assertOk()->assertJsonPath('data.otp_required', true);
        $this->postJson('/api/membership/v1/auth/login-pin', $body + ['pin' => '763921'])->assertUnauthorized();
    }

    public function test_otp_verification_is_bound_to_the_device_and_phone(): void
    {
        $verification = $this->otp('phone-a');
        $body = ['mobile_no' => $this->mobile, 'device_id' => 'phone-b', 'pin' => '763921', 'verification_token' => $verification];
        $this->postJson('/api/membership/v1/auth/login-pin', $body)->assertUnauthorized();
        $body['device_id'] = 'phone-a';
        $body['mobile_no'] = '01900000999';
        $this->postJson('/api/membership/v1/auth/login-pin', $body)->assertUnauthorized();
        $this->assertSame(0, CustomerDevice::where('customer_id', $this->customer->id)->count());
    }

    public function test_pin_reset_requires_otp_and_remembers_the_device(): void
    {
        $verification = $this->otp('phone-a', true);
        $reset = $this->postJson('/api/membership/v1/auth/set-pin', [
            'verification_token' => $verification, 'device_id' => 'phone-a',
            'pin' => '763922', 'pin_confirmation' => '763922',
        ])->assertOk();
        $this->assertTrue(Hash::check('763922', $this->customer->fresh()->pin));
        $this->postJson('/api/membership/v1/auth/initiate', [
            'mobile_no' => $this->mobile, 'device_id' => 'phone-a', 'device_token' => $reset->json('data.device_token'),
        ])->assertOk()->assertJsonPath('data.otp_required', false);
    }

    public function test_device_id_without_the_correct_secret_does_not_skip_otp(): void
    {
        CustomerDevice::create(['customer_id' => $this->customer->id, 'device_id' => 'known-device',
            'trusted_token_hash' => hash('sha256', 'correct-secret')]);
        foreach ([null, 'wrong-secret'] as $secret) {
            $this->postJson('/api/membership/v1/auth/initiate', [
                'mobile_no' => $this->mobile, 'device_id' => 'known-device', 'device_token' => $secret,
            ])->assertOk()->assertJsonPath('data.otp_required', true);
            $this->postJson('/api/membership/v1/auth/login-pin', [
                'mobile_no' => $this->mobile, 'device_id' => 'known-device', 'device_token' => $secret, 'pin' => '763921',
            ])->assertUnauthorized();
        }
    }
}
