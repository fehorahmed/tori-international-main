<?php

namespace Tests\Integration;

use App\Http\Controllers\ShomriddhiAdvance\SlabController;
use App\Models\BranchManagement\SisterConcern;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiDailyInterest;
use App\Models\ShomriddhiAdvance\ShomriddhiInterestSlab;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ShomriddhiAdvance\DailyInterestAccrualService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InterestHistoryRecoveryTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-11-23 23:00:00'));
        DB::table('shomriddhi_interest_slabs')->delete();
        DB::table('shomriddhi_interest_rate_versions')->delete();
        $slab = ShomriddhiInterestSlab::create(['min_amount' => 1000, 'max_amount' => null, 'interest_rate' => 3]);
        DB::table('shomriddhi_interest_rate_versions')->insert(['effective_at' => '2026-11-01 00:00:00',
            'slabs' => json_encode([$slab->only('min_amount', 'max_amount', 'interest_rate')])]);
    }

    private function account(array $entries, string $balance): ShomriddhiAccount
    {
        $member = Supplier::create(['name' => 'Historical interest fixture', 'type' => 'Customer', 'status' => 1]);
        foreach ($entries as [$date, $type, $amount]) {
            DB::table('shomriddhi_transactions')->insert(['customer_id' => $member->id, 'date' => $date,
                'type' => $type, 'amount' => $amount, 'status' => 'Approved', 'created_by' => User::firstOrFail()->id,
                'created_at' => now(), 'updated_at' => now()]);
        }
        return ShomriddhiAccount::create(['customer_id' => $member->id, 'origin_sister_concern_id' => SisterConcern::firstOrFail()->id,
            'principal_balance' => $balance, 'accrued_interest' => '0.0000', 'last_interest_date' => '2026-11-20', 'status' => 'Active']);
    }

    public function test_recovery_uses_each_days_ledger_balance_and_archived_rate(): void
    {
        $slab = ShomriddhiInterestSlab::firstOrFail();
        $this->travelTo(\Carbon\Carbon::parse('2026-11-22 10:00:00'));
        app(SlabController::class)->update(Request::create('/', 'POST', ['min_amount' => 1000, 'max_amount' => null, 'interest_rate' => 6]), $slab);
        $this->travelTo(\Carbon\Carbon::parse('2026-11-23 23:00:00'));
        $account = $this->account([['2026-11-01', 'deposit', 5000], ['2026-11-22', 'deposit', 15000]], '20000.00');
        app(DailyInterestAccrualService::class)->accrue($account->id, '2026-11-23');
        $logs = ShomriddhiDailyInterest::where('customer_id', $account->customer_id)->orderBy('date')->get();
        $this->assertEquals([5000, 20000, 20000], $logs->pluck('principal_balance')->all());
        $this->assertEquals([3, 6, 6], $logs->pluck('interest_rate')->all());
        $this->assertEquals([0.4110, 3.2877, 3.2877], $logs->pluck('earned_interest')->all());
        $this->assertEquals(6.9864, $account->fresh()->accrued_interest);
        app(DailyInterestAccrualService::class)->accrue($account->id, '2026-11-23');
        $this->assertSame(3, ShomriddhiDailyInterest::where('customer_id', $account->customer_id)->count());
    }

    public function test_now_below_minimum_still_recovers_earlier_eligible_days_and_records_zero_days(): void
    {
        $account = $this->account([['2026-11-01', 'deposit', 20000], ['2026-11-22', 'withdraw', 19500]], '500.00');
        app(DailyInterestAccrualService::class)->accrue($account->id, '2026-11-23');
        $logs = ShomriddhiDailyInterest::where('customer_id', $account->customer_id)->orderBy('date')->get();
        $this->assertEquals([20000, 500, 500], $logs->pluck('principal_balance')->all());
        $this->assertEquals([1.6438, 0, 0], $logs->pluck('earned_interest')->all());
        $this->assertSame('2026-11-23', $account->fresh()->last_interest_date);
    }

    public function test_unknown_historical_rate_rolls_back_instead_of_guessing(): void
    {
        DB::table('shomriddhi_interest_rate_versions')->update(['effective_at' => '2026-11-22 00:00:00']);
        $account = $this->account([['2026-11-01', 'deposit', 5000]], '5000.00');
        try {
            app(DailyInterestAccrualService::class)->accrue($account->id, '2026-11-23');
            $this->fail('Unknown historical rate was guessed.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Historical interest rate is unavailable', $e->getMessage());
        }
        $this->assertSame('2026-11-20', $account->fresh()->last_interest_date);
        $this->assertSame(0, ShomriddhiDailyInterest::where('customer_id', $account->customer_id)->count());
    }

    public function test_unreconciled_ledger_blocks_historical_balance_reconstruction(): void
    {
        $account = $this->account([['2026-11-01', 'deposit', 5000]], '6000.00');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Savings journal does not reconcile');
        app(DailyInterestAccrualService::class)->accrue($account->id, '2026-11-23');
    }
}
