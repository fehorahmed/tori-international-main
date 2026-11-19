<?php

namespace Tests\Integration;

use App\Http\Controllers\SmsManagement\SmsGatewayController;
use App\Models\SmsManagement\SmsGateway;
use App\Models\SmsManagement\SmsLog;
use App\Models\User;
use App\Services\SmsManagement\Gateways\SendMySmsGateway;
use App\Services\SmsManagement\Gateways\SslWirelessGateway;
use App\Services\SmsManagement\SmsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SslWirelessTest extends TestCase
{
    use DatabaseTransactions;

    private SmsGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $this->gateway = SmsGateway::create(['provider_name' => 'SSL Wireless', 'api_url' => SslWirelessGateway::URL,
            'api_key' => 'ssl-private-token', 'sender_id' => 'TORI', 'status' => true, 'is_active' => false]);
    }

    private function response(): array
    {
        return ['status' => 'SUCCESS', 'status_code' => 200, 'smsinfo' => [['sms_status' => 'SUCCESS', 'msisdn' => '8801712345678',
            'sms_body' => 'OTP 654321', 'reference_id' => 'ssl-reference-1']]];
    }

    public function test_documented_json_payload_normalizes_mobile_and_generates_valid_reference(): void
    {
        Http::fake(['*' => Http::response($this->response())]);
        $adapter = app(SslWirelessGateway::class);
        foreach (['01712345678', '1712345678', '+8801712345678'] as $number) {
            $this->assertTrue($adapter->send($number, 'OTP 654321', $this->gateway)['success']);
        }
        $ids = [];
        foreach (Http::recorded() as [$r]) {
            $this->assertSame('POST', $r->method());
            $this->assertSame('8801712345678', $r['msisdn']);
            $this->assertSame('ssl-private-token', $r['api_token']);
            $this->assertSame('TORI', $r['sid']);
            $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{20}$/', $r['csms_id']);
            $ids[] = $r['csms_id'];
        }
        $this->assertCount(3, array_unique($ids));
    }

    public function test_missing_blocked_or_mismatched_recipient_never_counts_as_success(): void
    {
        $responses = [
            ['status' => 'SUCCESS', 'status_code' => 200],
            ['status' => 'SUCCESS', 'status_code' => 200, 'smsinfo' => [['sms_status' => 'BLOCKED', 'msisdn' => '8801712345678']]],
            ['status' => 'SUCCESS', 'status_code' => 200, 'smsinfo' => [['sms_status' => 'SUCCESS', 'msisdn' => '8801812345678']]],
            ['status' => 'FAILED', 'status_code' => 4025],
        ];
        foreach ($responses as $response) {
            Http::fake(['*' => Http::response($response)]);
            $this->assertFalse(app(SslWirelessGateway::class)->send('01712345678', 'Test', $this->gateway)['success']);
        }
    }

    public function test_timeout_invalid_json_and_http_failure_are_safe_and_never_retried(): void
    {
        Http::fake(['*' => Http::sequence()->push('raw OTP 654321 ssl-private-token')->push($this->response(), 500)->pushResponse(Http::failedConnection())]);
        foreach (range(1, 3) as $ignored) {
            $result = app(SslWirelessGateway::class)->send('01712345678', 'OTP 654321', $this->gateway);
            $this->assertFalse($result['success']);
            $this->assertStringNotContainsString('654321', $result['response']);
            $this->assertStringNotContainsString('ssl-private-token', $result['response']);
        }
        Http::assertSentCount(3);
    }

    public function test_switching_providers_routes_otp_to_exactly_one_gateway_and_redacts_logs(): void
    {
        config(['services.sendmysms.username' => 'fake-user', 'services.sendmysms.key' => 'fake-key']);
        $other = SmsGateway::create(['provider_name' => 'SendMySMS', 'api_url' => SendMySmsGateway::URL, 'username' => 'fake-user', 'api_key' => 'fake-key', 'status' => true, 'is_active' => false]);
        Http::fake([SslWirelessGateway::URL => Http::response($this->response()), SendMySmsGateway::URL => Http::response(['status' => 'OK'])]);
        $controller = app(SmsGatewayController::class);
        $controller->makeActive($this->gateway);
        $this->assertSame(1, SmsGateway::where('is_active', true)->count());
        $this->assertTrue(app(SmsService::class)->sendSms('01712345678', 'OTP 654321'));
        $log = SmsLog::latest('id')->firstOrFail();
        $this->assertStringNotContainsString('654321', $log->message.$log->response_text);
        $this->assertStringNotContainsString('ssl-private-token', $log->response_text);
        $controller->makeActive($other);
        $this->assertFalse((bool) $this->gateway->fresh()->is_active);
        $this->assertSame(1, SmsGateway::where('is_active', true)->count());
        $this->assertTrue(app(SmsService::class)->sendSms('01712345678', 'OTP 654321'));
        Http::assertSentCount(2);
        Http::assertSent(fn ($r) => $r->url() === SendMySmsGateway::URL);
        Http::assertSent(fn ($r) => $r->url() === SslWirelessGateway::URL);
    }

    public function test_invalid_configuration_or_phone_makes_no_external_request(): void
    {
        $adapter = app(SslWirelessGateway::class);
        $this->assertFalse($adapter->send('invalid', 'Test', $this->gateway)['success']);
        $this->gateway->api_url = 'https://other.example/send';
        $this->assertFalse($adapter->send('01712345678', 'Test', $this->gateway)['success']);
        $this->gateway->api_url = SslWirelessGateway::URL;
        $this->gateway->sender_id = null;
        $this->assertFalse($adapter->send('01712345678', 'Test', $this->gateway)['success']);
        Http::assertNothingSent();
    }

    public function test_admin_blank_token_preserves_existing_secret_and_view_does_not_expose_it(): void
    {
        $this->actingAs(User::firstOrFail());
        $controller = app(SmsGatewayController::class);
        $controller->update(Request::create('/', 'PUT', ['provider_name' => 'SSL Wireless', 'api_url' => SslWirelessGateway::URL,
            'api_key' => null, 'sender_id' => 'TORI']), $this->gateway);
        $this->assertSame('ssl-private-token', $this->gateway->fresh()->api_key);
        $html = $controller->index()->with('errors', new ViewErrorBag)->render();
        $this->assertStringNotContainsString('ssl-private-token', $html);
        try {
            $controller->store(Request::create('/', 'POST', ['provider_name' => 'SSL Wireless', 'api_url' => SslWirelessGateway::URL, 'api_key' => 'new-key']));
            $this->fail('SSL Sender ID is required.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('sender_id', $e->errors());
        }
    }
}
