@extends('layouts.print')
@section('title', 'FDR Active Accounts Report')

@section('content')
<div class="report-header">
    <h3 class="text-center">{{ auth()->user()->active_sister_concern->name ?? 'Company Name' }}</h3>
    <h5 class="text-center">FDR Active Accounts Report</h5>
</div>

<table class="table table-bordered table-sm table-striped mt-3">
    <thead>
        <tr>
            <th>S/L</th>
            <th>Account No</th>
            <th>Customer Name</th>
            <th>Scheme</th>
            <th>Opening Date</th>
            <th>Maturity Date</th>
            <th class="text-right">Principal Amount</th>
            <th class="text-right">Payable Amount</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalPrincipal = 0;
            $totalPayable = 0;
        @endphp
        @forelse($accounts as $index => $account)
            @php
                $totalPrincipal += $account->principal_amount;
                $totalPayable += $account->payable_amount_at_maturity;
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="font-weight-bold">{{ $account->fdr_no }}</td>
                <td>{{ $account->customer ? $account->customer->name : 'N/A' }} <br><small>{{ $account->customer ? $account->customer->phone : '' }}</small></td>
                <td>{{ $account->scheme->name }} ({{ $account->scheme->duration }}M)</td>
                <td>{{ \Carbon\Carbon::parse($account->opening_date)->format('d-M-Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($account->maturity_date)->format('d-M-Y') }}</td>
                <td class="text-right">{{ number_format($account->principal_amount, 2) }}</td>
                <td class="text-right">{{ number_format($account->payable_amount_at_maturity, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">No active accounts found.</td>
            </tr>
        @endforelse
    </tbody>
    @if($accounts->count() > 0)
    <tfoot>
        <tr>
            <th colspan="6" class="text-right">Total:</th>
            <th class="text-right">{{ number_format($totalPrincipal, 2) }}</th>
            <th class="text-right">{{ number_format($totalPayable, 2) }}</th>
        </tr>
    </tfoot>
    @endif
</table>
@endsection
