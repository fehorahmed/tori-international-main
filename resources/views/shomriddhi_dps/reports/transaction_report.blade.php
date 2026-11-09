@extends('layouts.print')
@section('title', 'DPS Transaction Report')
@section('content')
<div class="text-center mb-4">
    <h3 class="font-weight-bold">DPS Transaction Report</h3>
    <p class="mb-0">Date: {{ date('d-M-Y h:i A') }}</p>
</div>

<table class="table table-bordered table-sm">
    <thead class="bg-light">
        <tr>
            <th>SL</th>
            <th>Date</th>
            <th>DPS No</th>
            <th>Customer Name</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Penalty Amount</th>
            <th>Voucher No</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalAmount = 0;
            $totalPenalty = 0;
        @endphp
        @forelse($transactions as $txn)
            @php
                $totalAmount += $txn->amount;
                $totalPenalty += $txn->penalty_amount;
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($txn->date)->format('d-M-Y') }}</td>
                <td>{{ $txn->dpsAccount->dps_no ?? '' }}</td>
                <td>{{ $txn->dpsAccount->customer->name ?? '' }}</td>
                <td class="text-center">{{ $txn->type }}</td>
                <td class="text-right">{{ number_format($txn->amount, 2) }}</td>
                <td class="text-right">{{ number_format($txn->penalty_amount, 2) }}</td>
                <td class="text-center">{{ $txn->voucher->voucher_no ?? '' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">No transactions found.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="5" class="text-right">Total:</th>
            <th class="text-right">{{ number_format($totalAmount, 2) }}</th>
            <th class="text-right">{{ number_format($totalPenalty, 2) }}</th>
            <th></th>
        </tr>
    </tfoot>
</table>
@endsection
