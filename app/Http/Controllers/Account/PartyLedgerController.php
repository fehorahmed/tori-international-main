<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\BranchManagement\SisterConcern;
use App\Services\Reports\PartyLedgerReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PartyLedgerController extends Controller
{
    public function customer(Request $request) { return $this->show($request, 'customer'); }
    public function supplier(Request $request) { return $this->show($request, 'supplier'); }

    private function show(Request $request, string $kind)
    {
        $branchId = \App\Support\ReportBranch::id();
        $branch = $branchId === null ? new SisterConcern(['name' => 'All Branches']) : SisterConcern::findOrFail($branchId);
        $service = app(PartyLedgerReport::class);
        $title = ucfirst($kind).' Ledger';
        $field = $kind.'_id';
        $route = 'report.accounts_'.$kind.'_ledger';
        $parties = $service->parties($kind, $branchId)->orderBy('name')->get();
        if (! $request->has('search')) return view('accounts.reports.party_ledger.filters', compact('parties', 'title', 'kind', 'field', 'route'));
        $filters = $request->validate([$field => 'required|integer|min:1', 'start_date' => 'required|date_format:d-m-Y', 'end_date' => 'required|date_format:d-m-Y|after_or_equal:start_date']);
        $party = $service->parties($kind, $branchId)->whereKey($filters[$field])->first();
        if (! $party) throw ValidationException::withMessages([$field => 'Select a valid '. $kind .' for the active branch.']);
        if (\App\Support\ReportBranch::allowed()) $filters['report_branch_id'] = $branchId ?? 'all';
        $start = Carbon::createFromFormat('d-m-Y', $filters['start_date'])->toDateString();
        $end = Carbon::createFromFormat('d-m-Y', $filters['end_date'])->toDateString();
        $report = $service->generate($party->id, $branchId, $start, $end);
        return view('accounts.reports.party_ledger.statement', compact('title', 'kind', 'party', 'branch', 'filters', 'route', 'report'));
    }
}
