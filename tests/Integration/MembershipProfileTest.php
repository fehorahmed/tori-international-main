<?php

namespace Tests\Integration;

use App\Http\Controllers\CardManagement\KycReviewController;
use App\Models\CustomerKyc;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/** Uses rollback transactions only. Never runs migrations, seeders or RefreshDatabase. */
class MembershipProfileTest extends TestCase
{
    use DatabaseTransactions;

    private Supplier $customer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        Storage::fake('kyc');
        $this->customer = Supplier::where('type', '!=', 'Supplier')->where('status', 1)->firstOrFail();
        CustomerKyc::where('customer_id', $this->customer->id)->delete();
        Sanctum::actingAs($this->customer);
    }

    private function upload(): array
    {
        return [
            'nid_number' => '1234567890',
            'selfie' => UploadedFile::fake()->image('selfie.jpg'),
            'nid_front' => UploadedFile::fake()->image('front.jpg'),
            'nid_back' => UploadedFile::fake()->image('back.jpg'),
        ];
    }

    public function test_profile_saves_and_does_not_allow_identity_or_balance_changes(): void
    {
        $this->putJson('/api/membership/v1/profile', [
            'name' => 'Membership Profile Test', 'email' => 'profile-test@example.invalid',
            'address' => 'Test address', 'mobile_no' => '01900000999', 'status' => 0,
            'principal_balance' => 999999,
        ])->assertOk()->assertJsonPath('data.name', 'Membership Profile Test')
            ->assertJsonPath('data.mobile_no', $this->customer->mobile_no)
            ->assertJsonPath('data.is_verified', false)
            ->assertJsonPath('data.kyc.status', 'Not submitted');
        $this->getJson('/api/membership/v1/profile')->assertOk()
            ->assertJsonPath('data.address', 'Test address')
            ->assertJsonPath('data.kyc.documents', []);
    }

    public function test_three_documents_are_required_and_nid_is_validated(): void
    {
        $this->postJson('/api/membership/v1/kyc', ['nid_number' => '12'])
            ->assertUnprocessable()->assertJsonValidationErrors(['nid_number', 'selfie', 'nid_front', 'nid_back']);
        $files = $this->upload();
        $files['nid_back'] = UploadedFile::fake()->create('back.pdf', 1, 'application/pdf');
        $this->postJson('/api/membership/v1/kyc', $files)
            ->assertUnprocessable()->assertJsonValidationErrors('nid_back');
        $this->assertSame(0, CustomerKyc::where('customer_id', $this->customer->id)->count());
    }

    public function test_submission_is_pending_private_and_cannot_be_resubmitted_while_pending(): void
    {
        $this->postJson('/api/membership/v1/kyc', $this->upload())->assertCreated()
            ->assertJsonPath('data.status', 'Pending');
        $kyc = CustomerKyc::where('customer_id', $this->customer->id)->firstOrFail();
        foreach (['selfie', 'nid_front', 'nid_back'] as $kind) {
            Storage::disk('kyc')->assertExists($kyc->{$kind.'_path'});
            $this->get('/api/membership/v1/kyc/'.$kyc->id.'/documents/'.$kind)
                ->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        }
        $this->postJson('/api/membership/v1/kyc', $this->upload())->assertUnprocessable();
        $this->putJson('/api/membership/v1/profile', ['name' => 'Changed after submission'])
            ->assertUnprocessable();
        $other = Supplier::whereKeyNot($this->customer->id)->where('status', 1)->firstOrFail();
        Sanctum::actingAs($other);
        $this->getJson('/api/membership/v1/kyc/'.$kyc->id.'/documents/selfie')->assertForbidden();
        Sanctum::actingAs(User::firstOrFail());
        $this->getJson('/api/membership/v1/profile')->assertForbidden();
        $this->postJson('/api/membership/v1/kyc', $this->upload())->assertForbidden();
    }

    public function test_review_sets_verified_state_and_rejects_duplicate_reviews(): void
    {
        $this->postJson('/api/membership/v1/kyc', $this->upload())->assertCreated();
        $kyc = CustomerKyc::where('customer_id', $this->customer->id)->firstOrFail();
        $reviewer = User::firstOrFail();
        $reviewer->givePermissionTo('customer_edit');
        $this->actingAs($reviewer, 'web');
        $this->withSession(['active_sister_concern_id' => $this->customer->origin_sister_concern_id]);
        // Controller-level authorization, using the real session user and DB transaction.
        $request = Request::create('/', 'POST', ['status' => 'Verified']);
        app(KycReviewController::class)->review($request, $kyc);
        $this->assertSame('Verified', $kyc->fresh()->status);
        $this->assertSame('1234567890', $this->customer->fresh()->nid_number);
        try {
            app(KycReviewController::class)->review($request, $kyc);
            $this->fail('A reviewed KYC must not be reviewed twice.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }
        Sanctum::actingAs($this->customer->fresh());
        $this->getJson('/api/membership/v1/profile')->assertOk()->assertJsonPath('data.is_verified', true);
    }

    public function test_branch_scope_and_rejection_resubmission(): void
    {
        $this->postJson('/api/membership/v1/kyc', $this->upload())->assertCreated();
        $kyc = CustomerKyc::where('customer_id', $this->customer->id)->firstOrFail();
        $reviewer = User::firstOrFail();
        $reviewer->givePermissionTo('customer_edit');
        $this->actingAs($reviewer, 'web');
        $this->withSession(['active_sister_concern_id' => -1]);
        $controller = app(KycReviewController::class);
        try {
            $controller->document($kyc, 'selfie');
            $this->fail('Another branch must not see the document.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->withSession(['active_sister_concern_id' => $this->customer->origin_sister_concern_id]);
        $controller->review(Request::create('/', 'POST', [
            'status' => 'Rejected', 'reviewer_message' => 'Please upload a clear NID back photo.',
        ]), $kyc);
        Sanctum::actingAs($this->customer);
        $this->getJson('/api/membership/v1/profile')->assertOk()
            ->assertJsonPath('data.is_verified', false)
            ->assertJsonPath('data.kyc.reviewer_message', 'Please upload a clear NID back photo.');
        $this->postJson('/api/membership/v1/kyc', $this->upload())->assertCreated()
            ->assertJsonPath('data.status', 'Pending');
        $this->assertSame(2, CustomerKyc::where('customer_id', $this->customer->id)->count());
    }
}
