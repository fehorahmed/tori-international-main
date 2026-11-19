<?php

namespace Tests\Integration;

use App\Http\Controllers\SmsManagement\SmsGatewayController;
use App\Models\SmsManagement\SmsGateway;
use App\Models\SmsManagement\SmsLog;
use App\Services\SmsManagement\Gateways\SmsBangladeshGateway;
use App\Services\SmsManagement\SmsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class SmsBangladeshTest extends TestCase
{
    use DatabaseTransactions;

    private SmsGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.smsbangladesh.username' => 'fake-user', 'services.smsbangladesh.password' => 'fake-password']);
        Http::preventStrayRequests();
        $this->gateway = SmsGateway::create(['provider_name' => 'SMS Bangladesh', 'api_url' => 'https://panel2.smsbangladesh.com',
            'username' => 'fake-user', 'password' => 'fake-password', 'api_key' => '', 'sender_id' => null, 'is_active' => false, 'status' => true]);
    }

    public function test_activate_routes_otp_to_otp_endpoint_and_keeps_credentials_out_of_logs(): void
    {
        Http::fake(['*' => Http::response(['success' => 1, 'response_code' => 100, 'status' => 'Delivered'])]);
        app(SmsGatewayController::class)->makeActive($this->gateway);
        $this->assertSame(1, SmsGateway::where('is_active', true)->count());
        $this->assertTrue(app(SmsService::class)->sendSms('01712345678', 'OTP 654321'));
        Http::assertSent(fn ($r) => str_contains($r->url(), '/otp?') && $r['to'] === '8801712345678'
            && $r['user'] === 'fake-user' && $r['password'] === 'fake-password' && ! isset($r['from']));
        $log = SmsLog::latest('id')->firstOrFail();
        $this->assertStringNotContainsString('fake-password', $log->response_text);
        $this->assertStringNotContainsString('654321', $log->message.$log->response_text);
    }

    public function test_bulk_partial_failure_and_masking(): void
    {
        $this->gateway->sender_id = 'TORI';
        Http::fake(['*' => Http::response(['success' => 1, 'response_code' => 100, 'delivery_status' => [
            ['MSISDN' => '8801712345678', 'Status' => 1], ['MSISDN' => '8801812345678', 'Status' => 0]]])]);
        $result = app(SmsBangladeshGateway::class)->send('01712345678,01812345678', 'Test', $this->gateway);
        $this->assertFalse($result['success']);
        $this->assertTrue(json_decode($result['response'], true)['recipients']['8801712345678']);
        Http::assertSent(fn ($r) => str_contains($r->url(), '/api?') && $r['from'] === 'TORI');
    }

    public function test_errors_malformed_response_and_timeout_fail_without_leaking_secrets(): void
    {
        Http::fake(['*' => Http::sequence()->push(['success' => 0, 'response_code' => 105])
            ->push(['success' => 1, 'response_code' => 100, 'status' => 'Failed'])->push('raw fake-password 654321')->pushResponse(Http::failedConnection())]);
        foreach (range(1, 4) as $ignored) {
            $result = app(SmsBangladeshGateway::class)->sendOtp('01712345678', 'OTP 654321', $this->gateway);
            $this->assertFalse($result['success']);
            $this->assertStringNotContainsString('fake-password', $result['response']);
            $this->assertStringNotContainsString('654321', $result['response']);
        }
    }

    public function test_missing_credentials_prevent_activation_and_no_http_is_sent(): void
    {
        $this->gateway->password = null;
        $this->assertFalse(app(SmsBangladeshGateway::class)->sendOtp('01712345678', 'Test', $this->gateway)['success']);
        Http::assertNothingSent();
        $this->expectException(HttpException::class);
        app(SmsGatewayController::class)->makeActive($this->gateway);
    }
}
