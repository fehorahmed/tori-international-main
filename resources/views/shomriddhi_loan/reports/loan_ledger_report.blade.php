@extends('layouts.print')
@section('title', 'Loan Account Ledger')
@section('content')
<div class="text-center mb-4">
    <h3 class="font-weight-bold">Loan Account Ledger</h3>
    <p class="mb-0">Date: {{ date('d-M-Y h:i A') }}</p>
</div>

<div class="row mb-4">
    <div class="col-6">
        <strong>Loan No:</strong> {{ $selectedLoan->loan_no }}<br>
        <strong>Customer Name:</strong> {{ $selectedLoan->customer->name ?? 'N/A' }}<br>
        <strong>Mobile:</strong> {{ $selectedLoan->customer->mobile_no ?? 'N/A' }}<br>
        <strong>Type:</strong> <span class="text-capitalize">{{ $selectedLoan->type }}</span>
    </div>
    <div class="col-6 text-right">
        <strong>Status:</strong> {{ ucfirst($selectedLoan->status) }}<br>
        <strong>Disbursement Date:</strong> {{ date('d-M-Y', strtotime($selectedLoan->disbursement_date)) }}<br>
        <strong>Total Payable:</strong> {{ number_format($selectedLoan->total_payable, 2) }}<br>
        <strong>Total Paid:</strong> {{ number_format($selectedLoan->paid_amount, 2) }}
    </div>
</div>

<table class="table table-bordered table-sm">
    <thead class="bg-light">
        <tr>
            <th>Date</th>
            <th>Voucher No</th>
            <th>Description</th>
            <th class="text-right">Debit (Charge)</th>
            <th class="text-right">Credit (Received)</th>
            <th class="text-right">Balance</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-center">{{ date('d-M-Y', strtotime($selectedLoan->disbursement_date)) }}</td>
            <td class="text-center">Opening</td>
            <td>Loan Disbursement / Principal + Profit Generated</td>
            <td class="text-right">{{ number_format($selectedLoan->total_payable, 2) }}</td>
            <td class="text-right">-</td>
            <td class="text-right font-weight-bold">{{ number_format($selectedLoan->total_payable, 2) }}</td>
        </tr>
        @php 
            $balance = $selectedLoan->total_payable; 
            $totalCredit = 0;
        @endphp
        @foreach($selectedLoan->transactions as $txn)
            @php 
                $balance -= $txn->amount; 
                $totalCredit += $txn->amount;
            @endphp
            <tr>
                <td class="text-center">{{ date('d-M-Y', strtotime($txn->collection_date)) }}</td>
                <td class="text-center">{{ $txn->voucher->voucher_no ?? 'N/A' }}</td>
                <td>Installment Collection via {{ $txn->payment_mode }}</td>
                <td class="text-right">-</td>
                <td class="text-right">{{ number_format($txn->amount, 2) }}</td>
                <td class="text-right font-weight-bold">{{ number_format($balance, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3" class="text-right">Total:</th>
            <th class="text-right">{{ number_format($selectedLoan->total_payable, 2) }}</th>
            <th class="text-right">{{ number_format($totalCredit, 2) }}</th>
            <th class="text-right">{{ number_format($balance, 2) }}</th>
        </tr>
    </tfoot>
</table>
@endsection
