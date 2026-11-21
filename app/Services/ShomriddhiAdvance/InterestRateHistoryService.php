<?php

namespace App\Services\ShomriddhiAdvance;

use App\Models\ShomriddhiAdvance\ShomriddhiInterestSlab;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InterestRateHistoryService
{
    public function lock(): void
    {
        if (! DB::table('shomriddhi_interest_rate_versions')->orderBy('id')->lockForUpdate()->first()) {
            throw new \RuntimeException('Interest rate history baseline is missing.');
        }
    }

    public function capture(): void
    {
        DB::table('shomriddhi_interest_rate_versions')->insert(['effective_at' => now(), 'slabs' => json_encode($this->current())]);
    }

    private function current(): array
    {
        return ShomriddhiInterestSlab::orderBy('id')->get(['min_amount', 'max_amount', 'interest_rate'])->toArray();
    }

    public function rate($amount, string $date): float
    {
        $latest = DB::table('shomriddhi_interest_rate_versions')->orderByDesc('effective_at')->orderByDesc('id')->first();
        if (! $latest || json_decode($latest->slabs, true) != $this->current()) {
            throw new \RuntimeException('Interest slabs changed outside recorded history; reconcile before accrual.');
        }
        $version = DB::table('shomriddhi_interest_rate_versions')->where('effective_at', '<=', Carbon::parse($date)->endOfDay())
            ->orderByDesc('effective_at')->orderByDesc('id')->first();
        if (! $version) throw new \RuntimeException('Historical interest rate is unavailable for '.$date.'. Reconciliation required.');
        $matches = array_values(array_filter(json_decode($version->slabs, true), fn ($slab) => $amount >= $slab['min_amount']
            && ($slab['max_amount'] === null || $amount <= $slab['max_amount'])));
        if (count($matches) > 1) throw new \RuntimeException('Overlapping interest slabs for '.$date.'. Resolve before accrual.');
        return (float) ($matches[0]['interest_rate'] ?? 0);
    }
}
