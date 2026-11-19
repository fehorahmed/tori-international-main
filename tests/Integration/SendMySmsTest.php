<?php

namespace Tests\Integration;

use App\Http\Controllers\SmsManagement\SmsGatewayController;
use App\Models\SmsManagement\SmsGateway;
use App\Models\SmsManagement\SmsLog;
use App\Models\Supplier;
use App\Models\User;
use App\Services\SmsManagement\Gateways\SendMySmsGateway;
use App\Services\SmsManagement\SmsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class SendMySmsTest extends TestCase
{
    use DatabaseTransactions;

    private SmsGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array', 'services.sendmysms.username' => 'mock-user', 'services.sendmysms.key' => 'mock-secret', 'services.sendmysms.send_in_local' => false]);
        Http::preventStrayRequests();
        SmsGateway::query()->update(['is_active' => false]);
        $this->gateway = SmsGateway::create(['provider_name' => 'SendMySMS', 'api_url' => SendMySmsGateway::URL, 'username' => 'mock-user', 'api_key' => 'mock-secret', 'status' => true, 'is_active' => true]);
    }

    public function test_https_form_post_uses_server_credentials_and_local_number(): void
    {
        Http::fake(['*' => Http::response(['status' => 'OK', 'response' => 'The SMS has been sent successfully.'])]);
        $this->assertTrue(app(SmsService::class)->sendSms('+8801712345678', 'OTP 654321'));
        Http::assertSent(fn ($r) => $r->method() === 'POST' && $r->url() === SendMySmsGateway::URL
            && $r['user'] === 'mock-user' && $r['key'] === 'mock-secret' && $r['to'] === '01712345678' && $r['msg'] === 'OTP 654321');
        $log = SmsLog::latest('id')->firstOrFail();
        $this->assertSame('Sent', $log->status);
        $this->assertStringNotContainsString('654321', $log->message.$log->response_text);
        $this->assertStringNotContainsString('mock-secret', $log->response_text);
    }

    public function test_documented_provider_errors_are_sanitized_and_fail_closed(): void
    {
        $errors = ['Wrong username or API-Key', 'Incorrect phone number', 'Message can not be blank', 'You do not have enough SMS credit'];
        foreach ($errors as $error) {
            Http::fake(['*' => Http::response(['status' => 'ERROR', 'response' => $error])]);
            $this->assertFalse(app(SendMySmsGateway::class)->send('01712345678', 'Test', $this->gateway)['success']);
        }
    }

    public function test_bad_json_http_error_and_timeout_do_not_expose_response_or_credentials(): void
    {
        Http::fake(['*' => Http::sequence()->push('raw mock-secret OTP 654321')->push(['status' => 'OK'], 500)->pushResponse(Http::failedConnection())]);
        foreach (range(1, 3) as $ignored) {
            $result = app(SendMySmsGateway::class)->send('01712345678', 'OTP 654321', $this->gateway);
            $this->assertFalse($result['success']);
            $this->assertStringNotContainsString('mock-secret', $result['response']);
            $this->assertStringNotContainsString('654321', $result['response']);
        }
    }

    public function test_missing_credentials_blank_message_and_bad_number_make_no_requests(): void
    {
        $provider = app(SendMySmsGateway::class);
        $this->assertFalse($provider->send('invalid', 'Test', $this->gateway)['success']);
        $this->assertFalse($provider->send('01712345678', '', $this->gateway)['success']);
        $this->gateway->api_key = null;
        $this->assertFalse($provider->send('01712345678', 'Test', $this->gateway)['success']);
        Http::assertNothingSent();
    }

    public function test_live_login_sends_random_otp_and_verifies_it_without_debug_leak(): void
    {
        config(['services.sendmysms.send_in_local' => true]);
        $mobile = '017'.random_int(10000000, 99999999);
        Supplier::create(['type' => 'Customer', 'name' => 'SendMySMS login test', 'mobile_no' => $mobile, 'status' => 1]);
        $otp = null;
        Http::fake(function ($r) use (&$otp) {
            preg_match('/\d{6}/', $r['msg'], $matches);
            $otp = $matches[0] ?? null;

            return Http::response(['status' => 'OK']);
        });
        $device = (string) Str::uuid();
        $this->postJson('/api/membership/v1/auth/initiate', ['mobile_no' => $mobile, 'device_id' => $device])->assertOk()->assertJsonPath('data.debug_otp', null);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp);
        $this->postJson('/api/membership/v1/auth/verify-otp', ['mobile_no' => $mobile, 'device_id' => $device, 'otp' => $otp])->assertOk();
    }

    public function test_failed_delivery_returns_503_and_does_not_leave_verifiable_otp(): void
    {
        config(['services.sendmysms.send_in_local' => true]);
        $mobile = '018'.random_int(10000000, 99999999);
        Supplier::create(['type' => 'Customer', 'name' => 'SMS failure test', 'mobile_no' => $mobile, 'status' => 1]);
        Http::fake(['*' => Http::response(['status' => 'ERROR', 'response' => 'You do not have enough SMS credit'])]);
        $device = (string) Str::uuid();
        $this->postJson('/api/membership/v1/auth/initiate', ['mobile_no' => $mobile, 'device_id' => $device])->assertStatus(503)->assertJsonPath('success', false);
        $this->postJson('/api/membership/v1/auth/verify-otp', ['mobile_no' => $mobile, 'device_id' => $device, 'otp' => '123456'])->assertUnprocessable();
    }

    public function test_admin_saves_encrypted_credentials_without_displaying_them(): void
    {
        $this->actingAs(User::firstOrFail());
        $controller = app(SmsGatewayController::class);
        $controller->store(Request::create('/', 'POST', ['provider_name' => 'SendMySMS', 'api_url' => SendMySmsGateway::URL, 'username' => 'mock-user', 'api_key' => 'mock-secret']));
        $row = SmsGateway::latest('id')->firstOrFail();
        $this->assertSame('mock-secret', $row->api_key);
        $this->assertNotSame('mock-secret', \Illuminate\Support\Facades\DB::table('sms_gateways')->where('id', $row->id)->value('api_key'));
        $this->assertArrayNotHasKey('api_key', $row->toArray());
        $html = $controller->index()->with('errors', new ViewErrorBag)->render();
        $this->assertStringContainsString('SendMySMS', $html);
        $this->assertStringNotContainsString('mock-secret', $html);
        $this->assertStringNotContainsString('mock-user', $html);
    }
}
