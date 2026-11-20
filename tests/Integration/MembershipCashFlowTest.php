<?php

namespace Tests\Integration;

use App\Models\Supplier;
use App\Services\Membership\CashFlowService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MembershipCashFlowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_daily_cash_flow_uses_approved_own_accounting_entries_and_hour_buckets(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-11-20 15:00:00'));
        $customer = Supplier::create(['name' => 'Cash flow test', 'type' => 'Customer', 'status' => 1]);
        $other = Supplier::create(['name' => 'Other cash flow test', 'type' => 'Customer', 'status' => 1]);
        $insert = function ($amount, $type, $date, $hour, $extra = []) use ($customer) {
            DB::table('shomriddhi_transactions')->insert($extra + ['customer_id' => $customer->id, 'amount' => $amount,
                'created_by' => \App\Models\User::firstOrFail()->id, 'type' => $type, 'date' => $date, 'status' => 'Approved', 'created_at' => $date.' '.$hour.':00:00', 'updated_at' => now()]);
        };
        $insert('100.25', 'deposit', '2026-11-20', '08');
        $insert('20.50', 'withdraw', '2026-11-20', '12');
        $insert('5.00', 'withdraw', '2026-11-20', '12'); // Separate fee ledger entry.
        $insert('51.00', 'withdraw', '2026-11-19', '12');
        $insert('900.00', 'deposit', '2026-11-20', '08', ['status' => 'Pending']);
        $insert('900.00', 'deposit', '2026-11-20', '08', ['customer_id' => $other->id]);
        $insert('900.00', 'deposit', '2026-11-20', '08', ['is_opening_balance' => 1]);
        $insert('900.00', 'withdraw', '2026-11-20', '08', ['deleted_at' => now()]);
        $data = app(CashFlowService::class)->forCustomer($customer);
        $this->assertEquals(100.25, $data['day']['total_inflow']);
        $this->assertEquals(25.50, $data['day']['total_outflow']);
        $this->assertEquals(100.25, $data['day']['inflow'][2]);
        $this->assertEquals(25.50, $data['day']['outflow'][3]);
        $this->assertEquals(-50, $data['day']['change_percent']);
        $this->assertEquals(76.50, $data['week']['total_outflow']);
        $this->assertCount(12, $data['year']['labels']);
    }

    public function test_empty_daily_flow_has_zero_series_and_no_invented_percentage(): void
    {
        $customer = Supplier::create(['name' => 'Empty cash flow', 'type' => 'Customer', 'status' => 1]);
        $data = app(CashFlowService::class)->forCustomer($customer);
        $this->assertEquals(array_fill(0, 6, 0), $data['day']['inflow']);
        $this->assertEquals(0, $data['day']['total_outflow']);
        $this->assertNull($data['day']['change_percent']);
    }
}
