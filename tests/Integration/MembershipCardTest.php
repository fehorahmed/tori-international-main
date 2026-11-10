<?php

namespace Tests\Integration;

use App\Models\CardManagement\Card;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MembershipCardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_only_own_issued_cards_are_returned_and_active_card_is_preferred(): void
    {
        $customer = Supplier::where('type', '!=', 'Supplier')->where('status', 1)->firstOrFail();
        $customer->cards()->delete();
        Sanctum::actingAs($customer);
        $this->getJson('/api/membership/v1/card')->assertOk()->assertJsonPath('data.card', null);
        $card = Card::create([
            'cardable_type' => Supplier::class, 'cardable_id' => $customer->id,
            'card_number' => 'TEST-MEMBER-'.bin2hex(random_bytes(4)),
            'status' => 'Active', 'amount' => 100, 'payment_status' => 'Received',
            'created_by' => User::firstOrFail()->id,
            'origin_sister_concern_id' => $customer->origin_sister_concern_id,
        ]);
        $newer = $card->replicate();
        $newer->card_number = 'TEST-LOST-'.bin2hex(random_bytes(4));
        $newer->status = 'Lost';
        $newer->save();
        $this->getJson('/api/membership/v1/card')->assertOk()
            ->assertJsonPath('data.card.card_number', $card->card_number)
            ->assertJsonPath('data.card.holder_name', $customer->name)
            ->assertJsonPath('data.card.status', 'Active')
            ->assertJsonPath('data.card.fee', 100)
            ->assertJsonMissingPath('data.card.cvv')
            ->assertJsonMissingPath('data.card.expiry');
        $card->update(['status' => 'Revoked']);
        $this->getJson('/api/membership/v1/card')->assertOk()->assertJsonPath('data.card.status', 'Lost');
        $other = Supplier::whereKeyNot($customer->id)->where('type', '!=', 'Supplier')->where('status', 1)->firstOrFail();
        Sanctum::actingAs($other);
        $response = $this->getJson('/api/membership/v1/card')->assertOk();
        $this->assertNotSame($card->card_number, $response->json('data.card.card_number'));
        Sanctum::actingAs(User::firstOrFail());
        $this->getJson('/api/membership/v1/card')->assertForbidden();
    }
}
