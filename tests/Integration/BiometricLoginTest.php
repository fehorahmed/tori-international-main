<?php
namespace Tests\Integration;

use App\Models\Supplier;
use App\Models\CustomerDevice;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BiometricLoginTest extends TestCase
{
    use DatabaseTransactions;
    private Supplier $member;
    private string $proof;
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        $this->member = Supplier::create(['name' => 'Biometric fixture', 'mobile_no' => '019'.random_int(10000000,99999999), 'type' => 'Customer', 'status' => 1, 'pin' => Hash::make('123456')]);
        $this->proof = bin2hex(random_bytes(32));
        CustomerDevice::create(['customer_id' => $this->member->id, 'device_id' => 'biometric-test-device', 'platform' => 'android', 'trusted_token_hash' => hash('sha256', $this->proof)]);
    }
    private function enable(): string
    {
        Sanctum::actingAs($this->member);
        return $this->postJson('/api/membership/v1/auth/toggle-biometric', ['enable' => true, 'device_id' => 'biometric-test-device', 'device_token' => $this->proof, 'pin' => '123456'])->assertOk()->json('data.biometric_token');
    }
    private function login(string $credential, array $extra = [])
    {
        return $this->postJson('/api/membership/v1/auth/login-biometric', $extra + ['device_id' => 'biometric-test-device', 'mobile_no' => $this->member->mobile_no, 'biometric_token' => $credential]);
    }
    public function test_enabled_device_can_login_and_credential_is_hashed_and_hidden(): void
    {
        $token = $this->enable();
        $device = CustomerDevice::where('customer_id', $this->member->id)->firstOrFail();
        $this->assertTrue(Hash::check($token, $device->biometric_token_hash));
        $this->assertArrayNotHasKey('biometric_token_hash', $device->toArray());
        $this->login($token)->assertOk()->assertJsonPath('success', true)->assertJsonStructure(['data' => ['token', 'customer']]);
    }
    public function test_wrong_device_wrong_token_inactive_account_and_disabled_device_are_rejected(): void
    {
        $token = $this->enable();
        $this->login($token, ['device_id' => 'other-device'])->assertUnauthorized();
        $this->login(str_repeat('x',64))->assertUnauthorized();
        $this->member->update(['status' => 0]);
        $this->login($token)->assertUnauthorized();
        $this->member->update(['status' => 1]);
        $this->postJson('/api/membership/v1/auth/toggle-biometric', ['enable' => false, 'device_id' => 'biometric-test-device'])->assertOk();
        $this->login($token)->assertUnauthorized();
    }
    public function test_enabling_requires_pin_and_trusted_device(): void
    {
        Sanctum::actingAs($this->member);
        $data = ['enable' => true, 'device_id' => 'biometric-test-device', 'device_token' => $this->proof];
        $this->postJson('/api/membership/v1/auth/toggle-biometric', $data)->assertUnprocessable()->assertJsonValidationErrors('pin');
        $this->postJson('/api/membership/v1/auth/toggle-biometric', $data + ['pin' => '654321'])->assertUnprocessable();
        $data['device_token'] = 'wrong-proof';
        $this->postJson('/api/membership/v1/auth/toggle-biometric', $data + ['pin' => '123456'])->assertForbidden();
    }
    public function test_pin_reset_revokes_biometric_credentials(): void
    {
        $token = $this->enable();
        $verification = bin2hex(random_bytes(32));
        \Illuminate\Support\Facades\Cache::put('verified_token_'.$verification,
            ['customer_id' => $this->member->id, 'device_id' => 'biometric-test-device'], 60);
        $this->postJson('/api/membership/v1/auth/set-pin', ['verification_token' => $verification,
            'device_id' => 'biometric-test-device', 'pin' => '234567', 'pin_confirmation' => '234567'])->assertOk();
        $this->login($token)->assertUnauthorized();
        $this->assertNull(CustomerDevice::where('customer_id', $this->member->id)->firstOrFail()->biometric_token_hash);
    }

    public function test_reenabling_rotates_the_device_credential(): void
    {
        $old = $this->enable();
        $new = $this->enable();
        $this->assertNotSame($old, $new);
        $this->login($old)->assertUnauthorized();
        $this->login($new)->assertOk();
    }
}
