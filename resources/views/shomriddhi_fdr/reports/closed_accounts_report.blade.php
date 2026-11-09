@extends('layouts.print')
@section('title', 'FDR Closed Accounts Report')

@section('content')
<div class="report-header">
    <h3 class="text-center">{{ auth()->user()->active_sister_concern->name ?? 'Company Name' }}</h3>
    <h5 class="text-center">FDR Closed/Encashed Accounts Report</h5>
</div>

<table class="table table-bordered table-sm table-striped mt-3">
    <thead>
        <tr>
            <th>S/L</th>
            <th>Account No</th>
            <th>Customer Name</th>
            <th>Scheme</th>
            <th>Opening Date</th>
            <th>Closed On</th>
            <th class="text-right">Principal Amount</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalPrincipal = 0;
        @endphp
        @forelse($accounts as $index => $account)
            @php
                $totalPrincipal += $account->principal_amount;
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="font-weight-bold">{{ $account->fdr_no }}</td>
                <td>{{ $account->customer ? $account->customer->name : 'N/A' }} <br><small>{{ $account->customer ? $account->customer->phone : '' }}</small></td>
                <td>{{ $account->scheme->name }}</td>
                <td>{{ \Carbon\Carbon::parse($account->opening_date)->format('d-M-Y') }}</td>
                <td class="text-danger">{{ \Carbon\Carbon::parse($account->updated_at)->format('d-M-Y') }}</td>
                <td class="text-right">{{ number_format($account->principal_amount, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No closed accounts found.</td>
            </tr>
        @endforelse
    </tbody>
    @if($accounts->count() > 0)
    <tfoot>
        <tr>
            <th colspan="6" class="text-right">Total:</th>
            <th class="text-right">{{ number_format($totalPrincipal, 2) }}</th>
        </tr>
    </tfoot>
    @endif
</table>
@endsection
