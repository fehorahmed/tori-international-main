<?php

namespace Tests\Integration;

use App\Http\Requests\BranchManagement\SisterConcernRequest;
use App\Models\BranchManagement\SisterConcern;
use App\Models\User;
use App\Rules\BusinessDate;
use App\Support\ApplicationDate;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ApplicationDateTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_every_branch_and_user_use_dhaka_date_across_midnight_without_reloading(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-24 17:59:59', 'UTC'));
        $first = new SisterConcern;
        $first->setRawAttributes(['business_date' => '2020-01-01']);
        $second = new SisterConcern;
        $second->setRawAttributes(['business_date' => '2030-01-01']);
        $user = new User;
        $this->assertSame('2026-11-24', $first->business_date);
        $this->assertSame($first->business_date, $second->business_date);
        $this->assertSame($first->business_date, $user->business_date);
        Carbon::setTestNow(Carbon::parse('2026-11-24 18:00:00', 'UTC'));
        $this->assertSame('2026-11-25', ApplicationDate::today());
        $this->assertSame('2026-11-25', $first->business_date);
        $this->assertSame('2026-11-25', $second->business_date);
        $this->assertSame('2026-11-25', $user->business_date);
    }

    public function test_legacy_storage_is_ignored_and_ordinary_saves_do_not_rewrite_it(): void
    {
        Carbon::setTestNow('2026-11-24 12:00:00');
        $branch = SisterConcern::firstOrFail();
        DB::table('sister_concerns')->where('id', $branch->id)->update(['business_date' => '2020-01-01']);
        $branch->refresh();
        $branch->business_date = '2030-01-01';
        $branch->save();
        $this->assertSame('2020-01-01', $branch->fresh()->getRawOriginal('business_date'));
        $this->assertSame('2026-11-24', $branch->fresh()->business_date);
        $copy = $branch->replicate();
        $copy->code = 'DATE'.uniqid();
        $copy->name = 'Date test '.uniqid();
        $copy->save();
        $this->assertNull($copy->fresh()->getRawOriginal('business_date'));
        $this->assertSame('2026-11-24', $copy->business_date);
    }

    public function test_old_date_change_endpoint_cannot_modify_storage(): void
    {
        $this->withoutMiddleware();
        $branch = SisterConcern::firstOrFail();
        $before = $branch->getRawOriginal('business_date');
        $this->postJson('/admin/sister-concerns/update-business-date', ['id' => $branch->id, 'business_date' => '2030-01-01'])->assertStatus(410);
        $this->assertSame($before, $branch->fresh()->getRawOriginal('business_date'));
        $rules = (new SisterConcernRequest)->rules();
        $this->assertContains('prohibited', $rules['business_date']);
    }

    public function test_transaction_date_validation_uses_today_instead_of_branch_storage(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-24 18:00:00', 'UTC'));
        $this->assertTrue(Validator::make(['date' => '25-11-2026'], ['date' => [new BusinessDate]])->passes());
        $this->assertTrue(Validator::make(['date' => '24-11-2026'], ['date' => [new BusinessDate]])->fails());
    }
}
