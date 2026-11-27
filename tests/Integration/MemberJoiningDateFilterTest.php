<?php

namespace Tests\Integration;

use App\Models\BranchManagement\SisterConcern;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class MemberJoiningDateFilterTest extends TestCase
{
    use DatabaseTransactions;

    public function test_joining_filter_includes_end_day_and_combines_with_branch_filter(): void
    {
        $this->withoutMiddleware();
        $this->actingAs(User::firstOrFail());
        $branch = SisterConcern::firstOrFail();
        $token = 'Joining-'.Str::random(12);
        foreach (['2026-02-14 23:59:59', '2026-02-15 00:00:00', '2026-02-15 23:59:59', '2026-02-16 00:00:00'] as $date) {
            Supplier::create(['type' => 'Customer', 'status' => 1, 'name' => $token, 'origin_sister_concern_id' => $branch->id, 'created_at' => $date]);
        }
        $params = ['draw' => 1, 'start' => 0, 'length' => 10, 'branch_id' => $branch->id,
            'search' => ['value' => $token], 'columns' => [['data' => 'name', 'name' => 'name', 'searchable' => 'true', 'orderable' => 'true']]];
        $get = fn ($extra) => $this->getJson(route('shomriddhi-advance.members.datatable', $extra + $params), ['X-Requested-With' => 'XMLHttpRequest']);
        $get(['joined_from' => '2026-02-15', 'joined_to' => '2026-02-15'])->assertOk()->assertJsonPath('recordsFiltered', 2)
            ->assertJsonPath('data.0.branch_name', $branch->name);
        $get(['joined_from' => '2026-02-15'])->assertOk()->assertJsonPath('recordsFiltered', 3);
        $get(['joined_to' => '2026-02-15'])->assertOk()->assertJsonPath('recordsFiltered', 3);
        $get([])->assertOk()->assertJsonPath('recordsFiltered', 4);
        $get(['joined_from' => '2026-02-16', 'joined_to' => '2026-02-15'])->assertUnprocessable()->assertJsonValidationErrors('joined_to');
        $get(['joined_from' => 'invalid'])->assertUnprocessable()->assertJsonValidationErrors('joined_from');
    }
    public function test_accounts_filter_uses_opening_date_and_keeps_branch_scope(): void
    {
        $this->withoutMiddleware();
        $this->actingAs(User::firstOrFail());
        $branch = SisterConcern::firstOrFail();
        $other = SisterConcern::where('id', '!=', $branch->id)->firstOrFail();
        $member = Supplier::create(['type' => 'Customer', 'status' => 1, 'name' => 'Opening date test']);
        $token = 'Opening-'.Str::random(12);
        foreach (['2026-02-14', '2026-02-15', '2026-02-16', null] as $index => $date) {
            \App\Models\ShomriddhiAdvance\ShomriddhiAccount::create(['customer_id' => $member->id, 'origin_sister_concern_id' => $branch->id,
                'account_number' => $token.$index, 'opening_date' => $date, 'created_at' => '2026-01-01 00:00:00']);
        }
        \App\Models\ShomriddhiAdvance\ShomriddhiAccount::create(['customer_id' => $member->id, 'origin_sister_concern_id' => $other->id,
            'account_number' => $token.'foreign', 'opening_date' => '2026-02-15']);
        $params = ['draw' => 1, 'start' => 0, 'length' => 10, 'search' => ['value' => $token],
            'columns' => [['data' => 'account_number', 'name' => 'account_number', 'searchable' => 'true', 'orderable' => 'true']]];
        $get = fn ($extra) => $this->withSession(['active_sister_concern_id' => $branch->id])
            ->getJson(route('shomriddhi-advance.accounts.datatable', $extra + $params), ['X-Requested-With' => 'XMLHttpRequest']);
        $get(['start_date' => '2026-02-15', 'end_date' => '2026-02-15'])->assertOk()->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.account_number', $token.'1');
        $base = \App\Models\ShomriddhiAdvance\ShomriddhiAccount::where('origin_sister_concern_id', $branch->id);
        $get(['start_date' => '2026-02-15'])->assertOk()->assertJsonPath('recordsFiltered', (clone $base)->where('opening_date', '>=', '2026-02-15')->count());
        $get(['end_date' => '2026-02-15'])->assertOk()->assertJsonPath('recordsFiltered', (clone $base)->where('opening_date', '<=', '2026-02-15')->count());
        $get([])->assertOk()->assertJsonPath('recordsFiltered', $base->count());
        $get(['start_date' => '2026-02-16', 'end_date' => '2026-02-15'])->assertUnprocessable()->assertJsonValidationErrors('end_date');
        $get(['start_date' => 'invalid'])->assertUnprocessable()->assertJsonValidationErrors('start_date');
    }

}
