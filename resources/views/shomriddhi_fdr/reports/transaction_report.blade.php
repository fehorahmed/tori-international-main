@extends('layouts.print')
@section('title', 'FDR Transaction Report')

@section('content')
<div class="report-header">
    <h3 class="text-center">{{ auth()->user()->active_sister_concern->name ?? 'Company Name' }}</h3>
    <h5 class="text-center">FDR Transaction Report</h5>
    <p class="text-center">
        @if(request('type'))
            Type: {{ request('type') }} <br>
        @endif
        @if(request('start_date') && request('end_date'))
            Period: {{ request('start_date') }} to {{ request('end_date') }}
        @endif
    </p>
</div>

<table class="table table-bordered table-sm table-striped">
    <thead>
        <tr>
            <th>S/L</th>
            <th>Date</th>
            <th>Account No</th>
            <th>Customer Name</th>
            <th>Transaction Type</th>
            <th class="text-right">Amount</th>
            <th>Note</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalAmount = 0;
        @endphp
        @forelse($transactions as $index => $txn)
            @php
                $totalAmount += $txn->amount;
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($txn->date)->format('d-M-Y') }}</td>
                <td class="font-weight-bold">{{ $txn->account ? $txn->account->fdr_no : 'N/A' }}</td>
                <td>{{ $txn->account && $txn->account->customer ? $txn->account->customer->name : 'N/A' }}</td>
                <td>{{ $txn->type }}</td>
                <td class="text-right font-weight-bold">{{ number_format($txn->amount, 2) }}</td>
                <td>{{ $txn->note ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No transactions found.</td>
            </tr>
        @endforelse
    </tbody>
    @if($transactions->count() > 0)
    <tfoot>
        <tr>
            <th colspan="5" class="text-right">Total:</th>
            <th class="text-right">{{ number_format($totalAmount, 2) }}</th>
            <th></th>
        </tr>
    </tfoot>
    @endif
</table>
@endsection
