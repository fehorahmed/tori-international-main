<?php

namespace Tests\Integration;

use App\Http\Controllers\Ecommerce\API\FrontendController;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EcommerceRegistrationTest extends TestCase
{
    use DatabaseTransactions;

    private function payload(array $extra = []): array
    {
        $upazila = DB::table('upazilas')->first();
        $district = DB::table('districts')->find($upazila->district_id);
        return $extra + ['name' => 'Ecommerce registration fixture', 'mobile' => '019'.random_int(10000000, 99999999),
            'password' => 'test-password-123', 'agree' => true, 'division_id' => $district->division_id,
            'district_id' => $district->id, 'upazila_id' => $upazila->id, 'address' => 'Test address'];
    }

    private function register(array $data)
    {
        $request = Request::create('/api/register', 'POST', $data);
        $request->setLaravelSession(app('session.store'));
        app()->instance('request', $request);
        return app(FrontendController::class)->register($request);
    }

    public function test_default_branch_member_number_address_and_referral_are_saved(): void
    {
        $referrer = Supplier::create(['name' => 'Referral fixture', 'type' => 'Customer', 'status' => 1, 'member_no' => 'REF'.random_int(100000, 999999)]);
        $data = $this->payload(['referral_type' => 'member', 'referral_code' => $referrer->member_no]);
        $response = $this->register($data);
        $this->assertSame(201, $response->status(), $response->getContent());
        $user = Supplier::where('mobile_no', $data['mobile'])->firstOrFail();
        $this->assertNotEmpty($user->member_no);
        $account = \App\Models\ShomriddhiAdvance\ShomriddhiAccount::where('customer_id', $user->id)->sole();
        $this->assertEquals(0, $account->principal_balance);
        $this->assertSame('Active', $account->status);
        $this->assertEquals(1, $account->origin_sister_concern_id);
        $this->assertNotEmpty($account->account_number);
        $head = \App\Models\Account\AccountHead::where('supplier_id', $user->id)->where('head_type', 'savings')->sole();
        $this->assertSame('Credit', $head->nature);
        $this->assertEquals(1, $user->origin_sister_concern_id);
        $this->assertEquals($data['upazila_id'], $user->upazila_id);
        $this->assertSame('Test address', $user->address);
        $this->assertDatabaseHas('membership_referrals', ['customer_id' => $user->id, 'referrer_id' => $referrer->id, 'referrer_type' => 'member']);
    }

    public function test_selected_branch_and_agent_referral_are_saved(): void
    {
        $branch = \App\Models\BranchManagement\SisterConcern::where('status', 1)->where('id', '!=', 1)->firstOrFail();
        $agent = \App\Models\User::where('status', 1)->whereNotNull('username')->firstOrFail();
        $data = $this->payload(['branch_id' => $branch->id, 'referral_type' => 'agent', 'referral_code' => $agent->username]);
        $response = $this->register($data);
        $this->assertSame(201, $response->status(), $response->getContent());
        $user = Supplier::where('mobile_no', $data['mobile'])->firstOrFail();
        $this->assertEquals($branch->id, $user->origin_sister_concern_id);
        $this->assertStringStartsWith((string) $branch->code, $user->member_no);
        $this->assertDatabaseHas('membership_referrals', ['customer_id' => $user->id, 'referrer_id' => $agent->id, 'referrer_type' => 'agent']);
    }

    public function test_invalid_referral_cannot_create_customer(): void
    {
        $data = $this->payload(['referral_type' => 'member', 'referral_code' => 'INVALID-REFERRAL-FIXTURE']);
        $response = $this->register($data);
        $this->assertSame(422, $response->status());
        $this->assertArrayHasKey('referral_code', $response->getData(true)['errors']);
        $this->assertDatabaseMissing('suppliers', ['mobile_no' => $data['mobile']]);
    }

    public function test_invalid_mobile_numbers_are_rejected(): void
    {
        foreach (['12345678909', '01712345678,', '017,12345678', '0171234567', '017123456789', '01212345678', '0171234567a'] as $mobile) {
            $response = $this->register($this->payload(['mobile' => $mobile]));
            $this->assertSame(422, $response->status(), $mobile);
            $this->assertArrayHasKey('mobile', $response->getData(true)['errors']);
            $this->assertDatabaseMissing('suppliers', ['mobile_no' => $mobile]);
        }
    }

    public function test_account_failure_rolls_back_member_and_referral(): void
    {
        $data = $this->payload();
        $count = \App\Models\Account\AccountHead::count();
        \App\Models\ShomriddhiAdvance\ShomriddhiAccount::creating(function () { throw new \RuntimeException('Account fixture failure'); });
        try {
            $response = $this->register($data);
            $this->assertSame(500, $response->status());
            $this->assertDatabaseMissing('suppliers', ['mobile_no' => $data['mobile']]);
            $this->assertSame($count, \App\Models\Account\AccountHead::count());
        } finally {
            \App\Models\ShomriddhiAdvance\ShomriddhiAccount::flushEventListeners();
        }
    }

    public function test_backend_requires_terms_and_rejects_inactive_branch(): void
    {
        $response = $this->register($this->payload(['agree' => false]));
        $this->assertSame(422, $response->status());
        $this->assertArrayHasKey('agree', $response->getData(true)['errors']);
        \App\Models\BranchManagement\SisterConcern::whereKey(1)->update(['status' => 0]);
        $data = $this->payload();
        $response = $this->register($data);
        $this->assertSame(422, $response->status());
        $this->assertArrayHasKey('branch_id', $response->getData(true)['errors']);
        $this->assertDatabaseMissing('suppliers', ['mobile_no' => $data['mobile']]);
    }

    public function test_mismatched_address_is_rejected(): void
    {
        $data = $this->payload(['division_id' => 0]);
        $response = $this->register($data);
        $this->assertSame(422, $response->status());
        $this->assertDatabaseMissing('suppliers', ['mobile_no' => $data['mobile']]);
    }
}
