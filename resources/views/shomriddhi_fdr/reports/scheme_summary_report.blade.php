@extends('layouts.print')
@section('title', 'FDR Scheme Summary Report')

@section('content')
<div class="report-header">
    <h3 class="text-center">{{ auth()->user()->active_sister_concern->name ?? 'Company Name' }}</h3>
    <h5 class="text-center">FDR Scheme Summary Report</h5>
</div>

<table class="table table-bordered table-sm table-striped mt-3">
    <thead>
        <tr>
            <th>S/L</th>
            <th>Scheme Name</th>
            <th>Duration (Months)</th>
            <th class="text-center">Total Active Accounts</th>
            <th class="text-right">Total Principal Amount</th>
        </tr>
    </thead>
    <tbody>
        @php
            $grandTotalAccounts = 0;
            $grandTotalPrincipal = 0;
        @endphp
        @forelse($schemes as $index => $scheme)
            @php
                $grandTotalAccounts += $scheme->accounts_count;
                $grandTotalPrincipal += $scheme->accounts_sum_principal_amount;
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="font-weight-bold">{{ $scheme->name }}</td>
                <td>{{ $scheme->duration }}</td>
                <td class="text-center font-weight-bold text-primary">{{ $scheme->accounts_count }}</td>
                <td class="text-right font-weight-bold text-success">{{ number_format($scheme->accounts_sum_principal_amount, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">No schemes found.</td>
            </tr>
        @endforelse
    </tbody>
    @if($schemes->count() > 0)
    <tfoot>
        <tr>
            <th colspan="3" class="text-right">Grand Total:</th>
            <th class="text-center">{{ $grandTotalAccounts }}</th>
            <th class="text-right">{{ number_format($grandTotalPrincipal, 2) }}</th>
        </tr>
    </tfoot>
    @endif
</table>
@endsection
