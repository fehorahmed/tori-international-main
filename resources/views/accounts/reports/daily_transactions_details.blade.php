<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daily Transaction Details</title>
    @include('layouts.partial.__styles')
    @include('accounts.reports.daily_transactions_style')
</head>
<body>
<div class="container-fluid report-container">
    <div class="extra-remove mb-3"><a class="btn btn-secondary btn-sm" href="{{ route('report.daily_transactions', ['search' => 1, 'start_date' => $startDate, 'end_date' => $endDate, 'report_branch_id' => $branchId ?? 'all', 'category' => $category]) }}">Back to Summary</a></div>
    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Daily_Transaction_Details_'.$startDate.'_'.$endDate])
    <div id="printArea">
        @include('layouts.partial.__report_header')
        <h3 class="text-center mt-3">Transaction Details: {{ $head?->name ?? 'Missing Account Head' }}</h3>
        <p class="text-center">{{ $startDate }} to {{ $endDate }} · Branch: {{ $branchId !== null ? ($sister_concerns->get($branchId)?->name ?? $branchId) : 'All Branches' }}</p>
        <p>All matching entries: {{ $transactions->total() }} · Total Debit: <strong>{{ number_format($totals->debit, 2) }}</strong> · Total Credit: <strong>{{ number_format($totals->credit, 2) }}</strong></p>
        <p class="text-muted small">Showing page {{ $transactions->currentPage() }} of {{ $transactions->lastPage() }} (up to 100 entries per page). Export includes this page; totals above cover all matching entries.</p>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead><tr><th>Date</th><th>Branch</th><th>Voucher</th><th>Member / Party</th><th>Purpose</th><th>Notes</th><th class="text-right">Debit</th><th class="text-right">Credit</th></tr></thead>
                <tbody>
                    @foreach($transactions as $tx)
                    @php
                        $party = $tx->payeeDepositorHead?->supplier?->name ?? $tx->payeeDepositorHead?->name ?? $head?->supplier?->name;
                        if (!$party) {
                            $party = $tx->voucher?->transactions->map(fn ($line) => $line->accountHead?->supplier?->name)->filter()->unique()->implode(', ');
                        }
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($tx->date)->format('d-m-Y') }}</td>
                        <td>{{ $sister_concerns->get($tx->sister_concern_id)?->name ?? 'Unassigned' }}</td>
                        <td>{{ $tx->voucher_no ?: $tx->voucher?->voucher_no }}</td>
                        <td>{{ $party ?: '—' }}</td>
                        <td>{{ $purposeNames[$tx->payment_purpose] ?? 'Journal / Other' }}</td>
                        <td>{{ $tx->notes ?: $tx->voucher?->notes }}</td>
                        <td class="text-right">{{ (int) $tx->transaction_type === 1 ? number_format($tx->amount, 2) : '—' }}</td>
                        <td class="text-right">{{ (int) $tx->transaction_type === 2 ? number_format($tx->amount, 2) : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="extra-remove">{{ $transactions->links() }}</div>
</div>
@include('layouts.partial.__export_scripts')
</body></html>
