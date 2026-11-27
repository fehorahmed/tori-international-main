<?php

namespace Tests\Integration;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class ReferralDatatableTest extends TestCase
{
    use DatabaseTransactions;

    public function test_both_datatables_search_filter_sort_and_restrict_non_admin(): void
    {
        $this->withoutMiddleware();
        $user = User::firstOrFail();
        $user->update(['role' => 'Admin']);
        $this->actingAs($user);
        view()->share('errors', new ViewErrorBag);
        $token = 'ReferralTable-'.Str::random(10);
        $referrer = Supplier::create(['name' => $token.' <Referrer>', 'type' => 'Customer', 'status' => 1]);
        foreach (['Alpha', 'Zulu'] as $name) {
            $member = Supplier::create(['name' => $token.$name, 'type' => 'Customer', 'status' => 1, 'member_no' => Str::random(12)]);
            DB::table('membership_referrals')->insert(['customer_id' => $member->id, 'referrer_type' => 'member', 'referrer_id' => $referrer->id, 'referral_code' => $token, 'created_at' => now(), 'updated_at' => now()]);
        }
        $url = route('card-management.referrals.index');
        $this->get($url)->assertOk()->assertSee('referral-summary')->assertSee('referral-details');
        $base = ['type' => 'member', 'referrer_id' => $referrer->id, 'draw' => 1, 'length' => 1, 'start' => 0];
        $get = fn ($extra) => $this->getJson($url.'?'.http_build_query($extra + $base), ['X-Requested-With' => 'XMLHttpRequest']);
        $summary = $get(['table' => 'summary', 'columns' => [['data' => 'referrals', 'name' => 'referrals']], 'order' => [['column' => 0, 'dir' => 'desc']]])
            ->assertOk()->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.referrals', 2)->assertJsonPath('data.0.fee_label', '0 / 2');
        $this->assertStringContainsString('&lt;Referrer&gt;', $summary->json('data.0.referrer_label'));
        $details = ['table' => 'details', 'columns' => [['data' => 'member_label', 'name' => 'c.name']], 'order' => [['column' => 0, 'dir' => 'asc']]];
        $get($details)->assertOk()->assertJsonPath('recordsFiltered', 2)->assertJsonPath('data.0.customer_name', $token.'Alpha');
        $get(['search' => ['value' => $token.'Zulu']] + $details)->assertOk()->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.customer_name', $token.'Zulu');
        $get(['search' => ['value' => 'Missing-'.$token]] + $details)->assertOk()->assertJsonPath('recordsFiltered', 0);
        $get(['table' => 'summary', 'search' => ['value' => $token]])->assertOk()->assertJsonPath('recordsFiltered', 1);
        $ids = DB::table('membership_referrals')->where('referrer_type', 'member')->where('referrer_id', $referrer->id)->orderBy('id')->pluck('id');
        DB::table('membership_referrals')->where('id', $ids[0])->update(['created_at' => '2026-02-15 23:59:59']);
        DB::table('membership_referrals')->where('id', $ids[1])->update(['created_at' => '2026-02-16 00:00:00']);
        $dates = ['start_date' => '2026-02-15', 'end_date' => '2026-02-15'];
        $get($dates + $details)->assertOk()->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.customer_name', $token.'Alpha');
        $get($dates + ['table' => 'summary'])->assertOk()->assertJsonPath('data.0.referrals', 1);
        $get(['end_date' => '2026-02-15'] + $details)->assertOk()->assertJsonPath('recordsFiltered', 1);
        $get(['start_date' => '2026-02-16'] + $details)->assertOk()->assertJsonPath('recordsFiltered', 1);
        $get(['start_date' => '2026-02-17', 'end_date' => '2026-02-15'] + $details)->assertUnprocessable()->assertJsonValidationErrors('end_date');
        $get(['start_date' => 'bad'] + $details)->assertUnprocessable()->assertJsonValidationErrors('start_date');
        $get(['table' => 'details', 'type' => 'bad'])->assertUnprocessable();
        $user->update(['role' => 'Staff']);
        $get($details)->assertForbidden();
    }
}
