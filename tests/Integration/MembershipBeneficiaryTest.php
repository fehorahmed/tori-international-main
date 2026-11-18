<?php

namespace Tests\Integration;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MembershipBeneficiaryTest extends TestCase
{
    use DatabaseTransactions;

    private function member(): Supplier
    {
        return Supplier::create(['type' => 'Customer', 'name' => 'Quick Send test', 'member_no' => 'QS-'.Str::random(16), 'status' => 1]);
    }

    public function test_lookup_save_deduplicate_rename_and_remove_own_recipient(): void
    {
        $sender = $this->member();
        $recipient = $this->member();
        Sanctum::actingAs($sender);
        $base = '/api/membership/v1/beneficiaries';
        $this->postJson($base.'/lookup', ['recipient' => $recipient->member_no])->assertOk()->assertJsonPath('data.recipient_id', $recipient->id)->assertJsonMissingPath('data.pin');
        $id = $this->postJson($base, ['recipient_id' => $recipient->id, 'nickname' => 'Friend'])->assertCreated()->json('data.id');
        $this->postJson($base, ['recipient_id' => $recipient->id])->assertOk();
        $this->getJson($base)->assertOk()->assertJsonCount(1, 'data.items')->assertJsonPath('data.items.0.nickname', 'Friend');
        $this->patchJson($base.'/'.$id, ['nickname' => 'Family'])->assertOk();
        $recipient->update(['member_no' => 'QS-'.Str::random(16)]);
        $this->getJson($base)->assertJsonPath('data.items.0.member_no', $recipient->member_no)->assertJsonPath('data.items.0.nickname', 'Family');
        $this->deleteJson($base.'/'.$id)->assertOk();
        $this->getJson($base)->assertJsonCount(0, 'data.items');
    }

    public function test_ownership_self_inactive_and_limit_are_enforced(): void
    {
        $sender = $this->member();
        $recipient = $this->member();
        Sanctum::actingAs($sender);
        $base = '/api/membership/v1/beneficiaries';
        $this->postJson($base, ['recipient_id' => $sender->id])->assertUnprocessable();
        $recipient->update(['status' => 0]);
        $this->postJson($base, ['recipient_id' => $recipient->id])->assertUnprocessable();
        $recipient->update(['status' => 1]);
        $id = $this->postJson($base, ['recipient_id' => $recipient->id])->assertCreated()->json('data.id');
        $other = $this->member();
        Sanctum::actingAs($other);
        $this->getJson($base)->assertJsonCount(0, 'data.items');
        $this->patchJson($base.'/'.$id, ['nickname' => 'attack'])->assertNotFound();
        $this->deleteJson($base.'/'.$id)->assertNotFound();
        Sanctum::actingAs($sender);
        for ($i = 0; $i < 9; $i++) {
            $this->postJson($base, ['recipient_id' => $this->member()->id])->assertCreated();
        }
        $this->postJson($base, ['recipient_id' => $this->member()->id])->assertUnprocessable();
        $this->assertSame(10, DB::table('membership_beneficiaries')->where('customer_id', $sender->id)->count());
    }
}
