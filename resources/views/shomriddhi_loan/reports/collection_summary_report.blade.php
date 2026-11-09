@extends('layouts.print')
@section('title', 'Collection Summary Report')
@section('content')
<div class="text-center mb-4">
    <h3 class="font-weight-bold">Collection Summary Report</h3>
    <p class="mb-0">From {{ $startDate->format('d-M-Y') }} To {{ $endDate->format('d-M-Y') }}</p>
    <p class="mb-0">Printed On: {{ date('d-M-Y h:i A') }}</p>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <h5 class="text-success font-weight-bold">Total Collection in Period: ৳ {{ number_format($totalCollected, 2) }}</h5>
    </div>
</div>

<table class="table table-bordered table-sm">
    <thead class="bg-light">
        <tr>
            <th>Date</th>
            <th>Loan Account</th>
            <th>Customer</th>
            <th>Collected By</th>
            <th>Mode</th>
            <th class="text-right">Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse($transactions as $txn)
        <tr>
            <td class="text-center">{{ date('d-M-Y', strtotime($txn->collection_date)) }}</td>
            <td class="text-center">{{ $txn->loanAccount->loan_no ?? 'N/A' }}</td>
            <td>{{ $txn->loanAccount->customer->name ?? 'N/A' }}</td>
            <td>{{ $txn->collectedBy->name ?? 'N/A' }}</td>
            <td class="text-center">{{ $txn->payment_mode }}</td>
            <td class="text-right">{{ number_format($txn->amount, 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center">No collections found in this period.</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="5" class="text-right">Total:</th>
            <th class="text-right font-weight-bold">{{ number_format($totalCollected, 2) }}</th>
        </tr>
    </tfoot>
</table>
@endsection
