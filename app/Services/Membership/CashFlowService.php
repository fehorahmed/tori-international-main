<?php

namespace App\Services\Membership;

use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\Supplier;
use Carbon\Carbon;

class CashFlowService
{
    public function forCustomer(Supplier $customer): array
    {
        $today = now()->startOfDay();
        $result = [];
        foreach (['day', 'week', 'month', 'year'] as $period) {
            $start = match ($period) {
                'day' => $today->copy(),
                'week' => $today->copy()->startOfWeek(Carbon::MONDAY),
                'month' => $today->copy()->startOfMonth(),
                'year' => $today->copy()->startOfYear(),
            };
            $previous = match ($period) {
                'day' => $start->copy()->subDay(),
                'week' => $start->copy()->subWeek(),
                'month' => $start->copy()->subMonth(),
                'year' => $start->copy()->subYear(),
            };
            $labels = match ($period) {
                'day' => ['00', '04', '08', '12', '16', '20'],
                'week' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'month' => ['1–7', '8–14', '15–21', '22–28', '29–31'],
                'year' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            };
            $in = $out = array_fill(0, count($labels), 0);
            $oldOut = 0;
            // Use accounting date, not the last 50 activity entries; include charges,
            // payments and refunds once each through their savings ledger entries.
            $query = ShomriddhiTransaction::where('customer_id', $customer->id)->where('status', 'Approved')
                ->where(fn ($q) => $q->whereNull('is_opening_balance')->orWhere('is_opening_balance', 0))
                ->whereBetween('date', [$previous->toDateString(), $today->toDateString()])
                ->whereIn('type', ['deposit', 'withdraw'])
                ->selectRaw('DATE(date) as flow_date, type, SUM(amount) as total');
            if ($period === 'day') {
                $query->selectRaw('HOUR(created_at) as flow_hour')->groupByRaw('DATE(date), type, HOUR(created_at)');
            } else {
                $query->groupByRaw('DATE(date), type');
            }
            foreach ($query->get() as $row) {
                $date = Carbon::parse($row->flow_date);
                $amount = CashoutService::minor((string) $row->total);
                if ($date->lt($start)) {
                    if ($row->type === 'withdraw') $oldOut += $amount;
                    continue;
                }
                $bucket = match ($period) {
                    'day' => intdiv((int) $row->flow_hour, 4),
                    'week' => $date->dayOfWeekIso - 1,
                    'month' => intdiv($date->day - 1, 7),
                    'year' => $date->month - 1,
                };
                if ($row->type === 'deposit') $in[$bucket] += $amount;
                else $out[$bucket] += $amount;
            }
            $spending = array_sum($out);
            $result[$period] = ['labels' => $labels, 'inflow' => array_map(fn ($v) => $v / 100, $in),
                'outflow' => array_map(fn ($v) => $v / 100, $out), 'total_inflow' => array_sum($in) / 100,
                'total_outflow' => $spending / 100,
                'change_percent' => $oldOut > 0 ? round(($spending - $oldOut) * 100 / $oldOut, 1) : null];
        }
        return $result;
    }
}
