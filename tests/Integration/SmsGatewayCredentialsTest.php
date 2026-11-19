<?php

namespace Tests\Integration;

use App\Http\Controllers\SmsManagement\SmsGatewayController;
use App\Models\SmsManagement\SmsGateway;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SmsGatewayCredentialsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_credentials_are_encrypted_hidden_and_blank_edit_preserves_them(): void
    {
        $this->actingAs(User::firstOrFail());
        $g = SmsGateway::create(['provider_name' => 'SMS Bangladesh', 'api_url' => 'https://panel2.smsbangladesh.com',
            'username' => 'private-user', 'password' => 'private-password', 'api_key' => '', 'status' => true]);
        $raw = DB::table('sms_gateways')->where('id', $g->id)->first();
        $this->assertNotSame('private-user', $raw->username);
        $this->assertNotSame('private-password', $raw->password);
        $this->assertSame('private-password', $g->fresh()->password);
        $this->assertArrayNotHasKey('password', $g->toArray());
        $this->assertArrayNotHasKey('username', $g->toArray());
        app(SmsGatewayController::class)->update(Request::create('/', 'PUT', ['provider_name' => 'SMS Bangladesh',
            'api_url' => 'https://panel2.smsbangladesh.com', 'username' => null, 'password' => null]), $g);
        $this->assertSame('private-user', $g->fresh()->username);
        $this->assertSame('private-password', $g->fresh()->password);
    }

    public function test_changing_provider_requires_new_provider_credentials(): void
    {
        $this->actingAs(User::firstOrFail());
        $g = SmsGateway::create(['provider_name' => 'SSL Wireless', 'api_url' => 'https://smsplus.sslwireless.com/api/v3/send-sms',
            'api_key' => 'old-ssl-token', 'sender_id' => 'TORI']);
        $this->expectException(ValidationException::class);
        app(SmsGatewayController::class)->update(Request::create('/', 'PUT', ['provider_name' => 'SendMySMS',
            'api_url' => 'https://sendmysms.net/api.php', 'username' => 'new-user']), $g);
    }
}
