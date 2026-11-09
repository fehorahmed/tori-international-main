@extends('layouts.print')
@section('title', 'DPS Scheme Summary Report')
@section('content')
<div class="text-center mb-4">
    <h3 class="font-weight-bold">DPS Scheme Summary Report</h3>
    <p class="mb-0">Date: {{ date('d-M-Y h:i A') }}</p>
</div>

<table class="table table-bordered table-sm">
    <thead class="bg-light">
        <tr>
            <th>SL</th>
            <th>Scheme Name</th>
            <th>Duration (Months)</th>
            <th>Inst. Amount</th>
            <th>Interest Rate</th>
            <th class="text-center">Total Active Accounts</th>
            <th class="text-right">Total Deposited Amount</th>
            <th class="text-right">Total Payable Liability</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalActive = 0;
            $grandDeposited = 0;
            $grandPayable = 0;
        @endphp
        @forelse($schemes as $scheme)
            @php
                $totalActive += $scheme->active_count;
                $grandDeposited += $scheme->total_deposited;
                $grandPayable += $scheme->total_payable;
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $scheme->name }}</td>
                <td class="text-center">{{ $scheme->duration_months }}</td>
                <td class="text-right">{{ number_format($scheme->installment_amount, 2) }} ({{ $scheme->installment_type }})</td>
                <td class="text-center">{{ $scheme->interest_rate }}%</td>
                <td class="text-center font-weight-bold">{{ $scheme->active_count }}</td>
                <td class="text-right">{{ number_format($scheme->total_deposited, 2) }}</td>
                <td class="text-right text-primary font-weight-bold">{{ number_format($scheme->total_payable, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">No schemes found.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="bg-light">
            <th colspan="5" class="text-right">Grand Total:</th>
            <th class="text-center">{{ $totalActive }}</th>
            <th class="text-right">{{ number_format($grandDeposited, 2) }}</th>
            <th class="text-right text-primary">{{ number_format($grandPayable, 2) }}</th>
        </tr>
    </tfoot>
</table>
@endsection
