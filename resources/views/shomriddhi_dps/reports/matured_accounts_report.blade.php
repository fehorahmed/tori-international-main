@extends('layouts.print')
@section('title', 'DPS Matured Accounts Report')
@section('content')
<div class="text-center mb-4">
    <h3 class="font-weight-bold">DPS Matured Accounts Report</h3>
    <p class="mb-0">Date: {{ date('d-M-Y h:i A') }}</p>
</div>

<table class="table table-bordered table-sm">
    <thead class="bg-light">
        <tr>
            <th>SL</th>
            <th>DPS No</th>
            <th>Customer Name</th>
            <th>Mobile</th>
            <th>Scheme</th>
            <th>Opening Date</th>
            <th>Maturity Date</th>
            <th>Total Deposited</th>
            <th>Payable at Maturity</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalDeposited = 0;
            $totalPayable = 0;
        @endphp
        @forelse($accounts as $account)
            @php
                $totalDeposited += $account->total_deposited;
                $totalPayable += $account->payable_at_maturity;
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $account->dps_no }}</td>
                <td>{{ $account->customer->name ?? '' }}</td>
                <td>{{ $account->customer->mobile_no ?? '' }}</td>
                <td>{{ $account->scheme->name ?? '' }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($account->opening_date)->format('d-M-Y') }}</td>
                <td class="text-center font-weight-bold text-danger">{{ \Carbon\Carbon::parse($account->maturity_date)->format('d-M-Y') }}</td>
                <td class="text-right">{{ number_format($account->total_deposited, 2) }}</td>
                <td class="text-right font-weight-bold">{{ number_format($account->payable_at_maturity, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center">No matured accounts found.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="7" class="text-right">Total:</th>
            <th class="text-right">{{ number_format($totalDeposited, 2) }}</th>
            <th class="text-right">{{ number_format($totalPayable, 2) }}</th>
        </tr>
    </tfoot>
</table>
@endsection
