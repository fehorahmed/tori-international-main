<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daily Transactions Report</title>
    @include('layouts.partial.__styles')
    @include('accounts.reports.daily_transactions_style')
</head>
<body>
<div class="container-fluid report-container">
    <div class="extra-remove mb-3"><a href="{{ route('report.daily_transactions', ['start_date' => $startDate, 'end_date' => $endDate, 'branch_id' => $branchId, 'category' => $category]) }}" class="btn btn-secondary btn-sm">Change Filters</a></div>
    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Daily_Transactions_'.$startDate.'_'.$endDate])
    <div id="printArea">
        @include('layouts.partial.__report_header')
        <h3 class="text-center mt-3">Daily Transactions Report</h3>
        <p class="text-center">{{ $startDate }} to {{ $endDate }} · Branch: {{ $branchId !== null ? ($sister_concerns->firstWhere('id', $branchId)?->name ?? $branchId) : 'All Branches' }}<br>Category: {{ $sectionNames[$category] ?? 'All Transactions' }}</p>
        <div class="row">
            @foreach(['income' => 'Total Income', 'expense' => 'Total Expense', 'net_income' => 'Net Income', 'cash_in' => 'Cash / Bank In', 'cash_out' => 'Cash / Bank Out'] as $key => $label)
            <div class="col-6 col-lg"><div class="report-summary"><div>{{ $label }}</div><strong>৳ {{ number_format($report['totals'][$key], 2) }}</strong></div></div>
            @endforeach
        </div>
        <p class="text-muted small">Summary totals cover the full date and branch selection; category filters the tables below. Income = Credit − Debit; Expense = Debit − Credit. Refunds and reversals offset the original postings. Savings/FDR/DPS principal is a balance movement, not income or expense. Net income is based on posted entries; unposted expenses or stock adjustments are excluded.</p>
        <p class="text-muted small">Cash/Bank In and Out are gross account movements, including transfers between cash/bank accounts. Do not add these totals to income/expense. Other ledger debits and credits are accounting movements, not cash receipts or payments.</p>
        @foreach($sectionNames as $section => $title)
            @continue($category !== 'all' && $category !== $section)
            @php($rows = $report['sections'][$section])
            <h5 class="mt-4">{{ $title }}</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead><tr><th>Account Head</th><th class="text-right">Ledger Entries</th><th class="text-right">{{ $section === 'cash' ? 'Cash / Bank In' : 'Debit' }}</th><th class="text-right">{{ $section === 'cash' ? 'Cash / Bank Out' : 'Credit' }}</th>@if(in_array($section, ['income', 'expense']))<th class="text-right">Net {{ ucfirst($section) }}</th>@endif</tr></thead>
                    <tbody>
                        @forelse($rows as $row)
                        <tr>
                            <td><a href="{{ route('report.daily_transactions.details', ['start_date' => $startDate, 'end_date' => $endDate, 'branch_id' => $branchId, 'category' => $section, 'account_head_id' => $row['id']]) }}">{{ $row['code'] ? $row['code'].' — ' : '' }}{{ $row['name'] }}</a></td>
                            <td class="text-right">{{ $row['count'] }}</td><td class="text-right">{{ number_format($row['debit'], 2) }}</td><td class="text-right">{{ number_format($row['credit'], 2) }}</td>
                            @if(in_array($section, ['income', 'expense']))<td class="text-right">{{ number_format($row['net'], 2) }}</td>@endif
                        </tr>
                        @empty
                        <tr><td colspan="{{ in_array($section, ['income', 'expense']) ? 5 : 4 }}" class="text-center text-muted">No posted transactions for this selection.</td></tr>
                        @endforelse
                    </tbody>
                    @if(count($rows))
                    <tfoot><tr><th>Total</th><th class="text-right">{{ array_sum(array_column($rows, 'count')) }}</th><th class="text-right">{{ number_format(array_sum(array_column($rows, 'debit')), 2) }}</th><th class="text-right">{{ number_format(array_sum(array_column($rows, 'credit')), 2) }}</th>@if(in_array($section, ['income', 'expense']))<th class="text-right">{{ number_format($report['totals'][$section], 2) }}</th>@endif</tr></tfoot>
                    @endif
                </table>
            </div>
        @endforeach
    </div>
</div>
@include('layouts.partial.__export_scripts')
</body></html>
