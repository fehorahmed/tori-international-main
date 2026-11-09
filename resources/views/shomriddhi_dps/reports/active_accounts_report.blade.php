@extends('layouts.print')
@section('title', 'DPS Active Accounts Report')
@section('content')
<div class="text-center mb-4">
    <h3 class="font-weight-bold">DPS Active Accounts Report</h3>
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
            <th>Inst. Paid</th>
            <th>Inst. Amount</th>
            <th>Total Deposited</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalDeposited = 0;
        @endphp
        @forelse($accounts as $account)
            @php
                $totalDeposited += $account->total_deposited;
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $account->dps_no }}</td>
                <td>{{ $account->customer->name ?? '' }}</td>
                <td>{{ $account->customer->mobile_no ?? '' }}</td>
                <td>{{ $account->scheme->name ?? '' }} ({{ $account->scheme->duration_months ?? 0 }} M)</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($account->opening_date)->format('d-M-Y') }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($account->maturity_date)->format('d-M-Y') }}</td>
                <td class="text-center">{{ $account->paid_installments }}/{{ $account->total_installments }}</td>
                <td class="text-right">{{ number_format($account->installment_amount, 2) }}</td>
                <td class="text-right">{{ number_format($account->total_deposited, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center">No active accounts found.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="9" class="text-right">Total Deposited:</th>
            <th class="text-right">{{ number_format($totalDeposited, 2) }}</th>
        </tr>
    </tfoot>
</table>
@endsection
