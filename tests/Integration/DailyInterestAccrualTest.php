<?php

namespace Tests\Integration;

use App\Models\BranchManagement\SisterConcern;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiDailyInterest;
use App\Models\Supplier;
use App\Services\ShomriddhiAdvance\DailyInterestAccrualService;
use App\Services\ShomriddhiAdvance\DynamicInterestSlabService;
use App\Services\ShomriddhiAdvance\InterestCalculationService;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DailyInterestAccrualTest extends TestCase
{
    use DatabaseTransactions;

    private function account(): ShomriddhiAccount
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-11-21 23:00:00'));
        $member = Supplier::create(['name' => 'Atomic interest fixture', 'type' => 'Customer', 'status' => 1]);
        DB::table('shomriddhi_transactions')->insert(['customer_id' => $member->id, 'type' => 'deposit', 'amount' => '10000.00',
            'date' => '2026-11-20', 'status' => 'Approved', 'created_by' => \App\Models\User::firstOrFail()->id, 'created_at' => now(), 'updated_at' => now()]);
        return ShomriddhiAccount::create(['customer_id' => $member->id,
            'origin_sister_concern_id' => SisterConcern::firstOrFail()->id,
            'principal_balance' => '10000.00', 'accrued_interest' => '0.7500',
            'last_interest_date' => '2026-11-20', 'status' => 'Active']);
    }

    private function service(): DailyInterestAccrualService
    {
        $slabs = new class extends DynamicInterestSlabService {
            public function getRateForAmount($amount): float { return 6.0; }
            public function getRateForAmountOn($amount, string $date): float { return 6.0; }
        };
        return new DailyInterestAccrualService($slabs, new InterestCalculationService);
    }

    public function test_daily_history_failure_rolls_back_accrued_interest_and_processing_date(): void
    {
        $account = $this->account();
        $failOnce = true;
        DB::listen(function (QueryExecuted $query) use (&$failOnce) {
            if ($failOnce && str_starts_with(strtolower($query->sql), 'insert into `shomriddhi_daily_interests`')) {
                $failOnce = false;
                throw new \RuntimeException('Simulated daily history storage failure');
            }
        });
        try {
            $this->service()->accrue($account->id, '2026-11-21');
            $this->fail('Expected history failure.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulated daily history storage failure', $e->getMessage());
        }
        $this->assertSame('0.7500', $account->fresh()->accrued_interest);
        $this->assertSame('2026-11-20', $account->fresh()->last_interest_date);
        $this->assertSame(0, ShomriddhiDailyInterest::where('customer_id', $account->customer_id)->count());
        $this->service()->accrue($account->id, '2026-11-21');
        $this->assertSame(1, ShomriddhiDailyInterest::where('customer_id', $account->customer_id)->count());
    }

    public function test_rerun_is_idempotent_and_daily_totals_match_stored_precision(): void
    {
        $account = $this->account();
        $this->service()->accrue($account->id, '2026-11-23');
        $total = ShomriddhiDailyInterest::where('customer_id', $account->customer_id)->sum('earned_interest');
        $this->assertEquals(round(0.75 + $total, 4), (float) $account->fresh()->accrued_interest);
        $this->service()->accrue($account->id, '2026-11-23');
        $this->assertSame(3, ShomriddhiDailyInterest::where('customer_id', $account->customer_id)->count());
        $this->assertEquals(round(0.75 + $total, 4), (float) $account->fresh()->accrued_interest);
        $this->assertSame('10000.00', $account->fresh()->principal_balance);
    }

    public function test_account_eligibility_is_reloaded_before_accrual(): void
    {
        $account = $this->account();
        DB::table('shomriddhi_accounts')->where('id', $account->id)->update(['status' => 'Closed']);
        $this->service()->accrue($account->id, '2026-11-21');
        $this->assertSame(0, ShomriddhiDailyInterest::where('customer_id', $account->customer_id)->count());
        $this->assertSame('0.7500', $account->fresh()->accrued_interest);
    }
}
