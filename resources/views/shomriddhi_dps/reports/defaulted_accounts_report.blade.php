@extends('layouts.print')
@section('title', 'DPS Defaulted Accounts Report')
@section('content')
<div class="text-center mb-4">
    <h3 class="font-weight-bold">DPS Defaulted Accounts Report</h3>
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
            <th>Inst. Paid</th>
            <th>Missed Inst.</th>
            <th>Total Penalty Charged</th>
            <th>Total Deposited</th>
        </tr>
    </thead>
    <tbody>
        @forelse($accounts as $account)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $account->dps_no }}</td>
                <td>{{ $account->customer->name ?? '' }}</td>
                <td>{{ $account->customer->mobile_no ?? '' }}</td>
                <td>{{ $account->scheme->name ?? '' }}</td>
                <td class="text-center">{{ $account->paid_installments }}/{{ $account->total_installments }}</td>
                <td class="text-center font-weight-bold text-danger">{{ $account->missed_installments }}</td>
                <td class="text-right">{{ number_format($account->total_penalty_charged, 2) }}</td>
                <td class="text-right">{{ number_format($account->total_deposited, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center">No defaulted accounts found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
