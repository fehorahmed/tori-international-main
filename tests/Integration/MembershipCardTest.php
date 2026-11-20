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

    public function test_admin_member_card_dates_include_end_of_day_and_combine_with_member_filter(): void
    {
        $user = User::firstOrFail();
        $branch = \App\Models\BranchManagement\SisterConcern::where('status', 1)->firstOrFail();
        $member = Supplier::create(['name' => 'Card date test', 'type' => 'Customer', 'status' => 1]);
        $this->actingAs($user, 'web')->withSession(['active_sister_concern_id' => $branch->id]);
        foreach (['2026-11-19 23:59:59', '2026-11-20 00:00:00', '2026-11-20 23:59:59', '2026-11-21 00:00:00'] as $date) {
            $card = Card::create(['cardable_type' => Supplier::class, 'cardable_id' => $member->id,
                'origin_sister_concern_id' => $branch->id, 'created_by' => $user->id,
                'card_number' => 'DATE-'.bin2hex(random_bytes(5)), 'status' => 'Active', 'amount' => 100, 'payment_status' => 'Received']);
            $card->forceFill(['created_at' => $date])->save();
        }
        $fetch = function (array $filters) use ($member) {
            $request = \Illuminate\Http\Request::create('/', 'GET', $filters + ['member_id' => $member->id, 'draw' => 1, 'start' => 0, 'length' => 10]);
            $this->app->instance('request', $request);
            return app(\App\Http\Controllers\CardManagement\MemberCardController::class)->dataTable($request)->getData(true);
        };
        $this->assertSame(2, $fetch(['from_date' => '2026-11-20', 'to_date' => '2026-11-20'])['recordsFiltered']);
        $this->assertSame(3, $fetch(['from_date' => '2026-11-20'])['recordsFiltered']);
        $this->assertSame(3, $fetch(['to_date' => '2026-11-20'])['recordsFiltered']);
        $this->assertSame(4, $fetch([])['recordsFiltered']);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $fetch(['from_date' => '2026-11-21', 'to_date' => '2026-11-20']);
    }

    public function test_admin_card_search_matches_holder_name_and_mobile_within_branch(): void
    {
        $user = User::firstOrFail();
        $branch = \App\Models\BranchManagement\SisterConcern::where('status', 1)->firstOrFail();
        $other = \App\Models\BranchManagement\SisterConcern::where('status', 1)->whereKeyNot($branch->id)->firstOrFail();
        $this->actingAs($user, 'web')->withSession(['active_sister_concern_id' => $branch->id]);
        $member = Supplier::create(['name' => 'Mobile card test', 'type' => 'Customer', 'status' => 1, 'mobile_no' => '01798765432']);
        $card = Card::create(['cardable_type' => Supplier::class, 'cardable_id' => $member->id,
            'origin_sister_concern_id' => $branch->id, 'created_by' => $user->id,
            'card_number' => 'MOBILE-'.bin2hex(random_bytes(5)), 'status' => 'Active', 'amount' => 100, 'payment_status' => 'Received']);
        $foreign = $card->replicate();
        $foreign->card_number = 'FOREIGN-'.bin2hex(random_bytes(5));
        $foreign->origin_sister_concern_id = $other->id;
        $foreign->save();
        foreach (['01798765432', '98765432', 'Mobile card test', 'card test', 'NOT-A-NUMBER'] as $term) {
            $request = \Illuminate\Http\Request::create('/', 'GET', ['draw' => 1, 'start' => 0, 'length' => 10,
                'member_id' => $member->id, 'search' => ['value' => $term, 'regex' => 'false'],
                'columns' => [['data' => 'holder_name', 'name' => 'holder_name', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']], ['data' => 'holder_mobile', 'name' => 'holder_mobile', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']]]]);
            $this->app->instance('request', $request);
            $data = app(\App\Http\Controllers\CardManagement\MemberCardController::class)->dataTable($request)->getData(true);
            $this->assertSame($term === 'NOT-A-NUMBER' ? 0 : 1, $data['recordsFiltered']);
            if ($data['recordsFiltered']) $this->assertSame($card->card_number, $data['data'][0]['card_number']);
        }
    }

    public function test_ecommerce_dashboard_returns_only_own_card_details_and_no_pin(): void
    {
        $customer = Supplier::where('type', 'Customer')->where('status', 1)->firstOrFail();
        $customer->cards()->delete();
        $this->actingAs($customer, 'customer')->getJson('/api/user')
            ->assertOk()->assertJsonPath('membership_card', null)->assertJsonMissingPath('pin');
        $card = Card::create([
            'cardable_type' => Supplier::class, 'cardable_id' => $customer->id,
            'card_number' => 'TEST-WEB-'.bin2hex(random_bytes(4)),
            'status' => 'Active', 'amount' => 100, 'payment_status' => 'Received',
            'created_by' => User::firstOrFail()->id,
            'origin_sister_concern_id' => $customer->origin_sister_concern_id,
        ]);
        $this->getJson('/api/user')->assertOk()
            ->assertJsonPath('membership_card.card_number', $card->card_number)
            ->assertJsonPath('membership_card.holder_name', $customer->name)
            ->assertJsonPath('membership_card.fee_status', 'Received')
            ->assertJsonMissingPath('pin');
    }

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
